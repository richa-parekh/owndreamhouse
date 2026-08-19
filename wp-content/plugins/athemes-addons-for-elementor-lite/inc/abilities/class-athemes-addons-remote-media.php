<?php
/**
 * Fetches a remote image into the media library with SSRF, size and type
 * guards, deduplicating by the URL it came from.
 *
 * @package aThemes_Addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class aThemes_Addons_Remote_Media {

	const SOURCE_URL_META = '_athemes_addons_source_url';
	const MAX_FILE_SIZE   = 10485760; // 10 MB (10 * MB_IN_BYTES).
	const TIMEOUT         = 30;
	const MAX_REDIRECTS   = 3;

	/**
	 * Is this IP one only the server (or a private network) can reach?
	 *
	 * Uses inet_pton + byte/range math so both IPv4 and IPv6 (including
	 * ::ffff: mapped IPv4) are covered. Unparseable input fails closed.
	 *
	 * @param string $ip A raw IP literal.
	 * @return bool
	 */
	public static function is_blocked_ip( $ip ) {
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Malformed input warns; the false return is handled below.
		$packed = @inet_pton( $ip );
		if ( false === $packed ) {
			return true; // Fail closed: if we cannot parse it, do not trust it.
		}
		$len = strlen( $packed );
		if ( 4 === $len ) {
			return self::is_blocked_ipv4( $packed );
		}
		if ( 16 === $len ) {
			return self::is_blocked_ipv6( $packed );
		}
		return true;
	}

	private static function is_blocked_ipv4( $packed ) {
		$b = array_values( unpack( 'C4', $packed ) );

		if ( 0 === $b[0] ) {
			return true; // 0.0.0.0/8
		}
		if ( 10 === $b[0] ) {
			return true; // 10/8 private
		}
		if ( 127 === $b[0] ) {
			return true; // 127/8 loopback
		}
		if ( 100 === $b[0] && $b[1] >= 64 && $b[1] <= 127 ) {
			return true; // 100.64/10 CGNAT
		}
		if ( 169 === $b[0] && 254 === $b[1] ) {
			return true; // 169.254/16 link-local (incl. IMDS)
		}
		if ( 172 === $b[0] && $b[1] >= 16 && $b[1] <= 31 ) {
			return true; // 172.16/12 private
		}
		if ( 192 === $b[0] && 0 === $b[1] && 0 === $b[2] ) {
			return true; // 192.0.0/24 IETF protocol assignments
		}
		if ( 192 === $b[0] && 168 === $b[1] ) {
			return true; // 192.168/16 private
		}
		if ( 198 === $b[0] && ( 18 === $b[1] || 19 === $b[1] ) ) {
			return true; // 198.18/15 benchmarking
		}
		if ( 192 === $b[0] && 0 === $b[1] && 2 === $b[2] ) {
			return true; // 192.0.2/24 TEST-NET-1 documentation
		}
		if ( 198 === $b[0] && 51 === $b[1] && 100 === $b[2] ) {
			return true; // 198.51.100/24 TEST-NET-2 documentation
		}
		if ( 203 === $b[0] && 0 === $b[1] && 113 === $b[2] ) {
			return true; // 203.0.113/24 TEST-NET-3 documentation
		}
		if ( $b[0] >= 224 ) {
			return true; // 224/3 multicast + 240/4 reserved
		}
		return false;
	}

	private static function is_blocked_ipv6( $packed ) {
		$b = array_values( unpack( 'C16', $packed ) );

		$all_zero = true;
		for ( $i = 0; $i < 16; $i++ ) {
			if ( 0 !== $b[ $i ] ) {
				$all_zero = false;
				break;
			}
		}
		if ( $all_zero ) {
			return true; // :: unspecified
		}

		$loopback = true;
		for ( $i = 0; $i < 15; $i++ ) {
			if ( 0 !== $b[ $i ] ) {
				$loopback = false;
				break;
			}
		}
		if ( $loopback && 1 === $b[15] ) {
			return true; // ::1 loopback
		}

		if ( 0xfc === ( $b[0] & 0xfe ) ) {
			return true; // fc00::/7 unique local
		}
		if ( 0xfe === $b[0] && 0x80 === ( $b[1] & 0xc0 ) ) {
			return true; // fe80::/10 link-local
		}

		// Bytes 0-9 zero covers ::ffff:0:0/96 IPv4-mapped (bytes 10-11 = 0xff,0xff)
		// as well as the deprecated IPv4-compatible ::a.b.c.d form (bytes 10-11
		// = 0x00) and any other oddity in 0000::/8. `::` and `::1` are already
		// blocked above, so anything else reaching this point in that prefix has
		// no legitimate public-unicast use: validate the embedded v4 address if
		// it is a clean mapped address, otherwise block outright.
		$zero_prefix = true;
		for ( $i = 0; $i < 10; $i++ ) {
			if ( 0 !== $b[ $i ] ) {
				$zero_prefix = false;
				break;
			}
		}
		if ( $zero_prefix ) {
			if ( 0xff === $b[10] && 0xff === $b[11] ) {
				return self::is_blocked_ipv4( pack( 'C4', $b[12], $b[13], $b[14], $b[15] ) );
			}
			return true; // 0000::/8 oddity (e.g. deprecated IPv4-compatible ::a.b.c.d) — block.
		}

		return false;
	}

	/**
	 * Validate a URL and its resolved addresses before fetching.
	 *
	 * @param string $url          The URL about to be fetched.
	 * @param array  $resolved_ips IPs the host resolved to (injected so this
	 *                             stays pure and testable).
	 * @return true|WP_Error
	 */
	public static function validate_url_target( $url, array $resolved_ips ) {
		$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );
		if ( 'http' !== $scheme && 'https' !== $scheme ) {
			return new WP_Error( 'invalid_scheme', 'The image URL must be a public http or https address.' );
		}

		$host = (string) wp_parse_url( $url, PHP_URL_HOST );
		if ( '' === $host ) {
			return new WP_Error( 'blocked_host', 'The image URL has no host.' );
		}

		$raw = trim( $host, '[]' );
		if ( filter_var( $raw, FILTER_VALIDATE_IP ) && self::is_blocked_ip( $raw ) ) {
			return new WP_Error( 'blocked_host', 'The image URL points to a private, local or internal address, which is not allowed.' );
		}

		foreach ( $resolved_ips as $ip ) {
			if ( self::is_blocked_ip( $ip ) ) {
				return new WP_Error( 'blocked_host', 'The image host resolves to a private, local or internal address, which is not allowed.' );
			}
		}

		return true;
	}

	/**
	 * Fetch a remote image into the media library.
	 *
	 * Deduplicates by the source URL, resolves + validates the host on every
	 * redirect hop (redirects are followed manually so none are trusted
	 * silently), caps the body size, and confirms the bytes are really an
	 * image before handing them to media_handle_sideload().
	 *
	 * @param string $url     Public image URL.
	 * @param int    $post_id Post to attach to.
	 * @return array|WP_Error {id, url}
	 */
	public static function sideload( $url, $post_id ) {
		// Dedup: return the attachment already stored for this URL.
		$existing = get_posts(
			array(
				'post_type'   => 'attachment',
				'post_status' => 'inherit',
				'numberposts' => 1,
				'fields'      => 'ids',
				'meta_key'    => self::SOURCE_URL_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'  => esc_url_raw( $url ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		if ( ! empty( $existing ) ) {
			$id = (int) $existing[0];
			return array(
				'id'  => $id,
				'url' => wp_get_attachment_url( $id ),
			);
		}

		$current  = $url;
		$response = null;

		for ( $hop = 0; $hop <= self::MAX_REDIRECTS; $hop++ ) {
			$ips = self::resolve_host( $current );

			$host = trim( (string) wp_parse_url( $current, PHP_URL_HOST ), '[]' );
			if ( empty( $ips ) && ! filter_var( $host, FILTER_VALIDATE_IP ) ) {
				return new WP_Error( 'blocked_host', 'Could not resolve the image host to a public address.' );
			}

			$valid = self::validate_url_target( $current, $ips );
			if ( is_wp_error( $valid ) ) {
				return $valid;
			}

			$response = wp_safe_remote_get(
				$current,
				array(
					'timeout'             => self::TIMEOUT, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
					'redirection'         => 0,
					'limit_response_size' => self::MAX_FILE_SIZE,
				)
			);
			if ( is_wp_error( $response ) ) {
				return new WP_Error( 'fetch_failed', sprintf( 'Could not fetch the image: %s', $response->get_error_message() ) );
			}

			$code = (int) wp_remote_retrieve_response_code( $response );

			if ( in_array( $code, array( 301, 302, 303, 307, 308 ), true ) ) {
				$location = (string) wp_remote_retrieve_header( $response, 'location' );
				if ( '' === $location ) {
					return new WP_Error( 'fetch_failed', 'The image URL sent a redirect with no Location header.' );
				}
				$current = self::resolve_redirect_url( $current, $location );
				continue; // Re-resolve + re-validate the new target next loop.
			}

			if ( 200 !== $code ) {
				return new WP_Error( 'fetch_failed', sprintf( 'The image URL returned HTTP %d.', $code ) );
			}

			break; // 200 OK.
		}

		if ( null === $response || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( 'too_many_redirects', 'The image URL redirected too many times.' );
		}

		$body = wp_remote_retrieve_body( $response );
		if ( '' === $body ) {
			return new WP_Error( 'fetch_failed', 'The image URL returned an empty body.' );
		}
		if ( strlen( $body ) > self::MAX_FILE_SIZE ) {
			return new WP_Error( 'file_too_large', sprintf( 'The image is larger than the %s limit.', size_format( self::MAX_FILE_SIZE ) ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = wp_tempnam( 'athemes-addons-media' );
		if ( ! $tmp ) {
			return new WP_Error( 'temp_failed', 'Could not create a temporary file for the download.' );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$written = file_put_contents( $tmp, $body );
		if ( false === $written ) {
			wp_delete_file( $tmp );
			return new WP_Error( 'fetch_failed', 'Could not write the downloaded image to a temporary file.' );
		}

		// Magic-byte check: a script renamed .png is rejected; an extension-less
		// CDN URL still resolves. SVG carries no image header, so it is rejected.
		$mime = wp_get_image_mime( $tmp );
		$ext  = $mime ? array_search( $mime, get_allowed_mime_types(), true ) : false;
		if ( ! $ext ) {
			wp_delete_file( $tmp );
			return new WP_Error( 'not_an_image', 'The downloaded file is not an image type this site accepts. SVG is not supported.' );
		}

		$filename   = self::filename_from_url( $url, strtok( $ext, '|' ) );
		$sideloaded = media_handle_sideload(
			array(
				'name'     => $filename,
				'tmp_name' => $tmp,
			),
			$post_id
		);

		if ( is_wp_error( $sideloaded ) ) {
			wp_delete_file( $tmp );
			return $sideloaded;
		}

		update_post_meta( $sideloaded, self::SOURCE_URL_META, esc_url_raw( $url ) );

		return array(
			'id'  => (int) $sideloaded,
			'url' => wp_get_attachment_url( $sideloaded ),
		);
	}

	private static function resolve_host( $url ) {
		$host = trim( (string) wp_parse_url( $url, PHP_URL_HOST ), '[]' );
		if ( '' === $host ) {
			return array();
		}
		if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
			return array( $host );
		}

		$ips = array();
		$v4  = gethostbyname( $host );
		if ( $v4 && $v4 !== $host ) {
			$ips[] = $v4;
		}
		if ( function_exists( 'dns_get_record' ) ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Lookup failure warns; the non-array return is handled below.
			$records = @dns_get_record( $host, DNS_AAAA );
			if ( is_array( $records ) ) {
				foreach ( $records as $record ) {
					if ( ! empty( $record['ipv6'] ) ) {
						$ips[] = $record['ipv6'];
					}
				}
			}
		}
		return $ips;
	}

	public static function resolve_redirect_url( $base, $location ) {
		if ( preg_match( '#^https?://#i', $location ) ) {
			return $location;
		}
		$parts = wp_parse_url( $base );
		if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return $location;
		}
		// Protocol-relative ("//host/path") is an absolute URL missing only the
		// scheme — must be checked before the root-relative "/" case below,
		// since it also starts with "/".
		if ( 0 === strpos( $location, '//' ) ) {
			return $parts['scheme'] . ':' . $location;
		}
		$origin = $parts['scheme'] . '://' . $parts['host'] . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' );
		if ( '' !== $location && '/' === $location[0] ) {
			return $origin . $location;
		}
		return $origin . '/' . ltrim( $location, '/' );
	}

	private static function filename_from_url( $url, $ext ) {
		$path = wp_parse_url( $url, PHP_URL_PATH );
		$name = is_string( $path ) ? sanitize_file_name( basename( $path ) ) : '';
		if ( '' === $name ) {
			$name = 'image';
		}
		if ( '' !== $ext && strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) !== strtolower( $ext ) ) {
			$name = pathinfo( $name, PATHINFO_FILENAME ) . '.' . $ext;
		}
		return $name;
	}
}
