<?php
namespace aThemesAddons;

use Elementor\Core\Common\Modules\Ajax\Module as Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Template_Library_Manager {

	protected static $source = null;

	public static function init() {
		add_action( 'elementor/editor/footer', [ __CLASS__, 'print_template_views' ] );
		add_action( 'elementor/ajax/register_actions', [ __CLASS__, 'register_ajax_actions' ] );
		add_action( 'elementor/preview/enqueue_styles', [ __CLASS__, 'enqueue_preview_styles' ] );
		add_action( 'elementor/editor/after_enqueue_scripts', [ __CLASS__, 'editor_scripts' ] );
	}

	public static function print_template_views() {
		require ATHEMES_AFE_DIR . 'inc/library/templates.php';
	}

	public static function enqueue_preview_styles() {
		wp_enqueue_style( 'athemes-addons-template-preview-style', ATHEMES_AFE_URI . 'inc/library/template-preview.min.css', [], ATHEMES_AFE_VERSION );
	}

	public static function editor_scripts() {
        wp_enqueue_script( 'athemes-addons-template-library-script', ATHEMES_AFE_URI . 'inc/library/template-library.min.js', [ 'elementor-editor', 'jquery-hover-intent' ], ATHEMES_AFE_VERSION, true );
		wp_enqueue_style( 'athemes-addons-template-library-style', ATHEMES_AFE_URI . 'inc/library/template-library.min.css', [], ATHEMES_AFE_VERSION );

		if ( !defined( 'ATHEMES_AFE_PRO_VERSION' ) ) {
			$isProActive = false;
		} else {
			$isProActive = true;
		}

		$required_widgets_map = require ATHEMES_AFE_DIR . 'inc/library/required-widgets.php';

		// Collect every unique widget type referenced across all templates
		$all_referenced = [];
		foreach ( $required_widgets_map as $widget_types ) {
			foreach ( $widget_types as $widget_type ) {
				$all_referenced[ $widget_type ] = true;
			}
		}

		// Determine which of those are currently inactive
		$inactive_widgets = [];
		foreach ( array_keys( $all_referenced ) as $widget_type ) {
			$module_id = str_replace( 'athemes-addons-', '', $widget_type );
			if ( ! \aThemes_Addons_Modules::is_module_active( $module_id ) ) {
				$inactive_widgets[] = $widget_type;
			}
		}

		$localized_data = [
			'aafeProWidgets'  => [],
			'isProActive'     => $isProActive,
			'requiredWidgets' => $required_widgets_map,
			'inactiveWidgets' => $inactive_widgets,
			'dashboardUrl'    => current_user_can( 'manage_options' ) ? esc_url( admin_url( 'admin.php?page=athemes-addons&section=widgets' ) ) : '',
			'i18n'            => [
				'templatesEmptyTitle'       => esc_html__( 'No Templates Found', 'athemes-addons-for-elementor-lite' ),
				'templatesEmptyMessage'     => esc_html__( 'Try different category or sync for new templates.', 'athemes-addons-for-elementor-lite' ),
				'templatesNoResultsTitle'   => esc_html__( 'No Results Found', 'athemes-addons-for-elementor-lite' ),
				'templatesNoResultsMessage' => esc_html__( 'Please make sure your search is spelled correctly or try a different word.', 'athemes-addons-for-elementor-lite' ),
				'missingWidgetsLabel'       => esc_html__( 'Required widgets are disabled:', 'athemes-addons-for-elementor-lite' ),
				'enableWidgetsDashboard'    => esc_html__( 'Enable in Dashboard', 'athemes-addons-for-elementor-lite' ),
				'insertLabel'               => esc_html__( 'Insert', 'athemes-addons-for-elementor-lite' ),
			],
		];

		wp_localize_script( 'athemes-addons-template-library-script', 'aafeEditor', $localized_data );
	}

	/**
	 * Undocumented function
	 *
	 * @return Template_Library_Source
	 */
	public static function get_source() {
		if ( is_null( self::$source ) ) {
			self::$source = new Template_Library_Source();
		}

		return self::$source;
	}

	public static function register_ajax_actions( Ajax $ajax ) {
		$ajax->register_ajax_action( 'aafe_get_template_library_data', function( $data ) {
			if ( ! current_user_can( 'edit_posts' ) ) {
				throw new \Exception( 'Access Denied' );
			}

			if ( ! empty( $data['editor_post_id'] ) ) {
				$editor_post_id = absint( $data['editor_post_id'] );

				if ( ! get_post( $editor_post_id ) ) {
					throw new \Exception( esc_html__( 'Post not found.', 'athemes-addons-for-elementor-lite' ) );
				}

				\Elementor\Plugin::instance()->db->switch_to_post( $editor_post_id );
			}

			$result = self::get_library_data( $data );

			return $result;
		} );

		$ajax->register_ajax_action( 'aafe_get_template_item_data', function( $data ) {
			if ( ! current_user_can( 'edit_posts' ) ) {
				throw new \Exception( 'Access Denied' );
			}

			if ( ! empty( $data['editor_post_id'] ) ) {
				$editor_post_id = absint( $data['editor_post_id'] );

				if ( ! get_post( $editor_post_id ) ) {
					throw new \Exception( esc_html__( 'Post not found', 'athemes-addons-for-elementor-lite' ) );
				}

				\Elementor\Plugin::instance()->db->switch_to_post( $editor_post_id );
			}

			if ( empty( $data['template_id'] ) ) {
				throw new \Exception( esc_html__( 'Template id missing', 'athemes-addons-for-elementor-lite' ) );
			}

			$result = self::get_template_data( $data );

			return $result;
		} );
	}

	public static function get_template_data( array $args ) {
		$source = self::get_source();
		$data = $source->get_data( $args );
		return $data;
	}

	public static function get_library_data( array $args ) {
		$source = self::get_source();

		if ( ! empty( $args['sync'] ) ) {
			Template_Library_Source::get_library_data( true );
		}

		return [
			'templates' => $source->get_items(),
			'category' => $source->get_categories(),
			'type_category' => $source->get_type_category(),
		];
	}
}

Template_Library_Manager::init();
