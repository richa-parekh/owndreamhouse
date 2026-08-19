<?php
namespace aThemesAddons;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="athemes-addons-modules-box">

	<?php
	do_action( 'athemes_addons_pro_admin_settings_before' );

	if ( defined('ATHEMES_AFE_PRO_DIR') ) {
		Admin_Settings::create( array(
			'title'  => __( 'MailChimp API Key', 'athemes-addons-for-elementor-lite' ),
			'fields' => array(
				array(
					'id'      => 'aafe_mailchimp_api_key',
					'type'    => 'text',
					'title'   => '',
					'default' => '',
				),
			),
		) );
	
		Admin_Settings::create( array(
			'title'  => __( 'Google Maps API Key', 'athemes-addons-for-elementor-lite' ),
			'fields' => array(
				array(
					'id'      => 'aafe_gmaps_api_key',
					'type'    => 'text',
					'title'   => '',
					'default' => '',
				),
			),
		) );
	}

	Admin_Settings::create( array(
		'title'     => __( 'Duplicator Post Types', 'athemes-addons-for-elementor-lite' ),
		'subtitle'  => __( 'Select the post types you want enable the duplicator for.', 'athemes-addons-for-elementor-lite' ),
		'fields'    => array(
			array(
				'id'      => 'aafe_duplicator_post_types',
				'type'    => 'multicheckbox',
				'default' => array( 'all' ),
				'options' => athemes_addons_get_post_types(),
			),
		),
	) );

	// Usage tracking settings.
	if ( ! defined('ATHEMES_AFE_PRO_DIR') ) {
		Admin_Settings::create( array(
			'title'    => __( 'Improve aThemes Addons', 'athemes-addons-for-elementor-lite' ),
			'subtitle' => __( 'By allowing us to track usage data, we can better help you, as we will know which WordPress configurations, themes, and plugins we should test. No sensitive data is collected.', 'athemes-addons-for-elementor-lite' ),
			'fields'   => array(
				array(
					'id'      => 'usage-tracking-enabled',
					'type'    => 'toggle',
					'default' => '',
				),
			),
		) );
	}

	Admin_Settings::save_button();

	/**
	 * AI Abilities card.
	 *
	 * The off/read/write radio saves immediately through its own AJAX
	 * handler (see Admin_Settings::ajax_abilities_mode()) instead of the
	 * generic aafe-save-settings flow above, so it is rendered as a bespoke
	 * block rather than through Admin_Settings::create(). Current state is
	 * read straight from the gate class (not the generic settings option)
	 * so the card always reflects what's actually registered.
	 */
	$aafe_abilities_enabled = \aThemes_Addons_Abilities::is_enabled();
	$aafe_abilities_writes  = \aThemes_Addons_Abilities::writes_enabled();
	$aafe_abilities_mode    = ! $aafe_abilities_enabled ? 'off' : ( $aafe_abilities_writes ? 'write' : 'read' );

	$aafe_abilities_modes = array(
		'off'   => __( 'Off', 'athemes-addons-for-elementor-lite' ),
		'read'  => __( 'Read-only', 'athemes-addons-for-elementor-lite' ),
		'write' => __( 'Read & write', 'athemes-addons-for-elementor-lite' ),
	);

	// Vibe AI plugin (the external MCP bridge). Status drives the install/activate button.
	$aafe_vibe_ai_path         = 'vibe-ai/vibe-ai.php';
	$aafe_vibe_ai_can_install  = current_user_can( 'install_plugins' );
	$aafe_vibe_ai_status       = $aafe_vibe_ai_can_install ? \aThemes_Addons_Plugin_Installer::get_plugin_status( $aafe_vibe_ai_path ) : 'not_installed';
	$aafe_vibe_ai_settings_url = admin_url( 'admin.php?page=vibe-ai' );
	?>

	<div class="athemes-addons-module-page-settings athemes-addons-abilities-settings">
		<div class="athemes-addons-module-page-setting-box">

			<div class="athemes-addons-module-page-setting-title">
				<h4><?php esc_html_e( 'AI Abilities', 'athemes-addons-for-elementor-lite' ); ?></h4>
				<div class="athemes-addons-module-page-setting-subtitle">
					<?php esc_html_e( 'Let AI assistants read and update widget and theme-builder settings, and build Elementor pages, through the WordPress Abilities API. Off by default.', 'athemes-addons-for-elementor-lite' ); ?>
				</div>
			</div>

			<div class="athemes-addons-module-page-setting-fields" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
				<div class="athemes-addons-module-page-setting-field athemes-addons-module-page-setting-field-radio">
					<div>
						<?php foreach ( $aafe_abilities_modes as $aafe_mode_value => $aafe_mode_label ) : ?>
							<label>
								<input
									type="radio"
									name="athemes-addons-abilities-mode"
									class="athemes-addons-abilities-mode-radio"
									value="<?php echo esc_attr( $aafe_mode_value ); ?>"
									<?php checked( $aafe_abilities_mode, $aafe_mode_value ); ?>
								/>
								<span><?php echo esc_html( $aafe_mode_label ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
					<span class="athemes-addons-abilities-mode-feedback" style="display:none;"></span>
				</div>

				<?php if ( $aafe_vibe_ai_can_install ) : ?>
					<div class="athemes-addons-module-page-setting-field-vibe-ai">
						<?php if ( 'active' === $aafe_vibe_ai_status ) : ?>
							<span class="athemes-addons-vibe-ai-active">
								<?php esc_html_e( 'Vibe AI active', 'athemes-addons-for-elementor-lite' ); ?>
								&mdash;
								<a href="<?php echo esc_url( $aafe_vibe_ai_settings_url ); ?>"><?php esc_html_e( 'Manage', 'athemes-addons-for-elementor-lite' ); ?></a>
							</span>
						<?php elseif ( 'inactive' === $aafe_vibe_ai_status ) : ?>
							<a
								href="<?php echo esc_url( $aafe_vibe_ai_settings_url ); ?>"
								class="button button-secondary addons-install-plugin"
								data-type="wporg"
								data-plugin-slug="vibe-ai"
								data-plugin-name="<?php echo esc_attr( $aafe_vibe_ai_path ); ?>"
								data-redirect-to="<?php echo esc_url( $aafe_vibe_ai_settings_url ); ?>"
							>
								<?php esc_html_e( 'Activate Vibe AI', 'athemes-addons-for-elementor-lite' ); ?>
							</a>
						<?php else : ?>
							<a
								href="<?php echo esc_url( $aafe_vibe_ai_settings_url ); ?>"
								class="button button-secondary addons-install-plugin"
								data-type="wporg"
								data-plugin-slug="vibe-ai"
								data-plugin-name="<?php echo esc_attr( $aafe_vibe_ai_path ); ?>"
								data-redirect-to="<?php echo esc_url( $aafe_vibe_ai_settings_url ); ?>"
							>
								<?php esc_html_e( 'Install Vibe AI', 'athemes-addons-for-elementor-lite' ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="athemes-addons-abilities-writes-warning notice notice-warning inline" style="margin: 12px 0 0; padding: 10px 14px;<?php echo ( 'write' === $aafe_abilities_mode ) ? '' : ' display:none;'; ?>">
				<p style="margin: 0;">
					<?php esc_html_e( 'Write access is enabled: any AI client connected over REST or MCP that a logged-in admin authorizes can modify widget settings, theme-builder templates, and Elementor pages on this site.', 'athemes-addons-for-elementor-lite' ); ?>
				</p>
			</div>

		</div>
	</div>

</div>