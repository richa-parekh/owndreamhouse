<?php
/**
 * Abilities: list-widgets, describe-widget.
 *
 * @package aThemes_Addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class aThemes_Addons_Abilities_Widgets {

	public function register() {
		aThemes_Addons_Ability::register(
			'athemes-addons/list-widgets',
			array(
				'label'            => __( 'List Elementor widgets', 'athemes-addons-for-elementor-lite' ),
				'description'      => __( 'List every aThemes Addons Elementor widget with its id, title, category, whether it is available (some widgets require the Pro plugin) and whether its module is active. Call this first to pick the right widget id, then call athemes-addons/describe-widget for its settings. If the widget the user wants is marked pro and not available, tell them and pick the closest available widget (e.g. use several team-member widgets in a row instead of the Pro team-carousel).', 'athemes-addons-for-elementor-lite' ),
				'output_schema'    => aThemes_Addons_Ability::envelope_schema(
					array(
						'widgets' => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'id'        => array( 'type' => 'string' ),
									'title'     => array( 'type' => 'string' ),
									'category'  => array( 'type' => 'string' ),
									'pro'       => array( 'type' => 'boolean' ),
									'available' => array( 'type' => 'boolean' ),
									'active'    => array( 'type' => 'boolean' ),
								),
							),
						),
					)
				),
				'execute_callback' => array( $this, 'execute_list_widgets' ),
				'meta'             => array( 'annotations' => aThemes_Addons_Ability::READ_ANNOTATIONS ),
			)
		);

		aThemes_Addons_Ability::register(
			'athemes-addons/describe-widget',
			array(
				'label'            => __( 'Describe a widget\'s settings', 'athemes-addons-for-elementor-lite' ),
				'description'      => __( 'Get the JSON schema of one widget\'s content settings (text, images, layout choices, repeater items). Call after athemes-addons/list-widgets and before athemes-addons/insert-widget. Every field is optional — omitted fields keep sensible defaults. Style settings (colors, spacing, typography sizes) are not included by default; pass include_style: true to add the style-tab controls when you need to change the look.', 'athemes-addons-for-elementor-lite' ),
				'input_schema'     => array(
					'type'       => 'object',
					'required'   => array( 'widget' ),
					'properties' => array(
						'widget'        => array(
							'type'        => 'string',
							'description' => 'Widget id from list-widgets, e.g. "team-member" or "pricing-table".',
						),
						'include_style' => array(
							'type'        => 'boolean',
							'description' => 'Also include style-tab controls (colors, spacing, typography sizes). Default false keeps the schema small.',
						),
					),
				),
				'output_schema'    => aThemes_Addons_Ability::envelope_schema(
					array(
						'widget' => array( 'type' => 'string' ),
						'title'  => array( 'type' => 'string' ),
						'schema' => array( 'type' => 'object' ),
					)
				),
				'execute_callback' => array( $this, 'execute_describe_widget' ),
				'meta'             => array( 'annotations' => aThemes_Addons_Ability::READ_ANNOTATIONS ),
			)
		);

		aThemes_Addons_Ability::register(
			'athemes-addons/get-design-tokens',
			array(
				'label'            => __( 'Get site design tokens', 'athemes-addons-for-elementor-lite' ),
				'description'      => __( 'Read the site\'s Elementor global design tokens: system and custom colors, and global typography (font families/weights). ALWAYS call this before setting any style value with insert-widget or update-widget, and use these colors/fonts instead of inventing hex codes or font names, so new widgets match the site\'s existing design. If a wanted color has no close token, prefer omitting the style setting (defaults follow the theme).', 'athemes-addons-for-elementor-lite' ),
				'output_schema'    => aThemes_Addons_Ability::envelope_schema(
					array(
						'colors'     => array(
							'type'        => 'array',
							'description' => 'Site colors (system + custom) from the active Elementor kit.',
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'id'    => array(
										'type'        => 'string',
										'description' => 'Elementor kit color id, e.g. "primary" or a custom color\'s internal id.',
									),
									'title' => array(
										'type'        => 'string',
										'description' => 'Human-readable color name, e.g. "Primary" or "Accent".',
									),
									'value' => array(
										'type'        => 'string',
										'description' => 'value is a CSS color, e.g. #6EC1E4.',
									),
								),
							),
						),
						'typography' => array(
							'type'        => 'array',
							'description' => 'Global typography (system + custom) from the active Elementor kit.',
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'id'          => array(
										'type'        => 'string',
										'description' => 'Elementor kit typography id.',
									),
									'title'       => array(
										'type'        => 'string',
										'description' => 'Human-readable typography name, e.g. "Primary" or "Heading".',
									),
									'font_family' => array(
										'type'        => 'string',
										'description' => 'Font family name, e.g. "Roboto". Empty string when not set.',
									),
									'font_weight' => array(
										'type'        => 'string',
										'description' => 'Font weight, e.g. "600" or "bold". Empty string when not set.',
									),
								),
							),
						),
					)
				),
				'execute_callback' => array( $this, 'execute_get_design_tokens' ),
				'meta'             => array( 'annotations' => aThemes_Addons_Ability::READ_ANNOTATIONS ),
			)
		);
	}

	public function execute_list_widgets() {
		return aThemes_Addons_Abilities_Response::success(
			'Widgets listed.',
			array( 'widgets' => aThemes_Addons_Widget_Resolver::catalog() )
		);
	}

	public function execute_describe_widget( $input ) {
		$id       = isset( $input['widget'] ) ? sanitize_key( $input['widget'] ) : '';
		$instance = aThemes_Addons_Widget_Resolver::get_instance( $id );

		if ( is_wp_error( $instance ) ) {
			return aThemes_Addons_Abilities_Response::error( $instance->get_error_message() );
		}

		$tabs = array( 'content' );
		if ( ! empty( $input['include_style'] ) && true === $input['include_style'] ) {
			$tabs[] = 'style';
		}

		return aThemes_Addons_Abilities_Response::success(
			'Schema generated from the widget\'s controls.',
			array(
				'widget' => $id,
				'title'  => $instance->get_title(),
				'schema' => aThemes_Addons_Controls_Schema::from_widget( $instance, $tabs ),
			)
		);
	}

	public function execute_get_design_tokens() {
		if ( ! class_exists( '\Elementor\Plugin' ) || empty( \Elementor\Plugin::$instance->kits_manager ) ) {
			return aThemes_Addons_Abilities_Response::error( 'No Elementor kit found. Style tokens unavailable; use widget defaults.' );
		}

		$kit      = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
		$settings = $kit ? $kit->get_settings_for_display() : array();

		$colors     = array_merge(
			$this->normalize_tokens_colors( isset( $settings['system_colors'] ) ? $settings['system_colors'] : array() ),
			$this->normalize_tokens_colors( isset( $settings['custom_colors'] ) ? $settings['custom_colors'] : array() )
		);
		$typography = array_merge(
			$this->normalize_tokens_typography( isset( $settings['system_typography'] ) ? $settings['system_typography'] : array() ),
			$this->normalize_tokens_typography( isset( $settings['custom_typography'] ) ? $settings['custom_typography'] : array() )
		);

		if ( empty( $colors ) && empty( $typography ) ) {
			return aThemes_Addons_Abilities_Response::error( 'No Elementor kit found. Style tokens unavailable; use widget defaults.' );
		}

		return aThemes_Addons_Abilities_Response::success(
			'Design tokens read from the active Elementor kit.',
			array(
				'colors'     => $colors,
				'typography' => $typography,
			)
		);
	}

	private function normalize_tokens_colors( $colors ) {
		if ( empty( $colors ) || ! is_array( $colors ) ) {
			return array();
		}

		$result = array();
		foreach ( $colors as $color ) {
			if ( ! is_array( $color ) || empty( $color['_id'] ) ) {
				continue;
			}

			$value    = isset( $color['color'] ) ? sanitize_hex_color( $color['color'] ) : '';
			$result[] = array(
				'id'    => sanitize_text_field( $color['_id'] ),
				'title' => sanitize_text_field( isset( $color['title'] ) ? $color['title'] : '' ),
				'value' => $value ? $value : '',
			);
		}

		return $result;
	}

	private function normalize_tokens_typography( $typography ) {
		if ( empty( $typography ) || ! is_array( $typography ) ) {
			return array();
		}

		$result = array();
		foreach ( $typography as $entry ) {
			if ( ! is_array( $entry ) || empty( $entry['_id'] ) ) {
				continue;
			}

			$item = array(
				'id'          => sanitize_text_field( $entry['_id'] ),
				'title'       => sanitize_text_field( isset( $entry['title'] ) ? $entry['title'] : '' ),
				'font_family' => '',
				'font_weight' => '',
			);

			// The typography group control is stored under either prefix depending on Elementor version.
			foreach ( array( 'typography_', 'styles_' ) as $prefix ) {
				if ( ! empty( $entry[ $prefix . 'font_family' ] ) ) {
					$item['font_family'] = sanitize_text_field( $entry[ $prefix . 'font_family' ] );
				}
				if ( ! empty( $entry[ $prefix . 'font_weight' ] ) ) {
					$item['font_weight'] = sanitize_text_field( $entry[ $prefix . 'font_weight' ] );
				}
			}

			$result[] = $item;
		}

		return $result;
	}
}
