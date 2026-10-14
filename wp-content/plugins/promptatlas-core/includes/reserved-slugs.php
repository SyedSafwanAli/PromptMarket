<?php
/**
 * Reserved slug guard.
 *
 * DECISIONS.md decision 13 freezes the URL architecture and lists the slugs that
 * would shadow a route if a post used them. For example, a prompt whose slug is
 * `category` would sit at /prompts/category/, colliding with the category
 * archive, and WordPress would resolve the conflict unpredictably.
 *
 * This hooks wp_unique_post_slug, which is the single point WordPress uses to
 * finalise every slug, so it covers the editor, Quick Edit, WP-CLI, REST and
 * programmatic inserts alike.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Slugs that may not be used by a page, prompt or collection.
 *
 * @return string[] Reserved slugs.
 */
function promptatlas_reserved_slugs() {
	return array(
		'prompts',
		'collections',
		'category',
		'tag',
		'tool',
		'page',
		'search',
		'feed',
		'wp-admin',
		'wp-content',
		'wp-includes',
		'about',
		'contact',
		'privacy',
		'privacy-policy',
		'terms',
		'dmca',
		'llms',
		'robots',
		'sitemap',
	);
}

/**
 * Append a suffix to a slug that would shadow a route.
 *
 * Only applies to the post types that live in the root or /prompts/ namespace.
 * Attachments and other types are left alone.
 *
 * Note: `about`, `contact`, `privacy` and similar are reserved against *posts*
 * precisely so the real Pages created in Phase 17 can own them. A page keeps
 * its own slug; a prompt or collection that tries to take one gets suffixed.
 *
 * @param string $slug          The slug WordPress settled on.
 * @param int    $post_id       Post ID.
 * @param string $post_status   Post status.
 * @param string $post_type     Post type.
 * @param int    $post_parent   Parent post ID.
 * @param string $original_slug The slug before WordPress made it unique.
 * @return string The slug to use.
 */
function promptatlas_guard_reserved_slugs( $slug, $post_id, $post_status, $post_type, $post_parent, $original_slug ) {
	unset( $post_id, $post_status, $post_parent, $original_slug );

	if ( ! in_array( $post_type, array( 'prompt', 'collection' ), true ) ) {
		return $slug;
	}

	if ( ! in_array( $slug, promptatlas_reserved_slugs(), true ) ) {
		return $slug;
	}

	return $slug . '-' . $post_type;
}
add_filter( 'wp_unique_post_slug', 'promptatlas_guard_reserved_slugs', 10, 6 );

/**
 * Second layer: catch reserved slugs on drafts too.
 *
 * `wp_unique_post_slug()` returns early for draft, pending and auto-draft posts
 * *before* it applies its filter, so the guard above never runs for a draft.
 * In practice the slug would be corrected the moment the post was published,
 * because publishing recomputes it — but the editor would see `category` in the
 * permalink box the whole time it was a draft, which is misleading, and any code
 * reading the slug before publication would read the colliding value.
 *
 * `wp_insert_post_data` runs for every status, so this closes that gap.
 *
 * @param array<string, mixed> $data    Sanitised post data about to be written.
 * @param array<string, mixed> $postarr Raw post data as supplied.
 * @return array<string, mixed> Post data, with a reserved slug suffixed.
 */
function promptatlas_guard_reserved_slugs_on_insert( $data, $postarr ) {
	unset( $postarr );

	if ( ! isset( $data['post_type'], $data['post_name'] ) ) {
		return $data;
	}

	if ( ! in_array( $data['post_type'], array( 'prompt', 'collection' ), true ) ) {
		return $data;
	}

	if ( '' === $data['post_name'] ) {
		return $data;
	}

	if ( ! in_array( $data['post_name'], promptatlas_reserved_slugs(), true ) ) {
		return $data;
	}

	$data['post_name'] = $data['post_name'] . '-' . $data['post_type'];

	return $data;
}
add_filter( 'wp_insert_post_data', 'promptatlas_guard_reserved_slugs_on_insert', 10, 2 );
