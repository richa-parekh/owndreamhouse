<?php
/**
 * Abilities feature gate.
 *
 * @package aThemes_Addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class aThemes_Addons_Abilities {

	const OPTION_ENABLED = 'athemes-addons-abilities-enabled';
	const OPTION_WRITES  = 'athemes-addons-abilities-allow-writes';

	public static function is_enabled() {
		return (bool) apply_filters( 'athemes_addons_abilities_enabled', get_option( self::OPTION_ENABLED, 0 ) );
	}

	public static function writes_enabled() {
		return self::is_enabled() && (bool) apply_filters( 'athemes_addons_abilities_allow_writes', get_option( self::OPTION_WRITES, 0 ) );
	}
}
