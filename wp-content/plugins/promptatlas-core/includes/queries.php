<?php
/**
 * Content relationship queries.
 *
 * Read-side helpers the Phase 08 templates consume. Kept in the plugin rather
 * than the theme because the relationships are part of the content model: a
 * theme switch must not change what "related" means.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get related prompts for a prompt.
 *
 * Implements CONTENT_STRATEGY.md 4.8 exactly:
 *   1. Manual overrides first, in the order the editor set.
 *   2. Then automatic: same category, ranked by count of shared tags
 *      descending, then by most recently modified.
 *   3. Up to $limit. Never padded with unrelated prompts - 2 genuinely related
 *      prompts beats 4 with filler.
 *
 * @param int $post_id Prompt ID.
 * @param int $limit   Maximum to return.
 * @return int[] Related prompt IDs.
 */
function promptatlas_get_related_prompts( $post_id, $limit = 4 ) {
	$post_id = absint( $post_id );
	$limit   = max( 1, absint( $limit ) );

	if ( 0 === $post_id ) {
		return array();
	}

	// 1. Manual overrides.
	$manual = get_post_meta( $post_id, PROMPTATLAS_META_RELATED, true );
	$manual = is_array( $manual ) ? array_map( 'absint', $manual ) : array();

	$related = array();

	foreach ( $manual as $id ) {
		if ( $id === $post_id || 'publish' !== get_post_status( $id ) ) {
			continue;
		}

		$related[] = $id;
	}

	if ( count( $related ) >= $limit ) {
		return array_slice( $related, 0, $limit );
	}

	// 2. Automatic: same category.
	$categories = wp_get_object_terms( $post_id, 'prompt_category', array( 'fields' => 'ids' ) );

	if ( is_wp_error( $categories ) || empty( $categories ) ) {
		return $related;
	}

	$exclude = array_merge( $related, array( $post_id ) );

	$candidates = get_posts(
		array(
			'post_type'              => 'prompt',
			'post_status'            => 'publish',
			'posts_per_page'         => 30,
			'fields'                 => 'ids',
			'post__not_in'           => $exclude,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => true,
			'orderby'                => 'modified',
			'order'                  => 'DESC',
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Taxonomy relationship is the definition of "related"; there is no non-taxonomy way to express it.
				array(
					'taxonomy' => 'prompt_category',
					'field'    => 'term_id',
					'terms'    => $categories,
				),
			),
		)
	);

	if ( empty( $candidates ) ) {
		return $related;
	}

	// Rank by shared tag count. The candidates are already ordered by modified
	// date descending, and PHP's sort is stable as of PHP 8.0, so equal scores
	// keep that order - which is exactly the documented tie-break.
	$own_tags = wp_get_object_terms( $post_id, 'prompt_tag', array( 'fields' => 'ids' ) );
	$own_tags = is_wp_error( $own_tags ) ? array() : $own_tags;

	$scored = array();

	foreach ( $candidates as $candidate_id ) {
		$candidate_tags = wp_get_object_terms( (int) $candidate_id, 'prompt_tag', array( 'fields' => 'ids' ) );
		$candidate_tags = is_wp_error( $candidate_tags ) ? array() : $candidate_tags;

		$scored[] = array(
			'id'    => (int) $candidate_id,
			'score' => count( array_intersect( $own_tags, $candidate_tags ) ),
		);
	}

	usort(
		$scored,
		static function ( $a, $b ) {
			return $b['score'] <=> $a['score'];
		}
	);

	foreach ( $scored as $entry ) {
		if ( count( $related ) >= $limit ) {
			break;
		}

		$related[] = $entry['id'];
	}

	return $related;
}

/**
 * Get the collections containing a prompt.
 *
 * Uses the reverse index maintained by includes/collection-sync.php.
 *
 * @param int $post_id Prompt ID.
 * @return int[] Published collection IDs.
 */
function promptatlas_get_collections_for_prompt( $post_id ) {
	$post_id = absint( $post_id );

	if ( 0 === $post_id ) {
		return array();
	}

	$collection_ids = get_post_meta( $post_id, PROMPTATLAS_META_IN_COLLECTION, false );

	if ( empty( $collection_ids ) || ! is_array( $collection_ids ) ) {
		return array();
	}

	$published = array();

	foreach ( $collection_ids as $collection_id ) {
		$collection_id = absint( $collection_id );

		if ( 0 === $collection_id || 'publish' !== get_post_status( $collection_id ) ) {
			continue;
		}

		$published[] = $collection_id;
	}

	return array_values( array_unique( $published ) );
}

