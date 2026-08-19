<?php
/**
 * Display conditions ajax callback
 */

/**
 * Get labels for display condition IDs.
 *
 * @param array $conditions Array of conditions with 'condition' and 'id' keys.
 * @return array Array of id => label mappings.
 */
function athemes_addons_get_display_conditions_labels( $conditions ) {
	$labels = array();

	if ( empty( $conditions ) || ! is_array( $conditions ) ) {
		return $labels;
	}

	foreach ( $conditions as $condition ) {
		if ( empty( $condition['id'] ) || empty( $condition['condition'] ) ) {
			continue;
		}

		$id     = $condition['id'];
		$source = $condition['condition'];
		$label  = '';

		switch ( $source ) {
			case 'post-id':
			case 'page-id':
			case 'product-id':
			case 'cpt-post-id':
				$post = get_post( $id );
				if ( $post ) {
					$label = esc_html( $post->post_title );
				}
				break;

			case 'tag-id':
				$term = get_term( $id, 'post_tag' );
				if ( $term && ! is_wp_error( $term ) ) {
					$label = esc_html( $term->name );
				}
				break;

			case 'category-id':
				$term = get_term( $id, 'category' );
				if ( $term && ! is_wp_error( $term ) ) {
					$label = esc_html( $term->name );
				}
				break;

			case 'author':
			case 'author-id':
				$user = get_user_by( 'id', $id );
				if ( $user ) {
					$label = esc_html( $user->display_name );
				}
				break;

			case 'cpt-term-id':
				$term = get_term( $id );
				if ( $term && ! is_wp_error( $term ) ) {
					$label = esc_html( $term->name );
				}
				break;

			case 'cpt-taxonomy-id':
				$taxonomy = get_taxonomy( $id );
				if ( $taxonomy ) {
					$label = esc_html( $taxonomy->label );
				}
				break;
		}

		if ( ! empty( $label ) ) {
			$labels[ $id ] = $label;
		}
	}

	return $labels;
}

function athemes_addons_templates_display_conditions_select_ajax() {

    $term   = ( isset( $_GET['term'] ) ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
    $nonce  = ( isset( $_GET['nonce'] ) ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';
    $source = ( isset( $_GET['source'] ) ) ? sanitize_text_field( wp_unslash( $_GET['source'] ) ) : '';

    if ( ! empty( $term ) && ! empty( $source ) && ! empty( $nonce ) && wp_verify_nonce( $nonce, 'athemes-addons-elementor' ) ) {

        $options = array();

        switch ( $source ) {

            case 'post-id':
            case 'page-id':
            case 'product-id':
                $post_type = 'post';

                if ( $source === 'page-id' ) {
                    $post_type = 'page';
                }

                if ( $source === 'product-id' ) {
                    $post_type = 'product';
                }

                $query = new WP_Query( array(
                    's'              => $term,
                    'post_type'      => $post_type,
                    'post_status'    => 'publish',
                    'posts_per_page' => 25,
                    'order'          => 'DESC',
                ) );

                if ( ! empty( $query->posts ) ) {
                    foreach( $query->posts as $post ) {
                        $options[] = array(
                            'id'   => $post->ID,
                            'text' => $post->post_title,
                        );
                    }
                }
    
                break;

            case 'tag-id':
            case 'category-id':
                $taxonomy = 'category';

                if ( $source === 'tag-id' ) {
                    $taxonomy = 'post_tag';
                }

                $query = new WP_Term_Query( array(
                    'search'     => $term,
                    'taxonomy'   => $taxonomy,
                    'number'     => 25,
                    'hide_empty' => false,
                ) );
            
                if ( ! empty( $query->terms ) ) {
                    foreach ( $query->terms as $term ) {
                        $options[] = array(
                            'id'   => $term->term_id,
                            'text' => $term->name,
                        );
                    }
                }

                break;

            case 'author':
            case 'author-id':
                $query      = new WP_User_Query( array(
                    'search'  => '*'. $term .'*',
                    'number'  => 25,
                    'order'   => 'DESC',
                    'fields'  => array( 'display_name', 'ID' ),
                ) );
                
                $authors = $query->get_results();

                if ( ! empty( $authors ) ) {
                    foreach ( $authors as $author ) {
                        $options[] = array(
                            'id'   => $author->ID,
                            'text' => $author->display_name,
                        );
                    }
                }

                break;

            case 'cpt-post-id':
                $post_types = get_post_types( array( 'show_in_nav_menus' => true ), 'objects' );

                if ( ! empty( $post_types ) ) {
                    foreach ( $post_types as $post_type_key => $post_type ) {
                        if ( in_array( $post_type_key, array( 'post', 'page' ), true ) ) {
                            continue;
                        }
                        $query = new WP_Query( array(
                            's'              => $term,
                            'post_type'      => $post_type_key,
                            'post_status'    => 'publish',
                            'posts_per_page' => 25,
                            'order'          => 'DESC',
                        ) );
                        if ( ! empty( $query->posts ) ) {
                            foreach( $query->posts as $post ) {
                                $options[] = array(
                                    'id'   => $post->ID,
                                    'text' => $post->post_title,
                                );
                            }
                        }
                    }
                }

                break;

            case 'cpt-term-id':
                $terms = get_terms( array(
                    'search'     => $term,
                    'number'     => 25,
                    'hide_empty' => false,
                ) );

                if ( ! empty( $terms ) ) {
                    foreach ( $terms as $term ) {
                        if ( in_array( $term->taxonomy, array( 'category', 'post_tag' ), true ) ) {
                            continue;
                        }
                        $taxonomy = get_taxonomy( $term->taxonomy );
                        if ( $taxonomy->show_in_nav_menus ) {
                            $options[] = array(
                                'id'   => $term->term_id,
                                'text' => $term->name,
                            );
                        }
                    }
                }

                break;

            case 'cpt-taxonomy-id':
                $taxonomies = get_taxonomies( array( 'show_in_nav_menus' => true ), 'objects' );

                if ( ! empty( $taxonomies ) ) {
                    foreach ( $taxonomies as $taxonomy_key => $taxonomy ) {
                        if ( in_array( $taxonomy_key, array( 'category', 'post_tag', 'post_format' ), true ) ) {
                            continue;
                        }
                        if ( preg_match( '/'. strtolower( $term ) .'/', strtolower( $taxonomy->label ) ) ) {
                            $options[] = array(
                                'id'   => $taxonomy_key,
                                'text' => $taxonomy->label,
                            );
                        }
                    }
                }

                break;

        }

        wp_send_json_success( $options );

    } else {

        wp_send_json_error();

    }
}
add_action( 'wp_ajax_athemes_addons_templates_display_conditions_select_ajax', 'athemes_addons_templates_display_conditions_select_ajax' );