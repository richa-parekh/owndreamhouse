<?php
/**
 * Abilities: get-page-elements, insert-widget.
 *
 * @package aThemes_Addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class aThemes_Addons_Abilities_Pages {

	/**
	 * Untranslated hint appended to insert-widget/update-widget success data,
	 * pointing agents at athemes-addons/get-design-tokens for style values.
	 */
	private static function design_note() {
		return 'Style tip: widgets inherit the site\'s theme styling by default. If you set colors/fonts, use values from athemes-addons/get-design-tokens rather than invented ones.';
	}

	public function register() {
		aThemes_Addons_Ability::register(
			'athemes-addons/list-pages',
			array(
				'label'            => __( 'List Elementor pages', 'athemes-addons-for-elementor-lite' ),
				'description'      => __( 'List pages/posts built with Elementor: id, title, status, type, URL, last-modified. Call this first when the user names a page (\'my about page\') and you don\'t have its post_id — match by title, then pass the id to athemes-addons/get-page-elements or athemes-addons/insert-widget. Only returns content the current user can edit.', 'athemes-addons-for-elementor-lite' ),
				'input_schema'     => array(
					'type'       => 'object',
					'properties' => array(
						'search'    => array(
							'type'        => 'string',
							'description' => 'Case-insensitive search in title and content, e.g. "about".',
						),
						'post_type' => array(
							'type'        => 'string',
							'enum'        => array( 'page', 'post', 'any' ),
							'description' => 'Which post type to search. Default "page".',
						),
						'limit'     => array(
							'type'        => 'integer',
							'description' => 'Max results, default 20, maximum 100.',
						),
					),
				),
				'output_schema'    => aThemes_Addons_Ability::envelope_schema(
					array(
						'pages' => array(
							'type'        => 'array',
							'description' => 'Matching pages/posts: {id, title, status, type, url, modified}.',
						),
						'total' => array(
							'type'        => 'integer',
							'description' => 'Total matches before limit was applied.',
						),
					)
				),
				'execute_callback' => array( $this, 'execute_list_pages' ),
				'meta'             => array( 'annotations' => aThemes_Addons_Ability::READ_ANNOTATIONS ),
			)
		);

		aThemes_Addons_Ability::register(
			'athemes-addons/get-page-elements',
			array(
				'label'            => __( 'Get page element structure', 'athemes-addons-for-elementor-lite' ),
				'description'      => __( 'Read the element tree of an Elementor page: every element\'s id, type and a text snippet. Call before athemes-addons/insert-widget to pick an anchor_id, and before athemes-addons/update-widget to find an element_id. Accepts a post_id or a page URL. Use contains to filter a large page down to matching nodes (and their ancestors), max_depth / max_nodes to bound the response (data.truncated flags when trimming happened), and include_settings to also see each widget\'s current stored settings before you update it.', 'athemes-addons-for-elementor-lite' ),
				'input_schema'     => array(
					'type'       => 'object',
					'properties' => array(
						'post_id'          => array(
							'type'        => 'integer',
							'description' => 'Post/page ID.',
						),
						'url'              => array(
							'type'        => 'string',
							'description' => 'Alternative to post_id: the page URL on this site.',
						),
						'include_settings' => array(
							'type'        => 'boolean',
							'description' => 'When true, each widget node also carries its current stored "settings". Use this before athemes-addons/update-widget so you know a widget\'s existing values and only change what you mean to.',
						),
						'contains'         => array(
							'type'        => 'string',
							'description' => 'Case-insensitive filter. The returned tree is pruned to nodes whose text, id or type contains this string, plus their ancestors. Use it to find the element you want without reading the whole page.',
						),
						'max_depth'        => array(
							'type'        => 'integer',
							'description' => 'Maximum nesting depth to return (1 = top-level only). Omit or 0 for unlimited. Deeper nodes are dropped and data.truncated becomes true.',
						),
						'max_nodes'        => array(
							'type'        => 'integer',
							'description' => 'Maximum number of nodes to return (default 500, capped at 2000). Beyond the cap the tree is trimmed and data.truncated becomes true.',
						),
					),
				),
				'output_schema'    => aThemes_Addons_Ability::envelope_schema(
					array(
						'post_id'         => array( 'type' => 'integer' ),
						'title'           => array( 'type' => 'string' ),
						'uses_containers' => array( 'type' => 'boolean' ),
						'elements'        => array(
							'type'        => 'array',
							'description' => 'Nested nodes: {id, type, text?, children?, settings?}. settings is present only when include_settings was true.',
						),
						'truncated'       => array(
							'type'        => 'boolean',
							'description' => 'True when max_depth or max_nodes trimmed the tree. Narrow with contains or raise max_nodes to see the rest.',
						),
					)
				),
				'execute_callback' => array( $this, 'execute_get_page_elements' ),
				'meta'             => array( 'annotations' => aThemes_Addons_Ability::READ_ANNOTATIONS ),
			)
		);

		aThemes_Addons_Ability::register(
			'athemes-addons/insert-widget',
			array(
				'label'               => __( 'Insert widgets into a page', 'athemes-addons-for-elementor-lite' ),
				'description'         => __( 'Insert 1-6 configured aThemes Addons widgets into an Elementor page as one row (multiple widgets sit side by side — e.g. three team-member widgets make a 3-person team row). Call athemes-addons/describe-widget first for valid settings, and athemes-addons/get-page-elements first when a specific position is wanted; omit position to append at the end of the page. Invalid setting keys/values are skipped and reported in data.ignored (with widget_index) — the insert itself still succeeds, so do not retry the whole call because of ignored entries. Pass dry_run: true to preview the outcome (including ignored[]) without changing the page. If someone has the Elementor editor open on the same page, their next save may overwrite this insert (a revision is always kept).', 'athemes-addons-for-elementor-lite' ),
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'post_id', 'widgets' ),
					'properties' => array(
						'post_id'  => array(
							'type'        => 'integer',
							'description' => 'Target Elementor page/post ID.',
						),
						'widgets'  => array(
							'type'        => 'array',
							'minItems'    => 1,
							'maxItems'    => 6,
							'description' => 'Widgets to insert as one row, in order.',
							'items'       => array(
								'type'       => 'object',
								'required'   => array( 'widget' ),
								'properties' => array(
									'widget'   => array(
										'type'        => 'string',
										'description' => 'Widget id from list-widgets, e.g. "team-member".',
									),
									'settings' => array(
										'type'        => 'object',
										'description' => 'Settings per describe-widget\'s schema. All optional.',
									),
								),
							),
						),
						'position' => array(
							'type'        => 'object',
							'description' => 'Where to insert. Omit to append at the end of the page.',
							'properties'  => array(
								'anchor_id' => array(
									'type'        => 'string',
									'description' => 'Element id from get-page-elements.',
								),
								'placement' => array(
									'type'        => 'string',
									'enum'        => array( 'before', 'after', 'inside_start', 'inside_end' ),
									'description' => 'before/after: sibling of the anchor (any element except columns). inside_start/inside_end: first/last child — anchor must be a container or column.',
								),
							),
						),
						'dry_run'  => array(
							'type'        => 'boolean',
							'description' => 'Validate and build without saving. The response shows exactly what a real call would do (including ignored[]) but the page is not modified.',
						),
					),
				),
				'output_schema'       => aThemes_Addons_Ability::envelope_schema(
					array(
						'container_id' => array( 'type' => 'string' ),
						'element_ids'  => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
						'ignored'      => array(
							'type'        => 'array',
							'description' => 'Skipped settings: {key, reason, widget_index}.',
						),
						'warnings'     => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
						'dry_run'      => array(
							'type'        => 'boolean',
							'description' => 'True when this was a validation-only run; the page was not modified.',
						),
						'edit_url'     => array( 'type' => 'string' ),
						'preview_url'  => array( 'type' => 'string' ),
						'design_note'  => array(
							'type'        => 'string',
							'description' => 'Reminder to source style values from athemes-addons/get-design-tokens instead of inventing them.',
						),
					)
				),
				'execute_callback'    => array( $this, 'execute_insert_widget' ),
				'permission_callback' => array( $this, 'can_insert' ),
				'meta'                => array( 'annotations' => aThemes_Addons_Ability::WRITE_ANNOTATIONS ),
				'write'               => true,
			)
		);

		aThemes_Addons_Ability::register(
			'athemes-addons/update-widget',
			array(
				'label'               => __( 'Update a widget\'s settings', 'athemes-addons-for-elementor-lite' ),
				'description'         => __( 'Change the content settings of an existing aThemes Addons widget on an Elementor page — e.g. rename a team member, change a price, edit a call-to-action text. Find the element_id via athemes-addons/get-page-elements or from a previous insert-widget result, and valid setting keys via athemes-addons/describe-widget. Settings merge over the current ones: omitted keys are untouched; a repeater key replaces that whole list. Invalid keys/values are skipped and reported in data.ignored — the update itself still succeeds, so do not retry the call because of ignored entries. Pass dry_run: true to preview the change without saving. Replacing a populated list with an empty one is refused unless you pass force: true (it names the affected lists). Only aThemes Addons widgets can be updated.', 'athemes-addons-for-elementor-lite' ),
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'post_id', 'element_id', 'settings' ),
					'properties' => array(
						'post_id'    => array(
							'type'        => 'integer',
							'description' => 'Elementor page/post ID.',
						),
						'element_id' => array(
							'type'        => 'string',
							'description' => 'Widget element id from get-page-elements or insert-widget\'s element_ids.',
						),
						'settings'   => array(
							'type'        => 'object',
							'description' => 'Keys per describe-widget\'s schema. Merged over the widget\'s existing settings.',
						),
						'dry_run'    => array(
							'type'        => 'boolean',
							'description' => 'Validate and build without saving. The response shows exactly what a real call would do (including ignored[]) but the page is not modified.',
						),
						'force'      => array(
							'type'        => 'boolean',
							'description' => 'Required (true) only when a repeater/list setting is being replaced with an empty list — i.e. you intend to delete all its items. Without it, such an update is refused so a partial write cannot silently wipe a list.',
						),
					),
				),
				'output_schema'       => aThemes_Addons_Ability::envelope_schema(
					array(
						'element_id'   => array( 'type' => 'string' ),
						'widget'       => array( 'type' => 'string' ),
						'updated_keys' => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
						'ignored'      => array(
							'type'        => 'array',
							'description' => 'Skipped settings: {key, reason}.',
						),
						'warnings'     => array(
							'type'        => 'array',
							'items'       => array( 'type' => 'string' ),
							'description' => 'Notes about the write, e.g. group toggles auto-set so a style value takes effect.',
						),
						'dry_run'      => array(
							'type'        => 'boolean',
							'description' => 'True when this was a validation-only run; the page was not modified.',
						),
						'edit_url'     => array( 'type' => 'string' ),
						'preview_url'  => array( 'type' => 'string' ),
						'design_note'  => array(
							'type'        => 'string',
							'description' => 'Reminder to source style values from athemes-addons/get-design-tokens instead of inventing them.',
						),
					)
				),
				'execute_callback'    => array( $this, 'execute_update_widget' ),
				'permission_callback' => array( $this, 'can_insert' ),
				'meta'                => array( 'annotations' => aThemes_Addons_Ability::WRITE_ANNOTATIONS ),
				'write'               => true,
			)
		);

		aThemes_Addons_Ability::register(
			'athemes-addons/remove-widget',
			array(
				'label'               => __( 'Remove an element from a page', 'athemes-addons-for-elementor-lite' ),
				'description'         => __( 'Delete an element from an Elementor page by element_id (find it with athemes-addons/get-page-elements). An aThemes Addons widget that holds no other elements is removed straight away. Anything else — a container, section or column, a non-aThemes widget, or any element that contains other elements — requires force: true, because removing it also removes everything inside it. After the removal, any container/column/section left empty by it is cleaned up automatically; every deleted id is returned in data.removed_ids. This cannot be undone from here, though Elementor keeps a revision.', 'athemes-addons-for-elementor-lite' ),
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'post_id', 'element_id' ),
					'properties' => array(
						'post_id'    => array(
							'type'        => 'integer',
							'description' => 'Elementor page/post ID.',
						),
						'element_id' => array(
							'type'        => 'string',
							'description' => 'Id of the element to remove, from get-page-elements.',
						),
						'force'      => array(
							'type'        => 'boolean',
							'description' => 'Required (true) to remove anything other than a childless aThemes Addons widget — i.e. any layout element, any non-aThemes widget, or any element containing others. Confirms you accept that everything inside it goes too.',
						),
					),
				),
				'output_schema'       => aThemes_Addons_Ability::envelope_schema(
					array(
						'removed_ids' => array(
							'type'        => 'array',
							'items'       => array( 'type' => 'string' ),
							'description' => 'Every element id deleted, including ancestors cleaned up because the removal left them empty.',
						),
						'edit_url'    => array( 'type' => 'string' ),
						'preview_url' => array( 'type' => 'string' ),
					)
				),
				'execute_callback'    => array( $this, 'execute_remove_widget' ),
				'permission_callback' => array( $this, 'can_insert' ),
				'meta'                => array(
					'annotations' => array(
						'readonly'    => false,
						'destructive' => true,
						'idempotent'  => false,
					),
				),
				'write'               => true,
			)
		);

		aThemes_Addons_Ability::register(
			'athemes-addons/move-widget',
			array(
				'label'               => __( 'Move an element on a page', 'athemes-addons-for-elementor-lite' ),
				'description'         => __( 'Reposition an existing element on an Elementor page without rebuilding it. Give the element_id to move and a position (anchor_id + placement) to move it to; find both ids with athemes-addons/get-page-elements. Placement rules match insert-widget: before/after work next to any element except a column; inside_start/inside_end require a container or column anchor. You cannot move an element next to or inside itself or one of its own descendants. On failure the error names the corrective call to make. Moving an element does not clean up the container/column left behind at the source — call athemes-addons/remove-widget on it separately if it should go too.', 'athemes-addons-for-elementor-lite' ),
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'post_id', 'element_id', 'position' ),
					'properties' => array(
						'post_id'    => array(
							'type'        => 'integer',
							'description' => 'Elementor page/post ID.',
						),
						'element_id' => array(
							'type'        => 'string',
							'description' => 'Id of the element to move, from get-page-elements.',
						),
						'position'   => array(
							'type'        => 'object',
							'required'    => array( 'anchor_id', 'placement' ),
							'description' => 'Where to move the element.',
							'properties'  => array(
								'anchor_id' => array(
									'type'        => 'string',
									'description' => 'Element id to move relative to (must not be the element itself or a descendant of it).',
								),
								'placement' => array(
									'type'        => 'string',
									'enum'        => array( 'before', 'after', 'inside_start', 'inside_end' ),
									'description' => 'before/after: sibling of the anchor (any element except a column). inside_start/inside_end: first/last child — anchor must be a container or column.',
								),
							),
						),
					),
				),
				'output_schema'       => aThemes_Addons_Ability::envelope_schema(
					array(
						'element_id'  => array( 'type' => 'string' ),
						'edit_url'    => array( 'type' => 'string' ),
						'preview_url' => array( 'type' => 'string' ),
					)
				),
				'execute_callback'    => array( $this, 'execute_move_widget' ),
				'permission_callback' => array( $this, 'can_insert' ),
				'meta'                => array( 'annotations' => aThemes_Addons_Ability::WRITE_ANNOTATIONS ),
				'write'               => true,
			)
		);
	}

	public function can_insert() {
		return current_user_can( 'edit_posts' ); // Per-post check happens at execute time.
	}

	public function execute_list_pages( $input ) {
		$search    = isset( $input['search'] ) && is_string( $input['search'] ) ? sanitize_text_field( $input['search'] ) : '';
		$post_type = isset( $input['post_type'] ) && is_string( $input['post_type'] ) ? $input['post_type'] : 'page';
		if ( ! in_array( $post_type, array( 'page', 'post', 'any' ), true ) ) {
			$post_type = 'page';
		}

		$limit = isset( $input['limit'] ) ? absint( $input['limit'] ) : 20;
		if ( $limit < 1 ) {
			$limit = 20;
		} elseif ( $limit > 100 ) {
			$limit = 100; // Clamp: keep responses bounded regardless of caller input.
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'any' === $post_type ? array( 'page', 'post' ) : $post_type,
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'meta_key'       => '_elementor_edit_mode', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => 'builder', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				's'              => $search,
				'orderby'        => 'modified',
				'order'          => 'DESC',
				'posts_per_page' => $limit,
				'no_found_rows'  => false,
			)
		);

		$pages = array();
		foreach ( $query->posts as $post ) {
			if ( ! current_user_can( 'edit_post', $post->ID ) ) {
				continue; // Never surface content the caller isn't allowed to edit.
			}

			$pages[] = array(
				'id'       => $post->ID,
				'title'    => get_the_title( $post ),
				'status'   => get_post_status( $post ),
				'type'     => get_post_type( $post ),
				'url'      => get_permalink( $post ),
				'modified' => get_post_modified_time( 'Y-m-d H:i:s', false, $post ),
			);
		}

		return aThemes_Addons_Abilities_Response::success(
			'Pages listed. Use an id as post_id in get-page-elements or insert-widget.',
			array(
				'pages' => $pages,
				'total' => $query->found_posts,
			)
		);
	}

	public function execute_get_page_elements( $input ) {
		$post_id = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;

		if ( ! $post_id && ! empty( $input['url'] ) && is_string( $input['url'] ) ) {
			$post_id = url_to_postid( $input['url'] );
		}

		if ( ! $post_id ) {
			return aThemes_Addons_Abilities_Response::error( 'Provide a valid post_id or a URL belonging to this site.' );
		}

		$document = aThemes_Addons_Widget_Inserter::resolve_editable_document( $post_id );
		if ( is_wp_error( $document ) ) {
			return aThemes_Addons_Abilities_Response::error( $document->get_error_message() );
		}

		$max_nodes = isset( $input['max_nodes'] ) ? (int) $input['max_nodes'] : 500;
		if ( $max_nodes > 2000 ) {
			$max_nodes = 2000; // Clamp: keep responses bounded regardless of caller input.
		}

		$query = aThemes_Addons_Elementor_Tree::query(
			$document->get_elements_data(),
			array(
				'include_settings' => ! empty( $input['include_settings'] ),
				'contains'         => isset( $input['contains'] ) && is_string( $input['contains'] ) ? $input['contains'] : '',
				'max_depth'        => isset( $input['max_depth'] ) ? (int) $input['max_depth'] : 0,
				'max_nodes'        => $max_nodes,
			)
		);

		return aThemes_Addons_Abilities_Response::success(
			'Page structure read. Use any id as insert-widget\'s position.anchor_id.',
			array(
				'post_id'         => $post_id,
				'title'           => get_the_title( $post_id ),
				'uses_containers' => \Elementor\Plugin::$instance->experiments->is_feature_active( 'container' ),
				'elements'        => $query['elements'],
				'truncated'       => $query['truncated'],
			)
		);
	}

	public function execute_insert_widget( $input ) {
		$post_id = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;
		$widgets = isset( $input['widgets'] ) && is_array( $input['widgets'] ) ? array_values( $input['widgets'] ) : array();

		if ( ! $post_id || empty( $widgets ) ) {
			return aThemes_Addons_Abilities_Response::error( 'post_id and a non-empty widgets array are required.' );
		}
		if ( count( $widgets ) > 6 ) {
			return aThemes_Addons_Abilities_Response::error( 'A single row supports at most 6 widgets. Make multiple insert-widget calls for more.' );
		}

		$position = isset( $input['position'] ) && is_array( $input['position'] ) ? $input['position'] : null;
		$dry_run  = ! empty( $input['dry_run'] ) && true === $input['dry_run'];
		$result   = aThemes_Addons_Widget_Inserter::save_to_post( $post_id, $widgets, $position, $dry_run );

		if ( is_wp_error( $result ) ) {
			return aThemes_Addons_Abilities_Response::error( $result->get_error_message() );
		}

		$message = $dry_run
			? sprintf( 'Dry run: would insert %d widget(s). Nothing was saved.', count( $result['element_ids'] ) )
			: sprintf( 'Inserted %d widget(s). The row\'s container id is %s.', count( $result['element_ids'] ), $result['container_id'] );

		$result['design_note'] = self::design_note();

		return aThemes_Addons_Abilities_Response::success( $message, $result );
	}

	public function execute_update_widget( $input ) {
		$post_id    = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;
		$element_id = isset( $input['element_id'] ) && is_string( $input['element_id'] ) ? sanitize_text_field( $input['element_id'] ) : '';
		$settings   = isset( $input['settings'] ) && is_array( $input['settings'] ) ? $input['settings'] : array();
		$dry_run    = ! empty( $input['dry_run'] ) && true === $input['dry_run'];
		$force      = ! empty( $input['force'] ) && true === $input['force'];

		if ( ! $post_id || '' === $element_id || empty( $settings ) ) {
			return aThemes_Addons_Abilities_Response::error( 'post_id, element_id and a non-empty settings object are required.' );
		}

		$result = aThemes_Addons_Widget_Inserter::update_in_post( $post_id, $element_id, $settings, $dry_run, $force );

		if ( is_wp_error( $result ) ) {
			return aThemes_Addons_Abilities_Response::error( $result->get_error_message() );
		}

		$message = $dry_run
			? sprintf( 'Dry run: would update %d setting(s) on element %s. Nothing was saved.', count( $result['updated_keys'] ), $result['element_id'] )
			: sprintf( 'Updated %d setting(s) on element %s.', count( $result['updated_keys'] ), $result['element_id'] );

		$result['design_note'] = self::design_note();

		return aThemes_Addons_Abilities_Response::success( $message, $result );
	}

	public function execute_remove_widget( $input ) {
		$post_id    = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;
		$element_id = isset( $input['element_id'] ) && is_string( $input['element_id'] ) ? sanitize_text_field( $input['element_id'] ) : '';
		$force      = ! empty( $input['force'] ) && true === $input['force'];

		if ( ! $post_id || '' === $element_id ) {
			return aThemes_Addons_Abilities_Response::error( 'post_id and element_id are required.' );
		}

		$result = aThemes_Addons_Widget_Inserter::remove_from_post( $post_id, $element_id, $force );

		if ( is_wp_error( $result ) ) {
			return aThemes_Addons_Abilities_Response::error( $result->get_error_message() );
		}

		return aThemes_Addons_Abilities_Response::success(
			sprintf( 'Removed %d element(s).', count( $result['removed_ids'] ) ),
			$result
		);
	}

	public function execute_move_widget( $input ) {
		$post_id    = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;
		$element_id = isset( $input['element_id'] ) && is_string( $input['element_id'] ) ? sanitize_text_field( $input['element_id'] ) : '';
		$position   = isset( $input['position'] ) && is_array( $input['position'] ) ? $input['position'] : null;

		if ( ! $post_id || '' === $element_id || empty( $position ) ) {
			return aThemes_Addons_Abilities_Response::error( 'post_id, element_id and a position object (anchor_id + placement) are required.' );
		}

		$result = aThemes_Addons_Widget_Inserter::move_in_post( $post_id, $element_id, $position );

		if ( is_wp_error( $result ) ) {
			return aThemes_Addons_Abilities_Response::error( $result->get_error_message() );
		}

		return aThemes_Addons_Abilities_Response::success(
			sprintf( 'Moved element %s.', $result['element_id'] ),
			$result
		);
	}
}
