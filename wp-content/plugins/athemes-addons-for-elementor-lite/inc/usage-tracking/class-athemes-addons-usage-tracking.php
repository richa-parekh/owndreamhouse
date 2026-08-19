<?php
/**
 * Usage Tracker functionality to understand what's going on client's sites.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * aThemes_Addons_Usage_Tracking class.
 */
class aThemes_Addons_Usage_Tracking {

	/**
	 * The slug that will be used to save the option of Usage Tracker.
	 */
	const SETTINGS_SLUG = 'usage-tracking-enabled';

	/**
	 * Initialize the usage tracking system.
	 */
	public static function init_system() {

		/**
		 * Filter whether the Usage Tracking code is allowed to be loaded.
		 *
		 * @param bool $allowed Whether usage tracking is allowed.
		 */
		if ( ! apply_filters( 'athemes_addons_usage_tracking_is_allowed', true ) ) {
			return;
		}

		$usage_tracking = new self();
		$usage_tracking->init();

		$send_task = new aThemes_Addons_Send_Usage_Task();
		$send_task->init();
	}

	/**
	 * Attach hooks to the WordPress API
	 */
	public function init() {

		// Deregister the action if option is disabled.
		add_action( 'update_option_athemes-addons-settings', array( $this, 'maybe_cancel_task' ), 10, 2 );

		// Schedule the task if enabled.
		if ( self::is_enabled() ) {
			$this->schedule_task();
		}
	}

