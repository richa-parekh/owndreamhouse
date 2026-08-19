<?php
/**
 * Standard {success, message, data} envelope for ability results.
 *
 * @package aThemes_Addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class aThemes_Addons_Abilities_Response {

	public static function success( $message, $data = array() ) {
		return array(
			'success' => true,
			'message' => (string) $message,
			'data'    => $data,
		);
	}

	public static function error( $message, $data = array() ) {
		return array(
			'success' => false,
			'message' => (string) $message,
			'data'    => $data,
		);
	}
}
