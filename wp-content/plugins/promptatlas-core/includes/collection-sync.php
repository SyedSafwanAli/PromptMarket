<?php
/**
 * Keep the collection reverse index in sync.
 *
 * A collection stores its prompts as one ordered array
 * (_promptatlas_collection_prompts), which preserves the editorial order but
 * cannot be queried from the other direction: "which collections contain prompt
 * 134?" is not answerable from a serialised array without a LIKE scan, which is
 * both slow and wrong (it would match ID 1341 too).
 *
 * So each prompt also carries one _promptatlas_in_collection row per collection
 * containing it. That makes the reverse lookup a plain, indexed meta query.
 *
 * The cost of this design is that the two sides can drift, so this file is the
 * single place that writes the reverse index, and it is called from every path
 * that can change a collection: save, trash, untrash and delete.
 *
 * CONTENT_STRATEGY.md 5.2 flags this sync as the one part of the model that
 * genuinely needs testing.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rewrite the reverse index for one collection.
 *
 * Removes this collection's ID from every prompt that currently carries it,
 * then adds it to each prompt in the supplied list. Clearing first is what
 * makes removing a prompt from a collection actually take effect.
 *
 * @param int   $collection_id Collection post ID.
 * @param int[] $prompt_ids    Prompt IDs now in the collection.
 * @return void
 */
function promptatlas_sync_collection_reverse_index( $collection_id, $prompt_ids ) {
	$collection_id = absint( $collection_id );

	if ( 0 === $collection_id ) {
		return;
	}

	// Every prompt currently claiming membership of this collection.
	$existing = get_posts(
		array(
			'post_type'              => 'prompt',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'suppress_filters'       => false,
			'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Reverse-index maintenance on a single collection save; the alternative is a LIKE scan of serialised IDs, which is both slower and incorrect.
				array(
					'key'     => PROMPTATLAS_META_IN_COLLECTION,
					'value'   => $collection_id,
					'compare' => '=',
					'type'    => 'NUMERIC',
				),
			),
		)
	);

	foreach ( $existing as $prompt_id ) {
		delete_post_meta( (int) $prompt_id, PROMPTATLAS_META_IN_COLLECTION, $collection_id );
	}

	foreach ( (array) $prompt_ids as $prompt_id ) {
		$prompt_id = absint( $prompt_id );

		if ( 0 === $prompt_id || 'prompt' !== get_post_type( $prompt_id ) ) {
			continue;
		}

		add_post_meta( $prompt_id, PROMPTATLAS_META_IN_COLLECTION, $collection_id );
	}
}

/**
 * Drop a collection from the reverse index entirely.
 *
 * @param int $collection_id Collection post ID.
 * @return void
 */
function promptatlas_clear_collection_reverse_index( $collection_id ) {
	promptatlas_sync_collection_reverse_index( $collection_id, array() );
}

/**
 * Remove a trashed collection from the reverse index.
 *
 * A trashed collection is not public, so a prompt must stop advertising it.
 * The collection's own ordered list is left untouched, so restoring it restores
 * the membership.
 *
 * @param int $post_id Post being trashed.
 * @return void
 */
function promptatlas_on_collection_trashed( $post_id ) {
	if ( 'collection' !== get_post_type( $post_id ) ) {
		return;
	}

	promptatlas_clear_collection_reverse_index( $post_id );
}
add_action( 'trashed_post', 'promptatlas_on_collection_trashed' );

/**
 * Rebuild the reverse index when a collection comes back from the trash.
 *
 * @param int $post_id Post being untrashed.
 * @return void
 */
function promptatlas_on_collection_untrashed( $post_id ) {
	if ( 'collection' !== get_post_type( $post_id ) ) {
		return;
	}

	$prompt_ids = get_post_meta( $post_id, PROMPTATLAS_META_COLLECTION_PROMPTS, true );

	promptatlas_sync_collection_reverse_index( $post_id, is_array( $prompt_ids ) ? $prompt_ids : array() );
}
add_action( 'untrashed_post', 'promptatlas_on_collection_untrashed' );

/**
 * Remove a permanently deleted collection from the reverse index.
 *
 * Without this, prompts keep a meta row pointing at a post ID that no longer
 * exists, and the frontend would render an empty collection link.
 *
 * @param int $post_id Post being deleted.
 * @return void
 */
function promptatlas_on_collection_deleted( $post_id ) {
	if ( 'collection' !== get_post_type( $post_id ) ) {
		return;
	}

	promptatlas_clear_collection_reverse_index( $post_id );
}
add_action( 'before_delete_post', 'promptatlas_on_collection_deleted' );

/**
 * Remove a deleted prompt from every collection's ordered list.
 *
 * The other direction of the same drift problem: a collection holding the ID of
 * a deleted prompt would render a missing entry.
 *
 * @param int $post_id Post being deleted.
 * @return void
 */
function promptatlas_on_prompt_deleted( $post_id ) {
	if ( 'prompt' !== get_post_type( $post_id ) ) {
		return;
	}

	$post_id = absint( $post_id );

	$collections = get_posts(
		array(
			'post_type'              => 'collection',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);

	foreach ( $collections as $collection_id ) {
		$prompt_ids = get_post_meta( (int) $collection_id, PROMPTATLAS_META_COLLECTION_PROMPTS, true );

		if ( ! is_array( $prompt_ids ) || ! in_array( $post_id, array_map( 'absint', $prompt_ids ), true ) ) {
			continue;
		}

		$updated = array_values( array_diff( array_map( 'absint', $prompt_ids ), array( $post_id ) ) );

		if ( empty( $updated ) ) {
			delete_post_meta( (int) $collection_id, PROMPTATLAS_META_COLLECTION_PROMPTS );
		} else {
			update_post_meta( (int) $collection_id, PROMPTATLAS_META_COLLECTION_PROMPTS, $updated );
		}
	}
}
add_action( 'before_delete_post', 'promptatlas_on_prompt_deleted' );
