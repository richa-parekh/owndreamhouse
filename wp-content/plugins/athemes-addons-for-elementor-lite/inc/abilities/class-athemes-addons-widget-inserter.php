<?php
/**
 * Builds Elementor element arrays and persists inserts via the Documents API.
 *
 * @package aThemes_Addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class aThemes_Addons_Widget_Inserter {

	const COLUMN_SIZES = array(
		1 => 100,
		2 => 50,
		3 => 33,
		4 => 25,
		5 => 20,
		6 => 16,
	);

	public static function build_widget( $widget_type, array $settings ) {
		return array(
			'id'         => aThemes_Addons_Settings_Normalizer::random_id(),
			'elType'     => 'widget',
			'widgetType' => $widget_type,
			'settings'   => $settings,
			'elements'   => array(),
		);
	}

	public static function build_row( array $widget_elements, $use_containers ) {
		$count = count( $widget_elements );

		if ( $use_containers ) {
			$settings = array();
			if ( $count > 1 ) {
				$settings = array(
					'flex_direction' => 'row',
					'flex_wrap'      => 'wrap',
				);
				foreach ( $widget_elements as $i => $el ) {
					$widget_elements[ $i ]['settings']['_flex_size'] = 'grow';
				}
			}
			return array(
				'id'       => aThemes_Addons_Settings_Normalizer::random_id(),
				'elType'   => 'container',
				'settings' => $settings,
				'elements' => array_values( $widget_elements ),
			);
		}

		$size    = isset( self::COLUMN_SIZES[ $count ] ) ? self::COLUMN_SIZES[ $count ] : (int) floor( 100 / $count );
		$columns = array();
		foreach ( $widget_elements as $el ) {
			$columns[] = array(
				'id'       => aThemes_Addons_Settings_Normalizer::random_id(),
				'elType'   => 'column',
				'settings' => array( '_column_size' => $size ),
				'elements' => array( $el ),
			);
		}
		return array(
			'id'       => aThemes_Addons_Settings_Normalizer::random_id(),
			'elType'   => 'section',
			'settings' => array(),
			'elements' => $columns,
		);
	}

	/**
	 * @param array      $elements       Existing tree.
	 * @param array      $row            Wrapped row (always container/section from build_row).
	 * @param array|null $position       {anchor_id, placement} or null for append.
	 * @param bool       $use_containers Container feature flag (kept for parity/Phase 2).
	 * @return array|WP_Error New tree.
	 */
	public static function place( array $elements, array $row, $position, $use_containers ) {
		if ( empty( $position ) || empty( $position['anchor_id'] ) ) {
			$elements[] = $row;
			return $elements;
		}

		$anchor_id = (string) $position['anchor_id'];
		$placement = isset( $position['placement'] ) ? (string) $position['placement'] : 'after';
		$hit       = aThemes_Addons_Elementor_Tree::find( $elements, $anchor_id );

		if ( null === $hit ) {
			return new WP_Error( 'anchor_not_found', sprintf( 'No element with id "%s" on this page. Call athemes-addons/get-page-elements to list valid ids.', $anchor_id ) );
		}

		$anchor_type = isset( $hit['element']['elType'] ) ? $hit['element']['elType'] : '';

		if ( in_array( $placement, array( 'before', 'after' ), true ) && 'column' === $anchor_type ) {
			return new WP_Error( 'invalid_anchor', 'Cannot insert next to a column. Target its parent section, or use inside_start/inside_end on the column.' );
		}

		if ( in_array( $placement, array( 'inside_start', 'inside_end' ), true ) && ! in_array( $anchor_type, array( 'container', 'column' ), true ) ) {
			return new WP_Error( 'invalid_anchor', 'inside_start/inside_end require a container or column anchor. Use before/after for widgets and sections.' );
		}

		// Inside a container/column (or next to an element that already sits
		// in one) a single widget goes in bare; a multi-widget row keeps its
		// wrapper as a nested row. At root level the wrapper is mandatory.
		$parent_is_root = ( 'root' === $hit['parent_type'] && in_array( $placement, array( 'before', 'after' ), true ) );
		$element        = $row;
		if ( ! $parent_is_root && 1 === count( $row['elements'] ) ) {
			$inner   = $row['elements'][0];
			$element = ( 'column' === $inner['elType'] ) ? $inner['elements'][0] : $inner;
		}

		$tree = aThemes_Addons_Elementor_Tree::insert_relative( $elements, $anchor_id, $placement, $element );

		if ( null === $tree ) {
			return new WP_Error( 'insert_failed', 'Insertion failed: the placement is not valid for that anchor.' );
		}

		return $tree;
	}

	/**
	 * Resolve a post id to an editable Elementor document, or a WP_Error the
	 * caller can hand straight to the agent.
	 *
	 * Check order matters: existence first (so a bad id gets a "verify the
	 * id" message, not a permission wall), then capability, then Elementor.
	 *
	 * @param int $post_id Target post.
	 * @return \Elementor\Core\Base\Document|WP_Error
	 */
	public static function resolve_editable_document( $post_id ) {
		$post_id = absint( $post_id );

		if ( ! $post_id || ! get_post( $post_id ) ) {
			return new WP_Error( 'post_not_found', sprintf( 'No post with id %d exists. Verify the post_id with athemes-addons/get-page-elements (it accepts a page URL) before writing.', $post_id ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_Error( 'forbidden', 'You cannot edit this post.' );
		}

		$document = \Elementor\Plugin::$instance->documents->get( $post_id, false );
		if ( ! $document || ! $document->is_built_with_elementor() ) {
			return new WP_Error( 'not_elementor', 'This page is not built with Elementor. Choose an Elementor page, or open this page once with the Elementor editor first.' );
		}

		return $document;
	}

	/**
	 * Full orchestration used by the insert-widget ability.
	 *
	 * @param int        $post_id       Target post.
	 * @param array      $widgets_input Array of {widget, settings} from the agent.
	 * @param array|null $position      {anchor_id, placement} or null.
	 * @param bool       $dry_run       Validate and build without saving.
	 * @return array|WP_Error {container_id, element_ids, ignored, warnings, dry_run, edit_url, preview_url}
	 */
	public static function save_to_post( $post_id, array $widgets_input, $position, $dry_run = false ) {
		$document = self::resolve_editable_document( $post_id );
		if ( is_wp_error( $document ) ) {
			return $document;
		}

		$use_containers = \Elementor\Plugin::$instance->experiments->is_feature_active( 'container' );
		$ignored        = array();
		$warnings       = array();
		$widget_els     = array();

		foreach ( $widgets_input as $index => $item ) {
			$registry_id = isset( $item['widget'] ) ? sanitize_key( $item['widget'] ) : '';
			$instance    = aThemes_Addons_Widget_Resolver::get_instance( $registry_id );
			if ( is_wp_error( $instance ) ) {
				return $instance; // All-or-nothing preflight: never insert a partial row.
			}

			$was_active = aThemes_Addons_Modules::is_module_active( $registry_id );
			if ( ! $was_active ) {
				if ( $dry_run ) {
					$warnings[] = sprintf( 'Would auto-enable widget module "%s" so it renders on the frontend.', $registry_id );
				} else {
					$active = aThemes_Addons_Widget_Resolver::ensure_active( $registry_id );
					if ( is_wp_error( $active ) ) {
						return $active;
					}
					$warnings[] = sprintf( 'Widget module "%s" was auto-enabled so it renders on the frontend.', $registry_id );
				}
			}

			$controls       = $instance->get_controls();
			$input_settings = isset( $item['settings'] ) && is_array( $item['settings'] ) ? $item['settings'] : array();
			$normalized     = aThemes_Addons_Settings_Normalizer::normalize( $input_settings, $controls, $post_id, array( 'content', 'style' ) );

			foreach ( $normalized['ignored'] as $ig ) {
				$ig['widget_index'] = $index;
				$ignored[]          = $ig;
			}

			$toggled = aThemes_Addons_Settings_Normalizer::apply_prerequisite_toggles( $normalized['settings'], $controls );
			foreach ( $toggled['auto_set'] as $auto_key => $auto_val ) {
				$warnings[] = sprintf( 'Auto-set %s = %s so its related style settings take effect.', $auto_key, $auto_val );
			}

			$widget_els[] = self::build_widget( $instance->get_name(), $toggled['settings'] );
		}

		$row  = self::build_row( $widget_els, $use_containers );
		$tree = self::place( $document->get_elements_data(), $row, $position, $use_containers );

		if ( is_wp_error( $tree ) ) {
			return $tree;
		}

		$ids = array();
		foreach ( $widget_els as $el ) {
			$ids[] = $el['id'];
		}

		$result = array(
			'container_id' => $row['id'],
			'element_ids'  => $ids,
			'ignored'      => $ignored,
			'warnings'     => $warnings,
			'dry_run'      => (bool) $dry_run,
			'edit_url'     => $document->get_edit_url(),
			'preview_url'  => get_permalink( $post_id ),
		);

		if ( $dry_run ) {
			return $result; // Zero side effects: nothing enabled, nothing saved.
		}

		try {
			$saved = $document->save( array( 'elements' => $tree ) );
		} catch ( \Exception $e ) {
			return new WP_Error( 'save_failed', sprintf( 'Elementor rejected the save: %s', $e->getMessage() ) );
		}
		if ( ! $saved ) {
			return new WP_Error( 'save_failed', 'Elementor rejected the save. The post may be locked or you may lack permission.' );
		}

		return $result;
	}

	/**
	 * Merge new settings into an existing aThemes Addons widget element.
	 *
	 * @param int    $post_id        Target post.
	 * @param string $element_id     Widget element id on that page.
	 * @param array  $settings_input Agent-supplied settings (partial).
	 * @param bool   $dry_run        Validate and merge without saving.
	 * @param bool   $force          Confirm emptying a repeater list.
	 * @return array|WP_Error {element_id, widget, updated_keys, ignored, warnings, dry_run, edit_url, preview_url}
	 */
	public static function update_in_post( $post_id, $element_id, array $settings_input, $dry_run = false, $force = false ) {
		$document = self::resolve_editable_document( $post_id );
		if ( is_wp_error( $document ) ) {
			return $document;
		}

		$elements = $document->get_elements_data();
		$hit      = aThemes_Addons_Elementor_Tree::find( $elements, $element_id );

		if ( null === $hit ) {
			return new WP_Error( 'element_not_found', sprintf( 'No element with id "%s" on this page. Call athemes-addons/get-page-elements to list valid ids.', $element_id ) );
		}

		$el = $hit['element'];
		if ( ! isset( $el['elType'] ) || 'widget' !== $el['elType'] ) {
			return new WP_Error( 'not_a_widget', 'That element is a layout element (container/section/column), not a widget. Only widget settings can be updated.' );
		}

		$widget_type = isset( $el['widgetType'] ) ? $el['widgetType'] : '';
		if ( 0 !== strpos( $widget_type, 'athemes-addons-' ) ) {
			return new WP_Error( 'not_athemes_widget', sprintf( '"%s" is not an aThemes Addons widget. Only aThemes Addons widgets can be updated with this ability.', $widget_type ) );
		}

		$registry_id = substr( $widget_type, strlen( 'athemes-addons-' ) );
		$instance    = aThemes_Addons_Widget_Resolver::get_instance( $registry_id );
		if ( is_wp_error( $instance ) ) {
			return $instance;
		}

		$controls   = $instance->get_controls();
		$normalized = aThemes_Addons_Settings_Normalizer::normalize( $settings_input, $controls, $post_id, array( 'content', 'style' ) );

		if ( empty( $normalized['settings'] ) ) {
			return new WP_Error( 'nothing_to_update', 'No valid settings supplied (all keys were unknown or invalid). Call athemes-addons/describe-widget for the valid keys.' );
		}

		$existing = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();

		// Destructive-merge guard: refuse to empty a populated repeater list
		// unless the caller confirms with force.
		$wipes = aThemes_Addons_Settings_Normalizer::detect_repeater_wipes( $existing, $normalized['settings'], $controls );
		if ( ! empty( $wipes ) && true !== $force ) {
			return new WP_Error( 'destructive_update', sprintf( 'This update would empty these list(s): %s. Pass force: true to confirm emptying these lists.', implode( ', ', $wipes ) ) );
		}

		$toggled  = aThemes_Addons_Settings_Normalizer::apply_prerequisite_toggles( $normalized['settings'], $controls );
		$warnings = array();
		foreach ( $toggled['auto_set'] as $auto_key => $auto_val ) {
			$warnings[] = sprintf( 'Auto-set %s = %s so its related style settings take effect.', $auto_key, $auto_val );
		}

		// Merge over current settings: omitted keys stay untouched.
		$merged = array_merge( $existing, $toggled['settings'] );

		$tree = aThemes_Addons_Elementor_Tree::set_widget_settings( $elements, $element_id, $merged );
		if ( null === $tree ) {
			return new WP_Error( 'update_failed', 'Could not update the element.' );
		}

		$result = array(
			'element_id'   => $element_id,
			'widget'       => $registry_id,
			'updated_keys' => array_keys( $normalized['settings'] ),
			'ignored'      => $normalized['ignored'],
			'warnings'     => $warnings,
			'dry_run'      => (bool) $dry_run,
			'edit_url'     => $document->get_edit_url(),
			'preview_url'  => get_permalink( $post_id ),
		);

		if ( $dry_run ) {
			return $result; // Zero side effects: nothing saved.
		}

		try {
			$saved = $document->save( array( 'elements' => $tree ) );
		} catch ( \Exception $e ) {
			return new WP_Error( 'save_failed', sprintf( 'Elementor rejected the save: %s', $e->getMessage() ) );
		}
		if ( ! $saved ) {
			return new WP_Error( 'save_failed', 'Elementor rejected the save. The post may be locked or you may lack permission.' );
		}

		return $result;
	}

	/**
	 * Remove an element from a page. aThemes widgets with no children remove
	 * freely; anything else (layout element, non-aThemes widget, or any element
	 * with children) requires force to confirm the wider deletion.
	 *
	 * @param int    $post_id    Target post.
	 * @param string $element_id Element id to remove.
	 * @param bool   $force      Confirm removing a non-trivial element.
	 * @return array|WP_Error {removed_ids, edit_url, preview_url}
	 */
	public static function remove_from_post( $post_id, $element_id, $force = false ) {
		$document = self::resolve_editable_document( $post_id );
		if ( is_wp_error( $document ) ) {
			return $document;
		}

		$elements = $document->get_elements_data();
		$hit      = aThemes_Addons_Elementor_Tree::find( $elements, $element_id );

		if ( null === $hit ) {
			return new WP_Error( 'element_not_found', sprintf( 'No element with id "%s" on this page. Call athemes-addons/get-page-elements to list valid ids.', $element_id ) );
		}

		$el                = $hit['element'];
		$el_type           = isset( $el['elType'] ) ? $el['elType'] : '';
		$widget_type       = isset( $el['widgetType'] ) ? $el['widgetType'] : '';
		$has_children      = ! empty( $el['elements'] ) && is_array( $el['elements'] );
		$is_athemes_widget = ( 'widget' === $el_type && 0 === strpos( $widget_type, 'athemes-addons-' ) );
		$needs_force       = ! ( $is_athemes_widget && ! $has_children );

		if ( $needs_force && true !== $force ) {
			if ( 'widget' !== $el_type ) {
				$what = sprintf( 'a %s and everything inside it', $el_type );
			} elseif ( $has_children ) {
				$what = 'a widget that contains other elements';
			} else {
				$what = sprintf( 'the non-aThemes widget "%s"', $widget_type );
			}
			return new WP_Error( 'force_required', sprintf( 'Removing %s is not reversible from here. Call remove-widget again with force: true to confirm.', $what ) );
		}

		$result = aThemes_Addons_Elementor_Tree::remove( $elements, $element_id, true );
		if ( null === $result ) {
			return new WP_Error( 'element_not_found', sprintf( 'No element with id "%s" on this page.', $element_id ) );
		}

		try {
			$saved = $document->save( array( 'elements' => $result['tree'] ) );
		} catch ( \Exception $e ) {
			return new WP_Error( 'save_failed', sprintf( 'Elementor rejected the save: %s', $e->getMessage() ) );
		}
		if ( ! $saved ) {
			return new WP_Error( 'save_failed', 'Elementor rejected the save. The post may be locked or you may lack permission.' );
		}

		return array(
			'removed_ids' => $result['removed'],
			'edit_url'    => $document->get_edit_url(),
			'preview_url' => get_permalink( $post_id ),
		);
	}

	/**
	 * Move an existing element to a new anchor/placement.
	 *
	 * @param int        $post_id    Target post.
	 * @param string     $element_id Element to move.
	 * @param array|null $position   {anchor_id, placement}.
	 * @return array|WP_Error {element_id, edit_url, preview_url}
	 */
	public static function move_in_post( $post_id, $element_id, $position ) {
		$document = self::resolve_editable_document( $post_id );
		if ( is_wp_error( $document ) ) {
			return $document;
		}

		$anchor_id = isset( $position['anchor_id'] ) ? (string) $position['anchor_id'] : '';
		$placement = isset( $position['placement'] ) ? (string) $position['placement'] : 'after';

		if ( '' === $anchor_id ) {
			return new WP_Error( 'invalid_position', 'position.anchor_id is required. Call athemes-addons/get-page-elements to find a valid id.' );
		}

		$result = aThemes_Addons_Elementor_Tree::move( $document->get_elements_data(), $element_id, $anchor_id, $placement );

		if ( is_string( $result ) ) {
			switch ( $result ) {
				case 'target_not_found':
					return new WP_Error( 'element_not_found', sprintf( 'No element with id "%s" to move. Call athemes-addons/get-page-elements to list valid ids.', $element_id ) );
				case 'anchor_not_found':
					return new WP_Error( 'anchor_not_found', sprintf( 'No anchor element with id "%s". Call athemes-addons/get-page-elements to pick a valid position.anchor_id.', $anchor_id ) );
				case 'cannot_move_into_self':
					return new WP_Error( 'cannot_move_into_self', 'An element cannot be moved next to or inside itself or one of its own descendants. Pick an anchor outside the element you are moving.' );
				case 'invalid_placement_target':
				default:
					return new WP_Error( 'invalid_placement_target', 'That placement is not valid for the anchor. Use before/after on any element except a column, or inside_start/inside_end on a container or column.' );
			}
		}

		try {
			$saved = $document->save( array( 'elements' => $result ) );
		} catch ( \Exception $e ) {
			return new WP_Error( 'save_failed', sprintf( 'Elementor rejected the save: %s', $e->getMessage() ) );
		}
		if ( ! $saved ) {
			return new WP_Error( 'save_failed', 'Elementor rejected the save. The post may be locked or you may lack permission.' );
		}

		return array(
			'element_id'  => $element_id,
			'edit_url'    => $document->get_edit_url(),
			'preview_url' => get_permalink( $post_id ),
		);
	}
}
