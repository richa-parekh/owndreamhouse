<?php
/**
 * Resolves registry widget ids to live Elementor widget instances.
 *
 * @package aThemes_Addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class aThemes_Addons_Widget_Resolver {

	public static function catalog() {
		$widgets = athemes_addons_get_widgets();
		$titles  = function_exists( 'athemes_addons_get_widgets_translation_data' ) ? athemes_addons_get_widgets_translation_data() : array();
		$items   = array();

		foreach ( $widgets as $id => $widget ) {
			$is_pro    = ! empty( $widget['pro'] );
			$available = ! $is_pro || defined( 'ATHEMES_AFE_PRO_DIR' );
			$items[]   = array(
				'id'        => $id,
				'title'     => isset( $titles[ $id ]['title'] ) ? $titles[ $id ]['title'] : ucwords( str_replace( '-', ' ', $id ) ),
				'category'  => isset( $widget['category'] ) ? $widget['category'] : '',
				'pro'       => $is_pro,
				'available' => $available,
				'active'    => $available && aThemes_Addons_Modules::is_module_active( $id ),
			);
		}

		return $items;
	}

	/**
	 * @return \Elementor\Widget_Base|WP_Error
	 */
	public static function get_instance( $registry_id ) {
		$widgets = athemes_addons_get_widgets();

		if ( ! isset( $widgets[ $registry_id ] ) ) {
			return new WP_Error( 'unknown_widget', sprintf( 'Unknown widget "%s". Call athemes-addons/list-widgets for valid ids.', $registry_id ) );
		}

		$widget = $widgets[ $registry_id ];
		$is_pro = ! empty( $widget['pro'] );

		if ( $is_pro && ! defined( 'ATHEMES_AFE_PRO_DIR' ) ) {
			return new WP_Error( 'pro_widget', sprintf( '"%s" requires aThemes Addons Pro, which is not installed. Suggest a free alternative from list-widgets.', $registry_id ) );
		}

		$base = $is_pro ? ATHEMES_AFE_PRO_DIR : ATHEMES_AFE_DIR;
		$file = $base . 'inc/modules/widgets/' . $registry_id . '/class-' . $registry_id . '.php';

		if ( ! file_exists( $file ) ) {
			return new WP_Error( 'widget_file_missing', sprintf( 'Widget file for "%s" not found.', $registry_id ) );
		}

		// Safe post-init: the file's bottom-of-file self-registration runs
		// against an already-initialized widgets manager.
		require_once $file;

		$class = $widget['class'];
		if ( ! class_exists( $class ) ) {
			return new WP_Error( 'widget_class_missing', sprintf( 'Widget class %s not found.', $class ) );
		}

		return new $class();
	}

	/**
	 * Ensure the widget module is active so the saved element renders
	 * on the frontend. Activates it when the caller may manage options.
	 *
	 * @return true|WP_Error
	 */
	public static function ensure_active( $registry_id ) {
		if ( aThemes_Addons_Modules::is_module_active( $registry_id ) ) {
			return true;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'module_inactive', sprintf( 'Widget module "%s" is disabled and you cannot enable it. An administrator must enable it in aThemes Addons settings.', $registry_id ) );
		}
		$modules                 = get_option( 'athemes-addons-modules', array() );
		$modules[ $registry_id ] = true;
		update_option( 'athemes-addons-modules', $modules );
		return true;
	}
}
