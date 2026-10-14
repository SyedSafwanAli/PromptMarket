<?php
/**
 * Post type registration.
 *
 * Slugs come from DECISIONS.md decision 13 (frozen URL architecture) and must
 * not be changed without a redirect plan.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the prompt and collection post types.
 *
 * Argument rationale, recorded because Phase 05 requires each to be deliberate:
 *
 * - `public: true`         — these are the site's content; they must be
 *                            queryable, have permalinks, and appear in search.
 * - `has_archive`          — `prompts` and `collections` give the
 *                            /prompts/ and /collections/ archives that
 *                            CONTENT_STRATEGY.md 3.6 freezes.
 * - `rewrite.with_front`   — false. The site's permalink base is `/%postname%/`
 *                            with no front segment; leaving this true would
 *                            prefix the archive if the base ever gains one.
 * - `show_in_rest: true`   — enables the block editor, and exposes published
 *                            content that is already public on the frontend.
 *                            No private data is exposed: every custom meta
 *                            field is registered with `show_in_rest => false`
 *                            (see includes/meta.php for why).
 * - `supports`             — title, editor, excerpt, thumbnail, revisions and
 *                            author are all native WordPress features that the
 *                            content model in CONTENT_STRATEGY.md 4.1 maps onto
 *                            directly. Nothing here is reimplemented.
 * - no `comments` support  — DECISIONS.md decision 6: comments are off
 *                            everywhere. Omitting support means WordPress never
 *                            renders, queries or exposes a comment form.
 * - `capability_type: post`— prompts are editorial content with exactly the
 *                            same access rules as posts, so the standard
 *                            capabilities apply. Custom capabilities would have
 *                            to be granted to roles on activation, and getting
 *                            that wrong locks the owner out of their own
 *                            content. Phase 14 owns roles and may revisit this;
 *                            doing it now would be speculative.
 * - `menu_position`        — 5 and 6, directly below Posts, because this is the
 *                            primary content the owner works with daily.
 *
 * @return void
 */
function promptatlas_register_post_types() {

	$prompt_labels = array(
		'name'                  => _x( 'Prompts', 'Post type general name', 'promptatlas' ),
		'singular_name'         => _x( 'Prompt', 'Post type singular name', 'promptatlas' ),
		'menu_name'             => _x( 'Prompts', 'Admin Menu text', 'promptatlas' ),
		'name_admin_bar'        => _x( 'Prompt', 'Add New on Toolbar', 'promptatlas' ),
		'add_new'               => __( 'Add New', 'promptatlas' ),
		'add_new_item'          => __( 'Add New Prompt', 'promptatlas' ),
		'new_item'              => __( 'New Prompt', 'promptatlas' ),
		'edit_item'             => __( 'Edit Prompt', 'promptatlas' ),
		'view_item'             => __( 'View Prompt', 'promptatlas' ),
		'view_items'            => __( 'View Prompts', 'promptatlas' ),
		'all_items'             => __( 'All Prompts', 'promptatlas' ),
		'search_items'          => __( 'Search Prompts', 'promptatlas' ),
		'not_found'             => __( 'No prompts found.', 'promptatlas' ),
		'not_found_in_trash'    => __( 'No prompts found in Trash.', 'promptatlas' ),
		'featured_image'        => __( 'Prompt image', 'promptatlas' ),
		'set_featured_image'    => __( 'Set prompt image', 'promptatlas' ),
		'remove_featured_image' => __( 'Remove prompt image', 'promptatlas' ),
		'use_featured_image'    => __( 'Use as prompt image', 'promptatlas' ),
		'archives'              => __( 'Prompt archives', 'promptatlas' ),
		'item_published'        => __( 'Prompt published.', 'promptatlas' ),
		'item_updated'          => __( 'Prompt updated.', 'promptatlas' ),
	);

	register_post_type(
		'prompt',
		array(
			'labels'             => $prompt_labels,
			'description'        => __( 'A single AI prompt, its explanation, and its metadata.', 'promptatlas' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_admin_bar'  => true,
			'show_in_rest'       => true,
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-editor-code',
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author' ),
			'taxonomies'         => array( 'prompt_category', 'prompt_tool', 'prompt_tag' ),
			'has_archive'        => 'prompts',
			'rewrite'            => array(
				'slug'       => 'prompts',
				'with_front' => false,
				'feeds'      => true,
				'pages'      => true,
			),
			'query_var'          => true,
			'delete_with_user'   => false,
		)
	);

	$collection_labels = array(
		'name'                  => _x( 'Collections', 'Post type general name', 'promptatlas' ),
		'singular_name'         => _x( 'Collection', 'Post type singular name', 'promptatlas' ),
		'menu_name'             => _x( 'Collections', 'Admin Menu text', 'promptatlas' ),
		'name_admin_bar'        => _x( 'Collection', 'Add New on Toolbar', 'promptatlas' ),
		'add_new'               => __( 'Add New', 'promptatlas' ),
		'add_new_item'          => __( 'Add New Collection', 'promptatlas' ),
		'new_item'              => __( 'New Collection', 'promptatlas' ),
		'edit_item'             => __( 'Edit Collection', 'promptatlas' ),
		'view_item'             => __( 'View Collection', 'promptatlas' ),
		'view_items'            => __( 'View Collections', 'promptatlas' ),
		'all_items'             => __( 'All Collections', 'promptatlas' ),
		'search_items'          => __( 'Search Collections', 'promptatlas' ),
		'not_found'             => __( 'No collections found.', 'promptatlas' ),
		'not_found_in_trash'    => __( 'No collections found in Trash.', 'promptatlas' ),
		'featured_image'        => __( 'Collection image', 'promptatlas' ),
		'set_featured_image'    => __( 'Set collection image', 'promptatlas' ),
		'remove_featured_image' => __( 'Remove collection image', 'promptatlas' ),
		'use_featured_image'    => __( 'Use as collection image', 'promptatlas' ),
		'archives'              => __( 'Collection archives', 'promptatlas' ),
		'item_published'        => __( 'Collection published.', 'promptatlas' ),
		'item_updated'          => __( 'Collection updated.', 'promptatlas' ),
	);

	register_post_type(
		'collection',
		array(
			'labels'             => $collection_labels,
			'description'        => __( 'A curated, editorially ordered list of prompts with original framing.', 'promptatlas' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_admin_bar'  => true,
			'show_in_rest'       => true,
			'menu_position'      => 6,
			'menu_icon'          => 'dashicons-list-view',
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author' ),
			'taxonomies'         => array( 'prompt_tool' ),
			'has_archive'        => 'collections',
			'rewrite'            => array(
				'slug'       => 'collections',
				'with_front' => false,
				'feeds'      => true,
				'pages'      => true,
			),
			'query_var'          => true,
			'delete_with_user'   => false,
		)
	);
}
add_action( 'init', 'promptatlas_register_post_types', 6 );
