<?php
/**
 * Abilities API bootstrap. Loads nothing unless the feature is on
 * and the Abilities API exists.
 *
 * @package aThemes_Addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ATHEMES_AFE_DIR . 'inc/abilities/class-athemes-addons-abilities.php';

if ( ! aThemes_Addons_Abilities::is_enabled() ) {
	return;
}

if ( ! function_exists( 'wp_register_ability' ) ) {
	return;
}

require_once ATHEMES_AFE_DIR . 'inc/abilities/class-athemes-addons-abilities-registry.php';

add_action( 'abilities_api_categories_init', array( 'aThemes_Addons_Abilities_Registry', 'register_categories' ) );
add_action( 'wp_abilities_api_categories_init', array( 'aThemes_Addons_Abilities_Registry', 'register_categories' ) );
add_action( 'abilities_api_init', array( 'aThemes_Addons_Abilities_Registry', 'register_abilities' ) );
add_action( 'wp_abilities_api_init', array( 'aThemes_Addons_Abilities_Registry', 'register_abilities' ) );
