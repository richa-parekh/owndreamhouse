<?php
/**
 * Template library templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<script type="text/template" id="template-athemes-addons-templateLibrary-header-logo">
	<img src="<?php echo esc_url( ATHEMES_AFE_URI . 'assets/images/athemes-addons-logo-original.svg' ); ?>" alt="Logo">
	<h2 style="margin-left:10px;">Addons Studio</h2>
</script>

<script type="text/template" id="template-athemes-addons-templateLibrary-header-back">
	<i class="eicon-" aria-hidden="true"></i>
	<span><?php echo esc_html__( 'Back to Library', 'athemes-addons-for-elementor-lite' ); ?></span>
</script>

<script type="text/template" id="template-athemes-addons-TemplateLibrary_header-menu">
	<# _.each( tabs, function( args, tab ) { var activeClass = args.active ? 'elementor-active' : ''; #>
		<div class="elementor-component-tab elementor-template-library-menu-item {{activeClass}}" data-tab="{{{ tab }}}">{{{ args.title }}}</div>
	<# } ); #>
</script>

<script type="text/template" id="template-athemes-addons-templateLibrary-header-actions">
	<div id="athemes-addons-templateLibrary-header-sync" class="elementor-templates-modal__header__item">
		<i class="eicon-sync" aria-hidden="true" title="<?php esc_attr_e( 'Sync Library', 'athemes-addons-for-elementor-lite' ); ?>"></i>
		<span class="elementor-screen-only"><?php esc_html_e( 'Sync Library', 'athemes-addons-for-elementor-lite' ); ?></span>
	</div>
</script>

<script type="text/template" id="template-athemes-addons-templateLibrary-preview">
    <iframe></iframe>
</script>

<script type="text/template" id="template-athemes-addons-templateLibrary-header-insert">
	<div id="elementor-template-library-header-preview-insert-wrapper" class="elementor-templates-modal__header__item">
		{{{ aafe.library.getModal().getTemplateActionButton( obj ) }}}
	</div>
</script>

<script type="text/template" id="template-athemes-addons-templateLibrary-insert-button">
	<a class="elementor-template-library-template-action elementor-button athemes-addons-templateLibrary-insert-button">
		<i class="eicon-file-download" aria-hidden="true"></i>
		<span class="elementor-button-title"><?php esc_html_e( 'Insert', 'athemes-addons-for-elementor-lite' ); ?></span>
	</a>
</script>

<script type="text/template" id="template-athemes-addons-templateLibrary-pro-button">
	<a class="elementor-template-library-template-action elementor-button athemes-addons-templateLibrary-pro-button" href="<?php echo esc_url( athemes_addons_admin_upgrade_link( 'https://athemes.com/addons/', array( 'utm_source' => 'template-library', 'utm_medium' => 'button', 'utm_campaign' => 'Addons' ), 'template-library-pro-button' ) ); ?>" target="_blank">
		<i class="eicon-external-link-square" aria-hidden="true"></i>
		<span class="elementor-button-title"><?php esc_html_e( 'Get Pro', 'athemes-addons-for-elementor-lite' ); ?></span>
	</a>
</script>

<script type="text/template" id="template-athemes-addons-templateLibrary-loading">
	<div class="elementor-loader-wrapper">
		<div class="elementor-loader">
			<div class="elementor-loader-boxes">
				<div class="elementor-loader-box"></div>
				<div class="elementor-loader-box"></div>
				<div class="elementor-loader-box"></div>
				<div class="elementor-loader-box"></div>
			</div>
		</div>
		<div class="elementor-loading-title"><?php esc_html_e( 'Loading', 'athemes-addons-for-elementor-lite' ); ?></div>
	</div>
</script>

<script type="text/template" id="template-athemes-addons-templateLibrary-templates">
	<div id="athemes-addons-templateLibrary-toolbar">
		<div id="athemes-addons-templateLibrary-toolbar-filter" class="athemes-addons-templateLibrary-toolbar-filter">
			<select id="athemes-addons-templateLibrary-filter-category" class="athemes-addons-templateLibrary-filter-category">
				<option class="athemes-addons-templateLibrary-category-filter-item active" value="" data-tag=""><?php esc_html_e( 'Filter', 'athemes-addons-for-elementor-lite' ); ?></option>

				<?php
					$aafe_cats = aThemesAddons\Template_Library_Manager::get_source()->get_categories();
					foreach ( (array) $aafe_cats as $aafe_cat ) : ?>
						<option class="athemes-addons-templateLibrary-category-filter-item" value="<?php echo esc_html( $aafe_cat['slug'] ); ?>" data-tag="<?php echo esc_attr( $aafe_cat['slug'] ); ?>"><?php echo esc_html( $aafe_cat['name'] ); ?></option>
					<?php endforeach;
				?>
			</select>
		</div>

		<div id="athemes-addons-templateLibrary-toolbar-filter-pro">
			<select id="athemes-addons-templateLibrary-filter-pro" class="athemes-addons-templateLibrary-filter-pro">
				<option value=""><?php esc_html_e( 'All', 'athemes-addons-for-elementor-lite' ); ?></option>
				<option value="free"><?php esc_html_e( 'Free', 'athemes-addons-for-elementor-lite' ); ?></option>
				<option value="pro"><?php esc_html_e( 'Pro', 'athemes-addons-for-elementor-lite' ); ?></option>
			</select>
		</div>

		<div id="athemes-addons-templateLibrary-toolbar-search">
			<label for="athemes-addons-templateLibrary-search" class="elementor-screen-only"><?php esc_html_e( 'Search Templates:', 'athemes-addons-for-elementor-lite' ); ?></label>
			<input id="athemes-addons-templateLibrary-search" placeholder="<?php esc_attr_e( 'Search', 'athemes-addons-for-elementor-lite' ); ?>">
			<i class="eicon-search"></i>
		</div>
	</div>

	<div class="athemes-addons-templateLibrary-templates-window">
		<div id="athemes-addons-templateLibrary-templates-list"></div>
	</div>
</script>

<script type="text/template" id="template-athemes-addons-templateLibrary-template">
	<div class="athemes-addons-templateLibrary-template-body" id="athemes-addons-template-{{ template_id }}">
		<div class="athemes-addons-templateLibrary-template-preview">
			<i class="eicon-zoom-in-bold" aria-hidden="true"></i>
		</div>
		<img class="athemes-addons-templateLibrary-template-thumbnail" src="{{ thumbnail }}">
		<div class="athemes-addons-templateLibrary-template-title">
			<span>{{{ title }}}</span>
		</div>
	</div>
	{{{ aafe.library.getModal().proLabel( obj ) }}}
	<div class="athemes-addons-templateLibrary-template-footer">
		{{{ aafe.library.getModal().getTemplateActionButton( obj ) }}}
		<a href="#" class="elementor-button athemes-addons-templateLibrary-preview-button">
			<i class="eicon-device-desktop" aria-hidden="true"></i>
			<?php esc_html_e( 'Preview', 'athemes-addons-for-elementor-lite' ); ?>
		</a>
	</div>
</script>
