<?php
/**
 * Converts Elementor control definitions into JSON Schema. Supports both the
 * content tab (default) and, when requested via $tabs, the style tab.
 *
 * @package aThemes_Addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class aThemes_Addons_Controls_Schema {

	const SKIP_TYPES = array( 'section', 'tab', 'tabs', 'heading', 'raw_html', 'divider', 'notice', 'deprecated_notice', 'hidden', 'popover_toggle', 'button', 'aafe-template-link' );

	public static function from_widget( $widget, $tabs = array( 'content' ) ) {
		return self::from_controls( $widget->get_controls(), $tabs );
	}

	public static function from_controls( array $controls, $tabs = array( 'content' ) ) {
		$props = array();

		foreach ( $controls as $id => $control ) {
			if ( ! self::is_allowed_control( $control, $tabs ) ) {
				continue;
			}
			$schema = self::control_to_schema( $control );
			if ( null !== $schema ) {
				$props[ $id ] = $schema;
			}
		}

		return array(
			'type'                 => 'object',
			'additionalProperties' => false,
			'properties'           => $props,
		);
	}

	/**
	 * Is this control writable/describable for the given tabs?
	 *
	 * @param array $control One Elementor control definition.
	 * @param array $tabs    Allowed tab names, e.g. array( 'content', 'style' ).
	 * @return bool
	 */
	public static function is_allowed_control( array $control, array $tabs ) {
		$type = isset( $control['type'] ) ? $control['type'] : '';
		if ( in_array( $type, self::SKIP_TYPES, true ) ) {
			return false;
		}
		$tab = isset( $control['tab'] ) ? $control['tab'] : '';
		if ( ! in_array( $tab, $tabs, true ) ) {
			return false;
		}
		$section = isset( $control['section'] ) ? $control['section'] : '';
		if ( false !== strpos( $section, 'upsell' ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Back-compat wrapper: content-tab only.
	 *
	 * @param array $control One Elementor control definition.
	 * @return bool
	 */
	public static function is_content_control( array $control ) {
		return self::is_allowed_control( $control, array( 'content' ) );
	}

	private static function control_to_schema( array $control ) {
		$type = $control['type'];

		switch ( $type ) {
			case 'text':
			case 'textarea':
			case 'wysiwyg':
			case 'date_time':
				$schema = array( 'type' => 'string' );
				break;

			case 'number':
				$schema = array( 'type' => 'number' );
				break;

			case 'switcher':
				$schema = array( 'type' => 'boolean' );
				break;

			case 'select':
			case 'choose':
				$schema = array( 'type' => 'string' );
				if ( ! empty( $control['options'] ) && is_array( $control['options'] ) ) {
					$schema['enum'] = array_map( 'strval', array_keys( $control['options'] ) );
				}
				break;

			case 'select2':
				$multiple = ! empty( $control['multiple'] );
				$schema   = $multiple
					? array( 'type' => 'array', 'items' => array( 'type' => 'string' ) )
					: array( 'type' => 'string' );
				if ( ! empty( $control['options'] ) && is_array( $control['options'] ) ) {
					$enum = array_map( 'strval', array_keys( $control['options'] ) );
					if ( $multiple ) {
						$schema['items']['enum'] = $enum;
					} else {
						$schema['enum'] = $enum;
					}
				}
				break;

			case 'url':
				$schema = array(
					'type'        => 'object',
					'properties'  => array( 'url' => array( 'type' => 'string' ) ),
					'description' => 'Link. Pass {"url": "https://..."}.',
				);
				break;

			case 'media':
				$schema = array(
					'type'        => 'object',
					'properties'  => array(
						'id'  => array( 'type' => 'integer' ),
						'url' => array( 'type' => 'string' ),
					),
					'description' => 'Image. Pass {"id": attachment_id} for an existing media-library image, or {"url": "https://..."} to sideload a remote image.',
				);
				break;

			case 'icons':
				$schema = array(
					'type'        => 'object',
					'properties'  => array(
						'value'   => array( 'type' => 'string' ),
						'library' => array( 'type' => 'string' ),
					),
					'description' => 'Icon. Example: {"value": "fab fa-facebook-f", "library": "fa-brands"}. Omit to keep the default.',
				);
				break;

			case 'gallery':
				$schema = array(
					'type'        => 'array',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'  => array( 'type' => 'integer' ),
							'url' => array( 'type' => 'string' ),
						),
					),
					'description' => 'Image list. Each item: {"id": attachment_id} or {"url": "https://..."}.',
				);
				break;

			case 'repeater':
				$fields = array();
				if ( ! empty( $control['fields'] ) && is_array( $control['fields'] ) ) {
					foreach ( $control['fields'] as $field_id => $field ) {
						$field_type = isset( $field['type'] ) ? $field['type'] : '';
						if ( in_array( $field_type, self::SKIP_TYPES, true ) ) {
							continue;
						}
						// Repeater sub-fields carry no tab; convert directly.
						$field_schema = self::control_to_schema( $field );
						if ( null !== $field_schema ) {
							$fields[ $field_id ] = $field_schema;
						}
					}
				}
				$schema = array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => $fields,
					),
				);
				break;

			case 'color':
				$schema = array(
					'type'        => 'string',
					'description' => 'CSS color, e.g. "#1e73be" or "rgba(30,115,190,0.5)". Empty string resets to default.',
				);
				break;

			case 'slider':
				$size_unit = array( 'type' => 'string' );
				if ( ! empty( $control['size_units'] ) && is_array( $control['size_units'] ) ) {
					$size_unit['enum'] = array_map( 'strval', $control['size_units'] );
				}
				$schema = array(
					'type'        => 'object',
					'properties'  => array(
						'size' => array( 'type' => 'number' ),
						'unit' => $size_unit,
					),
					'description' => 'Size value. Pass {"size": 20, "unit": "px"}.',
				);
				break;

			case 'dimensions':
				$dim_unit = array( 'type' => 'string' );
				if ( ! empty( $control['size_units'] ) && is_array( $control['size_units'] ) ) {
					$dim_unit['enum'] = array_map( 'strval', $control['size_units'] );
				}
				$schema = array(
					'type'        => 'object',
					'properties'  => array(
						'top'      => array( 'type' => 'string' ),
						'right'    => array( 'type' => 'string' ),
						'bottom'   => array( 'type' => 'string' ),
						'left'     => array( 'type' => 'string' ),
						'unit'     => $dim_unit,
						'isLinked' => array( 'type' => 'boolean' ),
					),
					'description' => 'Spacing box. Pass any of top/right/bottom/left as numeric strings plus a unit, e.g. {"top": "10", "right": "20", "bottom": "10", "left": "20", "unit": "px", "isLinked": false}.',
				);
				break;

			default:
				return null; // Unmapped control type: omitted, widget default applies.
		}

		$label = isset( $control['label'] ) && is_string( $control['label'] ) ? $control['label'] : '';
		if ( '' !== $label && empty( $schema['description'] ) ) {
			$schema['description'] = $label;
		} elseif ( '' !== $label ) {
			$schema['description'] = $label . '. ' . $schema['description'];
		}

		if ( ! empty( $control['condition'] ) && is_array( $control['condition'] ) ) {
			$parts = array();
			foreach ( $control['condition'] as $dep_key => $dep_val ) {
				$parts[] = rtrim( $dep_key, '!' ) . '=' . ( is_array( $dep_val ) ? implode( '|', $dep_val ) : $dep_val );
			}
			$schema['description'] = ( isset( $schema['description'] ) ? $schema['description'] . ' ' : '' ) . '(Only applies when ' . implode( ', ', $parts ) . '.)';
		}

		if ( isset( $control['default'] ) && is_scalar( $control['default'] ) && '' !== $control['default'] && 'switcher' !== $type ) {
			$schema['default'] = $control['default'];
		}

		return $schema;
	}
}
