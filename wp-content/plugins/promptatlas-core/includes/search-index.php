<?php
/**
 * Plain-text search index.
 *
 * ---------------------------------------------------------------------------
 * Why this exists
 * ---------------------------------------------------------------------------
 * Searching `post_content` directly means searching the stored HTML, so the
 * markup is searchable too. This was not theoretical: the résumé prompt's
 * explanation uses <code>[JOB_TITLE]</code> to mark up placeholder names, which
 * made a search for "code" match it. Every prompt that formats a placeholder
 * would have been a false positive for one of the most common searches on a
 * site about prompts.
 *
 * So each prompt and collection keeps one meta field holding a plain-text
 * version of everything worth searching:
 *
 *   excerpt + tag-stripped content + prompt text + taxonomy term names
 *
 * Search then reads the title and this one field instead of four columns. That
 * fixes the precision problem, makes category and tool names searchable for
 * free (Phase 09 asks for both), and halves the number of LIKE conditions per
 * term.
 *
 * The cost is one meta row per post and a rebuild on save. Both are cheap, and
 * the index is derived data: it can be rebuilt from the content at any time and
 * never needs to be backed up or migrated.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta key holding the plain-text search index.
 */
const PROMPTATLAS_META_SEARCH_BLOB = '_promptatlas_search_blob';

/**
 * Register the search index meta field.
 *
 * Not exposed in REST and not editable: it is derived from other fields, and
 * letting it be written directly would let it drift from the content it
 * describes.
 *
 * @return void
 */
function promptatlas_register_search_meta() {
	foreach ( array( 'prompt', 'collection' ) as $post_type ) {
		register_post_meta(
			$post_type,
			PROMPTATLAS_META_SEARCH_BLOB,
			array(
				'type'              => 'string',
				'description'       => __( 'Derived plain-text search index. Rebuilt automatically.', 'promptatlas' ),
				'single'            => true,
				'default'           => '',
				'show_in_rest'      => false,
				'sanitize_callback' => 'promptatlas_sanitize_multiline_text',
				'auth_callback'     => '__return_false',
			)
		);
	}
}
add_action( 'init', 'promptatlas_register_search_meta', 8 );

/**
 * Build the plain-text search index for one post.
 *
 * @param int $post_id Post ID.
 * @return string The index text.
 */
function promptatlas_build_search_blob( $post_id ) {
	$post = get_post( $post_id );

	if ( ! $post ) {
		return '';
	}

	$parts = array(
		$post->post_excerpt,
		wp_strip_all_tags( (string) $post->post_content, true ),
	);

	if ( 'prompt' === $post->post_type ) {
		$parts[] = promptatlas_get_prompt_text( $post_id );

		// Placeholder descriptions are genuinely useful to search: someone
		// looking for "job description" should find a prompt whose variable is
		// described that way.
		foreach ( promptatlas_get_variables( $post_id ) as $variable ) {
			if ( ! empty( $variable['name'] ) ) {
				$parts[] = str_replace( '_', ' ', (string) $variable['name'] );
			}
			if ( ! empty( $variable['description'] ) ) {
				$parts[] = (string) $variable['description'];
			}
		}
	}

	// Term names, so "career" finds prompts in Career & Job Search and
	// "midjourney" finds everything tagged with that tool.
	foreach ( array( 'prompt_category', 'prompt_tool', 'prompt_tag' ) as $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue;
		}

		foreach ( $terms as $term ) {
			$parts[] = $term->name;
		}
	}

	$blob = implode( ' ', array_filter( array_map( 'trim', $parts ) ) );
	$blob = preg_replace( '/\s+/', ' ', $blob );

	return trim( (string) $blob );
}

/**
 * Rebuild the index for one post.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function promptatlas_rebuild_search_blob( $post_id ) {
	$post_id = absint( $post_id );

	if ( 0 === $post_id ) {
		return;
	}

	$post_type = get_post_type( $post_id );

	if ( ! in_array( $post_type, array( 'prompt', 'collection' ), true ) ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	$blob = promptatlas_build_search_blob( $post_id );

	if ( '' === $blob ) {
		delete_post_meta( $post_id, PROMPTATLAS_META_SEARCH_BLOB );
		return;
	}

	update_post_meta( $post_id, PROMPTATLAS_META_SEARCH_BLOB, $blob );
}

/**
 * Rebuild on save.
 *
 * Priority 20, after the Phase 05 meta box handlers at priority 10, so the
 * prompt text written in that same request is included rather than the previous
 * version.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function promptatlas_rebuild_blob_on_save( $post_id ) {
	promptatlas_rebuild_search_blob( $post_id );
}
add_action( 'save_post_prompt', 'promptatlas_rebuild_blob_on_save', 20 );
add_action( 'save_post_collection', 'promptatlas_rebuild_blob_on_save', 20 );

/**
 * Rebuild when taxonomy terms change.
 *
 * Assigning a category happens separately from saving the post content, so
 * without this the index would keep the old term names until the next edit.
 *
 * @param int    $object_id Post ID.
 * @param array  $terms     Terms. Unused.
 * @param array  $tt_ids    Term taxonomy IDs. Unused.
 * @param string $taxonomy  Taxonomy name.
 * @return void
 */
function promptatlas_rebuild_blob_on_terms( $object_id, $terms, $tt_ids, $taxonomy ) {
	unset( $terms, $tt_ids );

	if ( ! in_array( $taxonomy, array( 'prompt_category', 'prompt_tool', 'prompt_tag' ), true ) ) {
		return;
	}

	promptatlas_rebuild_search_blob( $object_id );
}
add_action( 'set_object_terms', 'promptatlas_rebuild_blob_on_terms', 10, 4 );

/**
 * Rebuild the index for every prompt and collection.
 *
 * Run on activation, so an existing site gets an index without anyone having to
 * re-save every post by hand.
 *
 * @return int Number of posts indexed.
 */
function promptatlas_rebuild_all_search_blobs() {
	$ids = get_posts(
		array(
			'post_type'              => array( 'prompt', 'collection' ),
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => true,
		)
	);

	foreach ( $ids as $id ) {
		promptatlas_rebuild_search_blob( (int) $id );
	}

	return count( $ids );
}
