<?php
/**
 * Normalizes agent-supplied settings into Elementor's stored shapes,
 * validated against the widget's real control definitions.
 *
 * @package aThemes_Addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class aThemes_Addons_Settings_Normalizer {

	public static function normalize( array $input, array $controls, $post_id, array $tabs = array( 'content' ) ) {
		$settings = array();
		$ignored  = array();

		foreach ( $input as $key => $value ) {
			if ( ! isset( $controls[ $key ] ) || ! aThemes_Addons_Controls_Schema::is_allowed_control( $controls[ $key ], $tabs ) ) {
				$ignored[] = array(
					'key'    => (string) $key,
					'reason' => 'unknown key',
				);
				continue;
			}

			$normalized = self::normalize_value( $value, $controls[ $key ], $post_id );

			if ( null === $normalized ) {
				$ignored[] = array(
					'key'    => (string) $key,
					'reason' => 'invalid value',
				);
				continue;
			}

			$settings[ $key ] = $normalized;
		}

		return array(
			'settings' => $settings,
			'ignored'  => $ignored,
		);
	}

	/**
	 * Find repeater keys the incoming settings would empty out.
	 *
	 * @param array $existing_settings The element's current stored settings.
	 * @param array $new_settings      The normalized incoming settings.
	 * @param array $controls          The widget's control definitions.
	 * @return array<string> Repeater keys whose non-empty list would become empty.
	 */
	public static function detect_repeater_wipes( array $existing_settings, array $new_settings, array $controls ) {
		$wipes = array();

		foreach ( $new_settings as $key => $new_val ) {
			if ( ! isset( $controls[ $key ] ) ) {
				continue;
			}
			$type = isset( $controls[ $key ]['type'] ) ? $controls[ $key ]['type'] : '';
			if ( 'repeater' !== $type ) {
				continue;
			}

			$old = isset( $existing_settings[ $key ] ) ? $existing_settings[ $key ] : null;
			if ( is_array( $old ) && ! empty( $old ) && is_array( $new_val ) && empty( $new_val ) ) {
				$wipes[] = (string) $key;
			}
		}

		return $wipes;
	}

	private static function is_valid_color( $value ) {
		$v = trim( $value );
		if ( preg_match( '/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $v ) ) {
			return true;
		}
		if ( preg_match( '/^(?:rgb|rgba|hsl|hsla)\(\s*[0-9.,%\s\/]+\)$/i', $v ) ) {
			return true;
		}
		if ( preg_match( '/^var\(\s*--[A-Za-z0-9_-]+\s*(?:,[^()]*)?\)$/', $v ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Auto-set Elementor group toggles so incoming style values take effect.
	 *
	 * Elementor ignores e.g. background_color unless background_background is
	 * "classic", or a typography size unless {prefix}_typography is "custom".
	 * For each incoming key we find its sibling toggle control and, when it
	 * exists and the caller has not set it, switch it on.
	 *
	 * @param array $settings Normalized incoming settings.
	 * @param array $controls The widget's control definitions.
	 * @return array {settings: array (with toggles applied), auto_set: array<string,string>}
	 */
	public static function apply_prerequisite_toggles( array $settings, array $controls ) {
		$auto_set = array();

		$typography_fields = array( 'font_family', 'font_size', 'font_weight', 'font_style', 'text_transform', 'text_decoration', 'line_height', 'letter_spacing', 'word_spacing' );

		foreach ( $settings as $key => $value ) {
			// Typography group: a member field switches on {prefix}_typography = 'custom'.
			foreach ( $typography_fields as $suffix ) {
				if ( self::ends_with( $key, '_' . $suffix ) ) {
					$prefix = substr( $key, 0, - ( strlen( $suffix ) + 1 ) );
					$toggle = $prefix . '_typography';
					if ( isset( $controls[ $toggle ] ) && ! array_key_exists( $toggle, $settings ) && ! isset( $auto_set[ $toggle ] ) ) {
						$auto_set[ $toggle ] = 'custom';
					}
					break;
				}
			}

			// Background group: {prefix}_color / {prefix}_image switch on {prefix}_background = 'classic'.
			if ( self::ends_with( $key, '_color' ) || self::ends_with( $key, '_image' ) ) {
				$prefix = substr( $key, 0, (int) strrpos( $key, '_' ) );
				$toggle = $prefix . '_background';
				if ( isset( $controls[ $toggle ] ) && in_array( self::control_type( $controls[ $toggle ] ), array( 'background', 'choose', 'select' ), true ) && ! array_key_exists( $toggle, $settings ) && ! isset( $auto_set[ $toggle ] ) ) {
					$auto_set[ $toggle ] = 'classic';
				}
			}

			// Border group: {prefix}_width / {prefix}_color switch on {prefix}_border = 'solid'.
			if ( self::ends_with( $key, '_width' ) || self::ends_with( $key, '_color' ) ) {
				$prefix = substr( $key, 0, (int) strrpos( $key, '_' ) );
				$toggle = $prefix . '_border';
				if ( isset( $controls[ $toggle ] ) && ! array_key_exists( $toggle, $settings ) && ! isset( $auto_set[ $toggle ] ) ) {
					$auto_set[ $toggle ] = 'solid';
				}
			}
		}

		foreach ( $auto_set as $toggle => $val ) {
			$settings[ $toggle ] = $val;
		}

		return array(
			'settings' => $settings,
			'auto_set' => $auto_set,
		);
	}

	private static function ends_with( $haystack, $needle ) {
		$len = strlen( $needle );
		return $len <= strlen( $haystack ) && substr( $haystack, - $len ) === $needle;
	}

	private static function control_type( $control ) {
		return isset( $control['type'] ) ? $control['type'] : '';
	}

	/**
	 * @return mixed|null Null means "reject" (caller records ignored[]).
	 */
	private static function normalize_value( $value, array $control, $post_id ) {
		switch ( $control['type'] ) {
			case 'text':
			case 'date_time':
				return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : null;

			case 'textarea':
			case 'wysiwyg':
				return is_scalar( $value ) ? wp_kses_post( (string) $value ) : null;

			case 'number':
				return is_numeric( $value ) ? $value + 0 : null;

			case 'switcher':
				if ( ! is_bool( $value ) && ! in_array( $value, array( 'yes', '' ), true ) ) {
					return null;
				}
				return ( true === $value || 'yes' === $value ) ? 'yes' : '';

			case 'select':
			case 'choose':
				$valid = isset( $control['options'] ) && is_array( $control['options'] ) ? array_map( 'strval', array_keys( $control['options'] ) ) : null;
				if ( ! is_scalar( $value ) || ( is_array( $valid ) && ! in_array( (string) $value, $valid, true ) ) ) {
					return null;
				}
				return (string) $value;

			case 'select2':
				$valid  = isset( $control['options'] ) && is_array( $control['options'] ) ? array_map( 'strval', array_keys( $control['options'] ) ) : null;
				$values = is_array( $value ) ? $value : array( $value );
				if ( empty( $values ) ) {
					return null;
				}
				foreach ( $values as $v ) {
					if ( ! is_scalar( $v ) || ( is_array( $valid ) && ! in_array( (string) $v, $valid, true ) ) ) {
						return null;
					}
				}
				return empty( $control['multiple'] ) ? (string) $values[0] : array_map( 'strval', $values );

			case 'url':
				$url = is_string( $value ) ? $value : ( is_array( $value ) && isset( $value['url'] ) && is_string( $value['url'] ) ? $value['url'] : null );
				if ( null === $url ) {
					return null;
				}
				return array(
					'url'         => esc_url_raw( $url ),
					'is_external' => is_array( $value ) && ! empty( $value['is_external'] ),
					'nofollow'    => is_array( $value ) && ! empty( $value['nofollow'] ),
				);

			case 'media':
				return self::normalize_media( $value, $post_id );

			case 'gallery':
				if ( ! is_array( $value ) ) {
					return null;
				}
				$items = array();
				foreach ( $value as $item ) {
					$media = self::normalize_media( $item, $post_id );
					if ( null === $media ) {
						return null;
					}
					$items[] = $media;
				}
				return $items;

			case 'icons':
				if ( ! is_array( $value ) || empty( $value['value'] ) || ! is_string( $value['value'] ) ) {
					return null;
				}
				return array(
					'value'   => sanitize_text_field( $value['value'] ),
					'library' => isset( $value['library'] ) && is_string( $value['library'] ) ? sanitize_text_field( $value['library'] ) : 'fa-solid',
				);

			case 'repeater':
				if ( ! is_array( $value ) || empty( $control['fields'] ) || ! is_array( $control['fields'] ) ) {
					return null;
				}
				$items = array();
				foreach ( $value as $item ) {
					if ( ! is_array( $item ) ) {
						return null;
					}
					$row = array( '_id' => self::random_id() );
					foreach ( $item as $fkey => $fval ) {
						if ( ! isset( $control['fields'][ $fkey ] ) ) {
							continue; // Unknown sub-field: drop silently, keep the item.
						}
						$fnorm = self::normalize_value( $fval, $control['fields'][ $fkey ], $post_id );
						if ( null !== $fnorm ) {
							$row[ $fkey ] = $fnorm;
						}
					}
					$items[] = $row;
				}
				return $items;

			case 'color':
				if ( ! is_string( $value ) ) {
					return null;
				}
				if ( '' === $value ) {
					return ''; // Reset to default.
				}
				return self::is_valid_color( $value ) ? sanitize_text_field( $value ) : null;

			case 'slider':
				$units = isset( $control['size_units'] ) && is_array( $control['size_units'] ) ? array_map( 'strval', $control['size_units'] ) : array();
				if ( is_numeric( $value ) ) {
					return array(
						'size' => $value + 0,
						'unit' => ! empty( $units ) ? $units[0] : 'px',
					);
				}
				if ( is_array( $value ) && isset( $value['size'] ) && is_numeric( $value['size'] ) ) {
					$unit = isset( $value['unit'] ) && is_string( $value['unit'] ) ? $value['unit'] : ( ! empty( $units ) ? $units[0] : 'px' );
					if ( ! empty( $units ) && ! in_array( $unit, $units, true ) ) {
						return null;
					}
					return array(
						'size' => $value['size'] + 0,
						'unit' => $unit,
					);
				}
				return null;

			case 'dimensions':
				if ( ! is_array( $value ) ) {
					return null;
				}
				$units   = isset( $control['size_units'] ) && is_array( $control['size_units'] ) ? array_map( 'strval', $control['size_units'] ) : array();
				$out     = array();
				$has_any = false;
				foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
					if ( isset( $value[ $side ] ) ) {
						if ( '' !== $value[ $side ] && ! is_numeric( $value[ $side ] ) ) {
							return null;
						}
						$out[ $side ] = (string) $value[ $side ];
						$has_any      = true;
					} else {
						$out[ $side ] = '';
					}
				}
				if ( ! $has_any ) {
					return null;
				}
				$unit = isset( $value['unit'] ) && is_string( $value['unit'] ) ? $value['unit'] : ( ! empty( $units ) ? $units[0] : 'px' );
				if ( ! empty( $units ) && ! in_array( $unit, $units, true ) ) {
					return null;
				}
				$out['unit']     = $unit;
				$out['isLinked'] = isset( $value['isLinked'] ) ? (bool) $value['isLinked'] : false;
				return $out;

			default:
				return null; // Unmapped control type: not writable via abilities.
		}
	}

	private static function normalize_media( $value, $post_id ) {
		if ( is_numeric( $value ) ) {
			$value = array( 'id' => (int) $value );
		}
		if ( ! is_array( $value ) ) {
			return null;
		}

		$id  = isset( $value['id'] ) && is_numeric( $value['id'] ) ? (int) $value['id'] : 0;
		$url = isset( $value['url'] ) && is_string( $value['url'] ) ? $value['url'] : '';

		if ( $id > 0 ) {
			$att_url = wp_get_attachment_url( $id );
			return $att_url ? array(
				'id'  => $id,
				'url' => $att_url,
			) : null;
		}

		if ( '' === $url || ! preg_match( '#^https?://#i', $url ) ) {
			return null;
		}

		$existing = attachment_url_to_postid( $url );
		if ( $existing ) {
			return array(
				'id'  => (int) $existing,
				'url' => esc_url_raw( $url ),
			);
		}

		if ( current_user_can( 'upload_files' ) ) {
			$sideloaded = aThemes_Addons_Remote_Media::sideload( $url, $post_id );
			if ( ! is_wp_error( $sideloaded ) ) {
				return array(
					'id'  => (int) $sideloaded['id'],
					'url' => $sideloaded['url'],
				);
			}
		}

		return array(
			'id'  => '',
			'url' => esc_url_raw( $url ),
		); // URL-only fallback still renders in most widgets (sideload rejected or not permitted).
	}

	public static function random_id() {
		return substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 7 );
	}
}
