<?php
/**
 * Admin_Options Class.
 */
namespace aThemesAddons;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Theme_Builder_CPT' ) ) {
	class Theme_Builder_CPT {

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
			add_action( 'init', array( $this, 'register_templates_cpt' ), 99 );
		}

		/**
		 * Register Templates CPT.
		 */
		public function register_templates_cpt() {

			$labels = array(
				'name'                  => __( 'Templates', 'athemes-addons-for-elementor-lite' ),
				/* translators: Post Type Singular Name */
				'singular_name'         => _x( 'Template', 'Post Type Singular Name', 'athemes-addons-for-elementor-lite' ),
				'menu_name'             => __( 'Templates', 'athemes-addons-for-elementor-lite' ),
				'name_admin_bar'        => __( 'Post Type', 'athemes-addons-for-elementor-lite' ),
				'archives'              => __( 'Item Archives', 'athemes-addons-for-elementor-lite' ),
				'attributes'            => __( 'Item Attributes', 'athemes-addons-for-elementor-lite' ),
				'parent_item_colon'     => __( 'Parent Template:', 'athemes-addons-for-elementor-lite' ),
				'all_items'             => __( 'Templates', 'athemes-addons-for-elementor-lite' ),
				'add_new_item'          => __( 'Add New Template', 'athemes-addons-for-elementor-lite' ),
				'add_new'               => __( 'Add Template', 'athemes-addons-for-elementor-lite' ),
				'new_item'              => __( 'New Item', 'athemes-addons-for-elementor-lite' ),
				'edit_item'             => __( 'Edit Template', 'athemes-addons-for-elementor-lite' ),
				'update_item'           => __( 'Update Template', 'athemes-addons-for-elementor-lite' ),
				'view_item'             => __( 'View Template', 'athemes-addons-for-elementor-lite' ),
				'view_items'            => __( 'View Templates', 'athemes-addons-for-elementor-lite' ),
				'search_items'          => __( 'Search Template', 'athemes-addons-for-elementor-lite' ),
				'not_found'             => __( 'Not found', 'athemes-addons-for-elementor-lite' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'athemes-addons-for-elementor-lite' ),
				'featured_image'        => __( 'Featured Image', 'athemes-addons-for-elementor-lite' ),
				'set_featured_image'    => __( 'Set featured image', 'athemes-addons-for-elementor-lite' ),
				'remove_featured_image' => __( 'Remove featured image', 'athemes-addons-for-elementor-lite' ),
				'use_featured_image'    => __( 'Use as featured image', 'athemes-addons-for-elementor-lite' ),
				'insert_into_item'      => __( 'Insert into item', 'athemes-addons-for-elementor-lite' ),
				'uploaded_to_this_item' => __( 'Uploaded to this item', 'athemes-addons-for-elementor-lite' ),
				'items_list'            => __( 'Items list', 'athemes-addons-for-elementor-lite' ),
				'items_list_navigation' => __( 'Items list navigation', 'athemes-addons-for-elementor-lite' ),
				'filter_items_list'     => __( 'Filter items list', 'athemes-addons-for-elementor-lite' ),
			);
			$args = array(
				'label'                 => __( 'Post Type', 'athemes-addons-for-elementor-lite' ),
				'description'           => __( 'Post Type Description', 'athemes-addons-for-elementor-lite' ),
				'labels'                => $labels,
				'supports'              => array( 'title', 'elementor', 'editor' ),
				'hierarchical'          => false,
				'public'                => true,
				'show_ui'               => true,
				'show_in_menu'          => 'themes.php',
				'menu_position'         => 60,
				'menu_icon'             => 'dashicons-cart',
				'show_in_admin_bar'     => true,
				'show_in_nav_menus'     => false,
				'can_export'            => true,
				'has_archive'           => false,
				'exclude_from_search'   => true,
				'publicly_queryable'    => true,
				'capability_type'       => 'page',
				'show_in_rest'          => false,
			);

			register_post_type( 'aafe_templates', $args );
		}
	}

	Theme_Builder_CPT::instance();
}