/**
 * Get the prompts in a collection, in editorial order.
 *
 * Order is meaningful, so this deliberately does not re-sort. Unpublished
 * prompts are dropped rather than rendered as dead entries.
 *
 * @param int $collection_id Collection ID.
 * @return int[] Published prompt IDs in editorial order.
 */
function promptatlas_get_collection_prompts( $collection_id ) {
	$collection_id = absint( $collection_id );

	if ( 0 === $collection_id ) {
		return array();
	}

	$prompt_ids = get_post_meta( $collection_id, PROMPTATLAS_META_COLLECTION_PROMPTS, true );

	if ( ! is_array( $prompt_ids ) ) {
		return array();
	}

	$published = array();

	foreach ( $prompt_ids as $prompt_id ) {
		$prompt_id = absint( $prompt_id );

		if ( 0 === $prompt_id || 'publish' !== get_post_status( $prompt_id ) ) {
			continue;
		}

		$published[] = $prompt_id;
	}

	return $published;
}

/**
 * Get a prompt's single category term.
 *
 * The model requires exactly one category (DECISIONS.md decision 12), but the
 * database cannot enforce that, so this returns the first and the templates
 * stay simple.
 *
 * @param int $post_id Prompt ID.
 * @return WP_Term|null The category, or null when none is assigned.
 */
function promptatlas_get_prompt_category( $post_id ) {
	$terms = wp_get_object_terms( absint( $post_id ), 'prompt_category' );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return null;
	}

	return $terms[0];
}

/**
 * Get collections that contain prompts from a given term.
 *
 * Powers the "collections for this category" block on taxonomy archives
 * (Phase 11 step 3). Works off the reverse index, so it is a meta lookup rather
 * than a scan of every collection's serialised ID list.
 *
 * @param int    $term_id  Term ID.
 * @param string $taxonomy Taxonomy name.
 * @param int    $limit    Maximum collections to return.
 * @return int[] Published collection IDs, most relevant first.
 */
function promptatlas_get_collections_for_term( $term_id, $taxonomy, $limit = 3 ) {
	$term_id = absint( $term_id );
	$limit   = max( 1, absint( $limit ) );

	if ( 0 === $term_id ) {
		return array();
	}

	$prompt_ids = get_posts(
		array(
			'post_type'              => 'prompt',
			'post_status'            => 'publish',
			'posts_per_page'         => 50,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- The term relationship is the question being asked; there is no non-taxonomy way to express it.
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $term_id,
				),
			),
		)
	);

	if ( empty( $prompt_ids ) ) {
		return array();
	}

	// Count how many of this term's prompts each collection contains, so the
	// most relevant collection is offered first rather than an arbitrary one.
	$scores = array();

	foreach ( $prompt_ids as $prompt_id ) {
		foreach ( promptatlas_get_collections_for_prompt( (int) $prompt_id ) as $collection_id ) {
			if ( ! isset( $scores[ $collection_id ] ) ) {
				$scores[ $collection_id ] = 0;
			}

			++$scores[ $collection_id ];
		}
	}

	if ( empty( $scores ) ) {
		return array();
	}

	arsort( $scores );

	return array_slice( array_keys( $scores ), 0, $limit );
}

/**
 * Get collections related to a collection.
 *
 * Related means "shares prompts with this one", which is the only honest signal
 * available: two collections drawing on the same prompts genuinely serve
 * adjacent needs. Collections sharing nothing are not offered, rather than
 * padding the list with whatever was published most recently.
 *
 * @param int $collection_id Collection ID.
 * @param int $limit         Maximum to return.
 * @return int[] Published collection IDs.
 */
function promptatlas_get_related_collections( $collection_id, $limit = 3 ) {
	$collection_id = absint( $collection_id );
	$limit         = max( 1, absint( $limit ) );

	if ( 0 === $collection_id ) {
		return array();
	}

	$own_prompts = promptatlas_get_collection_prompts( $collection_id );

	if ( empty( $own_prompts ) ) {
		return array();
	}

	$scores = array();

	foreach ( $own_prompts as $prompt_id ) {
		foreach ( promptatlas_get_collections_for_prompt( (int) $prompt_id ) as $other_id ) {
			if ( (int) $other_id === $collection_id ) {
				continue;
			}

			if ( ! isset( $scores[ $other_id ] ) ) {
				$scores[ $other_id ] = 0;
			}

			++$scores[ $other_id ];
		}
	}

	if ( empty( $scores ) ) {
		return array();
	}

	arsort( $scores );

	return array_slice( array_keys( $scores ), 0, $limit );
}
