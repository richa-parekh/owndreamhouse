<?php
/**
 * Admin_Options Class.
 */
namespace aThemesAddons;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Admin_Settings' ) ) {
	class Admin_Settings {

		/**
		 * The single class instance.
		 */
		private static $instance = null;

		/**
		 * Instance.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'wp_ajax_aafe_save_settings', array( $this, 'save_settings_callback' ) );
			add_action( 'wp_ajax_athemes_addons_abilities_mode', array( $this, 'ajax_abilities_mode' ) );
		}

		/**
		 * Save settings.
		 */
		public function save_settings_callback() {
			$nonce = ( isset( $_POST['nonce'] ) ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'You are not allowed to do this.' );
			}
		
			if ( wp_verify_nonce( $nonce, 'athemes-addons-elementor' ) ) {

				$options = get_option( 'athemes-addons-settings', array() );
		
				$fields = ( isset( $_POST['fields'] ) ) ? wp_unslash( $_POST['fields'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in foreach.
		
				foreach ( $fields as $field_id => $field_value ) {
					$sanitized_value  = isset( $field_value ) ? sanitize_text_field( wp_unslash( $field_value ) ) : '';

					$options[ $field_id ] = $sanitized_value;
				}
		
				update_option( 'athemes-addons-settings', $options );
		
				wp_send_json_success();
			}

			wp_send_json_error();
		}

		/**
		 * Set the AI Abilities mode from the Settings radio.
		 *
		 * Maps a single 3-way choice to the two gating options that
		 * `aThemes_Addons_Abilities` reads, so the radio stays a single
		 * atomic control:
		 *   off   -> enabled 0, allow-writes 0
		 *   read  -> enabled 1, allow-writes 0
		 *   write -> enabled 1, allow-writes 1
		 *
		 * This is a dedicated handler rather than routing through
		 * save_settings_callback() above: that saver has no allowlist and
		 * writes any posted field id into the generic settings option, which
		 * would let it flip the write-access gate outside of this explicit
		 * off/read/write mapping.
		 */
		public function ajax_abilities_mode() {
			check_ajax_referer( 'athemes-addons-elementor', 'nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error();
			}

			$mode = ( isset( $_POST['mode'] ) ) ? sanitize_text_field( wp_unslash( $_POST['mode'] ) ) : '';

			if ( ! in_array( $mode, array( 'off', 'read', 'write' ), true ) ) {
				wp_send_json_error();
			}

			update_option( \aThemes_Addons_Abilities::OPTION_ENABLED, ( 'off' === $mode ) ? 0 : 1 );
			update_option( \aThemes_Addons_Abilities::OPTION_WRITES, ( 'write' === $mode ) ? 1 : 0 );

			wp_send_json_success();
		}

		/**
		 * Get option.
		 */
		public static function get( $setting, $default ) {
			$options = get_option( 'athemes-addons-settings', array() );

			$value = $default;

			if ( isset( $options[ $setting ] ) && isset( $options[ $setting ] ) ) {
				$value = $options[ $setting ];
			}

			return $value;
		}

		/**
		 * Create options.
		 */
		public static function create( $settings ) {
			/**
			 * Hook: aafe_module_settings
			 *
			 * @since 1.0
			 */
			$settings = apply_filters( 'aafe_module_settings', $settings );

			$options = get_option( 'athemes-addons-settings', array() );

			?>
            <div class="athemes-addons-module-page-settings">
                <div class="athemes-addons-module-page-setting-box">
					<?php
					if ( ! empty( $settings['title'] ) ) : ?>
                        <div class="athemes-addons-module-page-setting-title">
							<?php
							echo '<h4>' . esc_html( $settings['title'] ) . '</h4>'; ?>
							<?php
							if ( ! empty( $settings['subtitle'] ) ) : ?>
                                <div class="athemes-addons-module-page-setting-subtitle"><?php
									echo esc_html( $settings['subtitle'] ); ?></div>
							<?php
							endif; ?>
                        </div>
					<?php
					endif; ?>
                    <div class="athemes-addons-module-page-setting-fields">
						<?php

						if ( ! empty( $settings['fields'] ) ) {
							foreach ( $settings['fields'] as $field ) {
								$value = null;

								if ( isset( $field['default'] ) ) {
									$value = $field['default'];
								}

								if ( isset( $field['id'] ) && isset( $options[ $field['id'] ] ) ) {
									$value = $options[ $field['id'] ];
								}
	
								if ( 'text' === $field['type'] ) {
									self::text( $field, $value );
								} elseif ( 'multicheckbox' === $field['type'] ) {
									self::multicheckbox( $field, $value );
								} elseif ( 'toggle' === $field['type'] ) {
									self::toggle( $field, $value );
								}
							}
						}
						?>
                    </div>
                </div>
            </div>
			<?php
		}

		/**
		 * Field: Text
		 */
		public static function text( $settings, $value ) {
			?>
			<div class="athemes-addons-module-page-setting-field athemes-addons-module-page-setting-field-text">
            <input type="text" name="<?php
			echo esc_attr( $settings['id'] ); ?>" value="<?php
			echo esc_attr( $value ); ?>"/>
			</div>
			<?php
		}

	/**
	 * Field: Multiple checkbox that allows multiple selection
	 */
	public static function multicheckbox( $settings, $value ) {
		?>
		<div class="athemes-addons-module-page-setting-field athemes-addons-module-page-setting-field-multicheckbox">
			<div>
			<?php
			if ( ! is_array( $value ) ) {
				$value = explode( ',', $value );
			}
			if ( ! empty( $settings['options'] ) ) : ?>
				<?php
				foreach ( $settings['options'] as $key => $option ) : ?>
					<label>
						<input 
							type="checkbox" name="<?php echo esc_attr( $settings['id'] ); ?>[]" 
							value="<?php echo esc_attr( $key ); ?>" 
							<?php checked( in_array( $key, $value, true ), true ); ?>
						/>
						<span><?php echo esc_html( $option ); ?></span>
					</label>
				<?php
				endforeach; ?>
			<?php
			endif; 
			?>
			</div>
			<input type="hidden" name="<?php echo esc_attr( $settings['id'] ); ?>" value="" />
		</div>
		<?php
	}

	/**
	 * Field: Toggle switch
	 */
	public static function toggle( $settings, $value ) {
		$checked = ( 'on' === $value ) ? 1 : 0;
		?>
		<div class="athemes-addons-module-page-setting-field athemes-addons-module-page-setting-field-toggle">
			<div class="athemes-addons-toggle-switch">
				<input 
					type="checkbox" 
					id="<?php echo esc_attr( $settings['id'] ); ?>" 
					name="<?php echo esc_attr( $settings['id'] ); ?>" 
					value="on" 
					<?php checked( $checked, 1 ); ?>
					class="toggle-switch-checkbox"
				/>
				<label class="toggle-switch-label" for="<?php echo esc_attr( $settings['id'] ); ?>">
					<span class="toggle-switch-inner"></span>
					<span class="toggle-switch-switch"></span>
				</label>
				<?php if ( ! empty( $settings['label'] ) ) : ?>
					<span><?php echo esc_html( $settings['label'] ); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Save button.
	 */
	public static function save_button() {
			?>
			<div class="athemes-addons-module-page-setting-save">
				<button class="button button-primary button-hero aafe-save-settings"><?php esc_html_e( 'Save', 'athemes-addons-for-elementor-lite' ); ?></button>
			</div>
			<?php
		}
	}

	Admin_Settings::instance();
}