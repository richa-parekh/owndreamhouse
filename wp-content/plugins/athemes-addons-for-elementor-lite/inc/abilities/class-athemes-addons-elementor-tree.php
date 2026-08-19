<?php
/**
 * Pure array operations on Elementor _elementor_data trees.
 *
 * @package aThemes_Addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class aThemes_Addons_Elementor_Tree {

	const TEXT_KEYS = array( 'title', 'heading', 'title_text', 'name', 'text', 'editor', 'content', 'description' );

	public static function distill( array $elements ) {
		$out = array();
		foreach ( $elements as $el ) {
			$node = array(
				'id'   => isset( $el['id'] ) ? $el['id'] : '',
				'type' => ( isset( $el['elType'] ) && 'widget' === $el['elType'] && isset( $el['widgetType'] ) ) ? $el['widgetType'] : ( isset( $el['elType'] ) ? $el['elType'] : '' ),
			);
			$text = self::extract_text( isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array() );
			if ( '' !== $text ) {
				$node['text'] = $text;
			}
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				$node['children'] = self::distill( $el['elements'] );
			}
			$out[] = $node;
		}
		return $out;
	}

	/**
	 * A richer, bounded read of the tree for get-page-elements.
	 *
	 * @param array $elements Raw Elementor elements.
	 * @param array $args     include_settings (bool), contains (string),
	 *                        max_depth (int, 0 = unlimited), max_nodes (int).
	 * @return array {elements: array, truncated: bool}
	 */
	public static function query( array $elements, array $args ) {
		$include_settings = ! empty( $args['include_settings'] );
		$contains         = isset( $args['contains'] ) && is_string( $args['contains'] ) && '' !== $args['contains'] ? self::lower( $args['contains'] ) : '';
		$max_depth        = isset( $args['max_depth'] ) ? (int) $args['max_depth'] : 0;
		$max_nodes        = isset( $args['max_nodes'] ) ? (int) $args['max_nodes'] : 500;
		if ( $max_nodes <= 0 ) {
			$max_nodes = 500;
		}

		$truncated = false;
		$tree      = self::build_query_nodes( $elements, $include_settings, $max_depth, 1, $truncated );

		if ( '' !== $contains ) {
			$tree = self::prune_contains( $tree, $contains );
		}

		$count = 0;
		$tree  = self::cap_nodes( $tree, $max_nodes, $count, $truncated );

		return array(
			'elements'  => $tree,
			'truncated' => $truncated,
		);
	}

	private static function build_query_nodes( array $elements, $include_settings, $max_depth, $depth, &$truncated ) {
		$out = array();
		foreach ( $elements as $el ) {
			$node = array(
				'id'   => isset( $el['id'] ) ? $el['id'] : '',
				'type' => ( isset( $el['elType'] ) && 'widget' === $el['elType'] && isset( $el['widgetType'] ) ) ? $el['widgetType'] : ( isset( $el['elType'] ) ? $el['elType'] : '' ),
			);
			$text = self::extract_text( isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array() );
			if ( '' !== $text ) {
				$node['text'] = $text;
			}
			if ( $include_settings && isset( $el['elType'] ) && 'widget' === $el['elType'] ) {
				$node['settings'] = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();
			}
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				if ( $max_depth > 0 && $depth >= $max_depth ) {
					$truncated = true; // Deeper nodes dropped.
				} else {
					$children = self::build_query_nodes( $el['elements'], $include_settings, $max_depth, $depth + 1, $truncated );
					if ( ! empty( $children ) ) {
						$node['children'] = $children;
					}
				}
			}
			$out[] = $node;
		}
		return $out;
	}

	private static function prune_contains( array $nodes, $needle ) {
		$out = array();
		foreach ( $nodes as $node ) {
			$children = isset( $node['children'] ) ? self::prune_contains( $node['children'], $needle ) : array();
			if ( self::node_matches( $node, $needle ) || ! empty( $children ) ) {
				if ( array_key_exists( 'children', $node ) ) {
					if ( ! empty( $children ) ) {
						$node['children'] = $children;
					} else {
						unset( $node['children'] );
					}
				}
				$out[] = $node;
			}
		}
		return $out;
	}

	private static function node_matches( array $node, $needle ) {
		foreach ( array( 'text', 'id', 'type' ) as $key ) {
			if ( isset( $node[ $key ] ) && is_string( $node[ $key ] ) && false !== strpos( self::lower( $node[ $key ] ), $needle ) ) {
				return true;
			}
		}
		return false;
	}

	private static function cap_nodes( array $nodes, $max, &$count, &$truncated ) {
		$out = array();
		foreach ( $nodes as $node ) {
			if ( $count >= $max ) {
				$truncated = true;
				break;
			}
			$count++;
			if ( isset( $node['children'] ) ) {
				$node['children'] = self::cap_nodes( $node['children'], $max, $count, $truncated );
				if ( empty( $node['children'] ) ) {
					unset( $node['children'] );
				}
			}
			$out[] = $node;
		}
		return $out;
	}

	private static function lower( $str ) {
		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $str ) : strtolower( $str );
	}

	public static function find( array $elements, $anchor_id, $parent_type = 'root' ) {
		foreach ( $elements as $el ) {
			if ( isset( $el['id'] ) && $el['id'] === $anchor_id ) {
				return array(
					'element'     => $el,
					'parent_type' => $parent_type,
				);
			}
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				$hit = self::find( $el['elements'], $anchor_id, isset( $el['elType'] ) ? $el['elType'] : '' );
				if ( null !== $hit ) {
					return $hit;
				}
			}
		}
		return null;
	}

	public static function insert_relative( array $elements, $anchor_id, $placement, array $new_element ) {
		$result = self::walk_insert( $elements, $anchor_id, $placement, $new_element );
		return $result['found'] ? $result['tree'] : null;
	}

	private static function walk_insert( array $elements, $anchor_id, $placement, array $new_element ) {
		$out   = array();
		$found = false;

		foreach ( $elements as $el ) {
			$is_anchor = isset( $el['id'] ) && $el['id'] === $anchor_id;

			if ( $is_anchor && 'before' === $placement ) {
				$out[] = $new_element;
				$out[] = $el;
				$found = true;
				continue;
			}

			if ( $is_anchor && 'after' === $placement ) {
				$out[] = $el;
				$out[] = $new_element;
				$found = true;
				continue;
			}

			if ( $is_anchor && in_array( $placement, array( 'inside_start', 'inside_end' ), true ) ) {
				$el_type = isset( $el['elType'] ) ? $el['elType'] : '';
				if ( ! in_array( $el_type, array( 'container', 'column' ), true ) ) {
					$out[] = $el; // Invalid target: widgets hold no children, sections need columns.
					continue;
				}
				$children = isset( $el['elements'] ) && is_array( $el['elements'] ) ? $el['elements'] : array();
				if ( 'inside_start' === $placement ) {
					array_unshift( $children, $new_element );
				} else {
					$children[] = $new_element;
				}
				$el['elements'] = $children;
				$out[]          = $el;
				$found          = true;
				continue;
			}

			if ( ! $found && ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				$child_result = self::walk_insert( $el['elements'], $anchor_id, $placement, $new_element );
				if ( $child_result['found'] ) {
					$el['elements'] = $child_result['tree'];
					$found          = true;
				}
			}

			$out[] = $el;
		}

		return array(
			'tree'  => $out,
			'found' => $found,
		);
	}

	/**
	 * Replace an element's settings wholesale. The caller is responsible
	 * for merging over the current settings first.
	 *
	 * @return array|null New tree, or null when the id is not found.
	 */
	public static function set_widget_settings( array $elements, $target_id, array $settings ) {
		foreach ( $elements as $i => $el ) {
			if ( isset( $el['id'] ) && $el['id'] === $target_id ) {
				$elements[ $i ]['settings'] = $settings;
				return $elements;
			}
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				$child = self::set_widget_settings( $el['elements'], $target_id, $settings );
				if ( null !== $child ) {
					$elements[ $i ]['elements'] = $child;
					return $elements;
				}
			}
		}
		return null;
	}

	/**
	 * Remove an element by id. With cleanup, structural ancestors left empty
	 * by the removal are pruned recursively upward.
	 *
	 * @param array  $elements  Raw tree.
	 * @param string $target_id Id to remove.
	 * @param bool   $cleanup   Prune now-empty containers/columns/sections.
	 * @return array|null {tree: array, removed: string[]} or null when not found.
	 */
	public static function remove( array $elements, $target_id, $cleanup = true ) {
		$removed = array();
		$tree    = self::prune_recursive( $elements, $target_id, $cleanup, $removed );

		if ( ! in_array( $target_id, $removed, true ) ) {
			return null;
		}

		return array(
			'tree'    => $tree,
			'removed' => $removed,
		);
	}

	private static function prune_recursive( array $elements, $target_id, $cleanup, &$removed ) {
		$out = array();
		foreach ( $elements as $el ) {
			if ( isset( $el['id'] ) && $el['id'] === $target_id ) {
				$removed[] = $el['id'];
				continue;
			}
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				$had            = count( $el['elements'] );
				$el['elements'] = self::prune_recursive( $el['elements'], $target_id, $cleanup, $removed );
				if ( $cleanup && $had > 0 && 0 === count( $el['elements'] ) && self::is_structural( $el ) ) {
					$removed[] = isset( $el['id'] ) ? $el['id'] : '';
					continue; // Cascade: this ancestor emptied by the removal, drop it too.
				}
			}
			$out[] = $el;
		}
		return $out;
	}

	private static function is_structural( array $el ) {
		$type = isset( $el['elType'] ) ? $el['elType'] : '';
		return in_array( $type, array( 'container', 'column', 'section' ), true );
	}

	/**
	 * Move an element to a new position relative to an anchor.
	 *
	 * @param array  $elements  Raw tree.
	 * @param string $target_id Element to move.
	 * @param string $anchor_id Element to move relative to.
	 * @param string $placement before|after|inside_start|inside_end.
	 * @return array|string New tree, or an error code.
	 */
	public static function move( array $elements, $target_id, $anchor_id, $placement ) {
		$target_hit = self::find( $elements, $target_id );
		if ( null === $target_hit ) {
			return 'target_not_found';
		}

		if ( $anchor_id === $target_id ) {
			return 'cannot_move_into_self';
		}

		// Reject moving a subtree into its own descendant (would create a cycle).
		$subtree = isset( $target_hit['element']['elements'] ) && is_array( $target_hit['element']['elements'] ) ? $target_hit['element']['elements'] : array();
		if ( null !== self::find( $subtree, $anchor_id ) ) {
			return 'cannot_move_into_self';
		}

		$anchor_hit = self::find( $elements, $anchor_id );
		if ( null === $anchor_hit ) {
			return 'anchor_not_found';
		}

		$anchor_type = isset( $anchor_hit['element']['elType'] ) ? $anchor_hit['element']['elType'] : '';

		if ( in_array( $placement, array( 'before', 'after' ), true ) ) {
			if ( 'column' === $anchor_type ) {
				return 'invalid_placement_target';
			}
		} elseif ( in_array( $placement, array( 'inside_start', 'inside_end' ), true ) ) {
			if ( ! in_array( $anchor_type, array( 'container', 'column' ), true ) ) {
				return 'invalid_placement_target';
			}
		} else {
			return 'invalid_placement_target';
		}

		// Detach the target (no cleanup: a move must not also prune the source).
		$detached = self::remove( $elements, $target_id, false );
		if ( null === $detached ) {
			return 'target_not_found';
		}

		$tree = self::insert_relative( $detached['tree'], $anchor_id, $placement, $target_hit['element'] );
		if ( null === $tree ) {
			return 'invalid_placement_target';
		}

		return $tree;
	}

	private static function extract_text( array $settings ) {
		foreach ( self::TEXT_KEYS as $key ) {
			if ( ! empty( $settings[ $key ] ) && is_string( $settings[ $key ] ) ) {
				$text = trim( wp_strip_all_tags( $settings[ $key ] ) );
				if ( '' !== $text ) {
					return function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 80 ) : substr( $text, 0, 80 );
				}
			}
		}
		return '';
	}
}
