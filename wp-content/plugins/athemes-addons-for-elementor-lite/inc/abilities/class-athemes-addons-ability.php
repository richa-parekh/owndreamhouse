<?php
/**
 * Static registrar wrapping wp_register_ability() with plugin defaults.
 *
 * @package aThemes_Addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class aThemes_Addons_Ability {

	const READ_ANNOTATIONS  = array(
		'readonly'    => true,
		'destructive' => false,
		'idempotent'  => true,
	);
	const WRITE_ANNOTATIONS = array(
		'readonly'    => false,
		'destructive' => false,
		'idempotent'  => false,
	);

	/**
	 * Register one ability. Write abilities are skipped entirely when writes are off.
	 */
	public static function register( $id, array $args ) {
		$is_write = ! empty( $args['write'] );
		unset( $args['write'] );

		if ( $is_write && ! aThemes_Addons_Abilities::writes_enabled() ) {
			return;
		}

		$defaults = array(
			'category'            => 'athemes-addons',
			'permission_callback' => array( __CLASS__, 'can_edit_posts' ),
			'meta'                => array(),
		);
		$args     = array_merge( $defaults, $args );

		$args['meta'] = array_merge(
			array(
				'show_in_rest' => true,
				'annotations'  => $is_write ? self::WRITE_ANNOTATIONS : self::READ_ANNOTATIONS,
				'mcp'          => array(
					'public' => true,
					'type'   => 'tool',
				),
			),
			$args['meta']
		);

		// Every ability must carry a non-empty input_schema. WP_Ability::validate_input()
		// rejects any non-null input ({} — what MCP clients send for a zero-arg tool)
		// when the schema is empty, failing the ability before execution. A schema with
		// no real properties is normalized to a permissive object schema that still
		// validates an empty object; abilities that omit input_schema get the same.
		if ( isset( $args['input_schema'] ) ) {
			$props = isset( $args['input_schema']['properties'] ) ? $args['input_schema']['properties'] : null;
			if ( empty( $props ) || $props instanceof stdClass ) {
				$args['input_schema'] = array(
					'type'    => 'object',
					'default' => array(),
				);
			} else {
				$args['input_schema']['default'] = array();
			}
		} else {
			$args['input_schema'] = array(
				'type'    => 'object',
				'default' => array(),
			);
		}

		// Hard-failure contract: Throwables become WP_Error, never a
		// success-shaped envelope. Detail is logged only under WP_DEBUG.
		$callback                 = $args['execute_callback'];
		$args['execute_callback'] = function ( $input = array() ) use ( $callback, $id ) {
			try {
				// Elementor's Performance::should_optimize_controls() partitions
				// style controls out of get_controls() outside admin/preview/REST
				// (i.e. WP-CLI, cron, or a direct ability call), which would
				// truncate the control set describe-widget/normalize() see. This
				// restores the full set for ability execution regardless of
				// request context.
				if ( class_exists( '\Elementor\Core\Frontend\Performance' ) && method_exists( '\Elementor\Core\Frontend\Performance', 'set_use_style_controls' ) ) {
					\Elementor\Core\Frontend\Performance::set_use_style_controls( true );
				}
				return call_user_func( $callback, is_array( $input ) ? $input : array() );
			} catch ( \Throwable $e ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( sprintf( '[athemes-addons ability %s] %s', $id, $e->getMessage() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
				return new WP_Error( 'athemes_addons_ability_failed', __( 'The operation failed unexpectedly.', 'athemes-addons-for-elementor-lite' ) );
			}
		};

		wp_register_ability( $id, $args );
	}

	public static function can_edit_posts() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Standard output schema: {success, message, data:{...}}.
	 */
	public static function envelope_schema( array $data_props ) {
		return array(
			'type'       => 'object',
			'properties' => array(
				'success' => array( 'type' => 'boolean' ),
				'message' => array( 'type' => 'string' ),
				'data'    => array(
					'type'       => 'object',
					'properties' => $data_props,
				),
			),
		);
	}
}
