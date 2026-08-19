<?php
/**
 * Registers the category and lazily loads ability groups.
 *
 * @package aThemes_Addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class aThemes_Addons_Abilities_Registry {

	private static $did_categories = false;
	private static $did_abilities  = false;

	public static function register_categories() {
		if ( self::$did_categories || ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}
		self::$did_categories = true;

		wp_register_ability_category(
			'athemes-addons',
			array(
				'label'       => __( 'aThemes Addons for Elementor', 'athemes-addons-for-elementor-lite' ),
				'description' => __( 'Discover aThemes Addons Elementor widgets (team members, pricing tables, sliders, galleries, testimonials, posts lists and more), inspect their available settings, read the element structure of Elementor pages, and insert configured widgets at a specific position on a page.', 'athemes-addons-for-elementor-lite' ),
			)
		);
	}

	public static function register_abilities() {
		if ( self::$did_abilities ) {
			return;
		}
		self::$did_abilities = true;

		self::load_dependencies();

		$groups = apply_filters(
			'athemes_addons_abilities_groups',
			array(
				'aThemes_Addons_Abilities_Widgets',
				'aThemes_Addons_Abilities_Pages',
			)
		);

		foreach ( $groups as $group_class ) {
			if ( class_exists( $group_class ) ) {
				$group = new $group_class();
				$group->register();
			}
		}
	}

	private static function load_dependencies() {
		$base = ATHEMES_AFE_DIR . 'inc/abilities/';

		require_once $base . 'class-athemes-addons-ability.php';
		require_once $base . 'class-athemes-addons-abilities-response.php';
		require_once $base . 'class-athemes-addons-controls-schema.php';
		require_once $base . 'class-athemes-addons-elementor-tree.php';
		require_once $base . 'class-athemes-addons-widget-resolver.php';
		require_once $base . 'class-athemes-addons-remote-media.php';
		require_once $base . 'class-athemes-addons-settings-normalizer.php';
		require_once $base . 'class-athemes-addons-widget-inserter.php';
		require_once $base . 'groups/class-athemes-addons-abilities-widgets.php';
		require_once $base . 'groups/class-athemes-addons-abilities-pages.php';
	}
}