	/**
	 * Whether Usage Tracking is enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled() {

		// Get the settings option directly.
		$options = get_option( 'athemes-addons-settings', array() );
		$enabled = isset( $options[ self::SETTINGS_SLUG ] ) && $options[ self::SETTINGS_SLUG ] === 'on';

		/**
		 * Filter whether the Usage Tracking is enabled.
		 *
		 * @param bool $enabled Whether usage tracking is enabled.
		 *
		 * @since 1.0.0
		 */
		return (bool) apply_filters(
			'athemes_addons_usage_tracking_is_enabled',
			$enabled
		);
	}

	/**
	 * Schedule the usage tracking task.
	 *
	 * @since 1.0.0
	 */
	public function schedule_task() {

		if ( ! function_exists( 'as_schedule_recurring_action' ) ) {
			return;
		}

		// Schedule to run weekly.
		as_schedule_recurring_action( time(), WEEK_IN_SECONDS, 'athemes_addons_send_usage_data', array(), 'athemes-addons', true );
	}

	/**
	 * Cancel the usage tracking task if disabled.
	 */
	public function maybe_cancel_task( $old_value, $new_value ) {

		if ( self::is_enabled() || ! function_exists( 'as_unschedule_action' ) ) {
			return;
		}

		as_unschedule_action( 'athemes_addons_send_usage_data', array(), 'athemes-addons' );
	}

	/**
	 * Get the User Agent string that will be sent to the API.
	 *
	 * @return string
	 */
	public function get_user_agent() {

		$version = ATHEMES_AFE_VERSION;

		if ( defined( 'ATHEMES_AFE_PRO_VERSION' ) ) {
			$version .= ' Pro/' . ATHEMES_AFE_PRO_VERSION;
		}

		return 'aThemes Addons/' . $version . '; ' . get_bloginfo( 'url' );
	}

	/**
	 * Get data for sending to the server.
	 *
	 * @return array
	 */
	public function get_data() {

		global $wpdb;

		$theme_data = wp_get_theme();
		$is_pro     = defined( 'ATHEMES_AFE_PRO_VERSION' );

		$data = array(
			// Generic data (environment) - keys without prefix.
			'url'                    => home_url(),
			'php_version'            => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
			'wp_version'             => get_bloginfo( 'version' ),
			'mysql_version'          => $wpdb->db_version(),
			'server_version'         => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
			'is_ssl'                 => (int) is_ssl(),
			'is_multisite'           => (int) is_multisite(),
			'is_network_activated'   => (int) $this->is_active_for_network(),
			'is_wpcom'               => (int) ( defined( 'IS_WPCOM' ) && IS_WPCOM ),
			'is_wpcom_vip'           => (int) ( ( defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV ) || ( function_exists( 'wpcom_is_vip' ) && wpcom_is_vip() ) ),
			'is_wp_cache'            => (int) ( defined( 'WP_CACHE' ) && WP_CACHE ),
			'is_wp_rest_api_enabled' => (int) $this->is_rest_api_enabled(),
			'sites_count'            => $this->get_sites_total(),
			'active_plugins'         => $this->get_active_plugins(),
			'theme_name'             => $theme_data->get( 'Name' ),
			'theme_version'          => $theme_data->get( 'Version' ),
			'locale'                 => get_locale(),
			'timezone_offset'        => wp_timezone_string(),
			// aThemes Addons-specific data - keys with athemes_addons_ prefix.
			'athemes_addons_version'             => ATHEMES_AFE_VERSION,
			'athemes_addons_license_key'         => $this->get_license_key(),
			'athemes_addons_license_type'        => $this->get_license_type(),
			'athemes_addons_is_pro'              => (int) $is_pro,
			'athemes_addons_lite_installed_date' => $this->get_installed_date( 'lite' ),
			'athemes_addons_pro_installed_date'  => $is_pro ? $this->get_installed_date( 'pro' ) : '',
			'athemes_addons_active_modules'      => $this->get_active_modules(),
			'athemes_addons_settings'            => $this->get_settings(),
		);

		if ( $is_pro ) {
			$data['athemes_addons_pro_version'] = ATHEMES_AFE_PRO_VERSION;
		}

		if ( $data['is_multisite'] ) {
			$data['url_primary'] = network_site_url();
		}

		/**
		 * Filter the usage tracking data.
		 *
		 * @param array $data Usage tracking data.
		 */
		return apply_filters( 'athemes_addons_usage_tracking_data', $data );
	}

	/**
	 * Get the installed date for aThemes Addons or aThemes Addons Pro.
	 *
	 * @param string $type Either 'lite' or 'pro'.
	 *
	 * @return int Unix timestamp of installation date.
	 */
	private function get_installed_date( $type = 'lite' ) {

		$option_name = 'athemes_addons_installed_date';
		
		if ( $type === 'pro' ) {
			$option_name = 'athemes_addons_pro_installed_date';
		}

		$installed_date = get_option( $option_name, 0 );

		// If not set, set it now.
		if ( empty( $installed_date ) ) {
			$installed_date = time();
			// Update the option.
			update_option( $option_name, $installed_date );
		}

		return (int) $installed_date;
	}

	/**
	 * Get the license key.
	 *
	 * @return string
	 */
	private function get_license_key() {

		$license_key = trim( get_option( 'athemes_addons_pro_license_key', '' ) );

		if ( empty( $license_key ) ) {
			return '';
		}

		return sanitize_text_field( $license_key );
	}

	/**
	 * Get the license type.
	 *
	 * @return string
	 */
	private function get_license_type() {

		if ( ! defined( 'ATHEMES_AFE_PRO_VERSION' ) ) {
			return 'lite';
		}

		// Get the license item name from option (for future plan names like "agency", "lifetime", etc.)
		$license_item_name = get_option( 'athemes_addons_pro_license_item_name', '' );

		// If option is empty or default, return 'pro', otherwise return the plan name
		if ( empty( $license_item_name ) || $license_item_name === 'aThemes Addons Pro' ) {
			return 'pro';
		}

		return sanitize_text_field( strtolower( $license_item_name ) );
	}

	/**
	 * Get all active modules.
	 *
	 * @return array
	 */
	private function get_active_modules() {

		$modules = get_option( aThemes_Addons_Modules::$option, array() );

		if ( empty( $modules ) || ! is_array( $modules ) ) {
			return array();
		}

		// Filter to only include enabled modules.
		$active_modules = array();
		foreach ( $modules as $module_id => $is_active ) {
			if ( $is_active === true ) {
				$active_modules[] = $module_id;
			}
		}

		return $active_modules;
	}

	/**
	 * Get all settings, except modules.
	 *
	 * @return array
	 */
	private function get_settings() {
	
		// Get global settings.
		$settings = get_option( 'athemes-addons-settings', array() );

		if ( empty( $settings ) || ! is_array( $settings ) ) {
			return array();
		}

		// Remove modules from settings as they are sent separately.
		unset( $settings[ aThemes_Addons_Modules::$option ] );

		return $settings;
	}

	/**
	 * Get the list of active plugins.
	 *
	 * @return array
	 */
	private function get_active_plugins() {

		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$active = is_multisite() ?
			array_merge( get_option( 'active_plugins', array() ), array_flip( get_site_option( 'active_sitewide_plugins', array() ) ) ) :
			get_option( 'active_plugins', array() );

		$plugins = array_intersect_key( get_plugins(), array_flip( $active ) );

		return array_map(
			static function ( $plugin ) {
				if ( isset( $plugin['Version'] ) ) {
					return $plugin['Version'];
				}

				return 'Not Set';
			},
			$plugins
		);
	}

	/**
	 * Test if the REST API is accessible.
	 *
	 * The REST API might be inaccessible due to various security measures,
	 * or it might be completely disabled by a plugin.
	 *
	 * @return bool
	 */
	private function is_rest_api_enabled() {

		$url      = rest_url( 'wp/v2/types/post' );
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
				'headers' => array(
					'Cache-Control' => 'no-cache',
					'X-WP-Nonce'    => wp_create_nonce( 'wp_rest' ),
				),
			)
		);

		// When testing the REST API, an error was encountered, leave early.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// When testing the REST API, an unexpected result was returned, leave early.
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return false;
		}

		// The REST API did not behave correctly, leave early.
		$body = wp_remote_retrieve_body( $response );

		if ( ! $this->is_json( $body ) ) {
			return false;
		}

		// We are all set. Confirm the connection.
		return true;
	}

	/**
	 * Check if a string is valid JSON.
	 *
	 * @param string $value The string to check.
	 *
	 * @return bool
	 */
	private function is_json( $value ) {

		if ( ! is_string( $value ) ) {
			return false;
		}

		json_decode( $value, true );

		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Determines whether the plugin is active for the entire network.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function is_active_for_network() {

		// Bail early, in case we are not in multisite.
		if ( ! is_multisite() ) {
			return false;
		}

		// Get all active plugins.
		$plugins = get_site_option( 'active_sitewide_plugins' );

		// Bail early, in case the plugin is active for the entire network.
		if ( isset( $plugins[ plugin_basename( ATHEMES_AFE_FILE ) ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Total number of sites.
	 *
	 * @return int
	 */
	private function get_sites_total() {
		return function_exists( 'get_blog_count' ) ? (int) get_blog_count() : 1;
	}
}

// Initialize usage tracking.
add_action( 'init', array( 'aThemes_Addons_Usage_Tracking', 'init_system' ) );

