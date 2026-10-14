<?php
/**
 * Taxonomy registration.
 *
 * Shapes and slugs come from DECISIONS.md decision 12 and the frozen URL table
 * in CONTENT_STRATEGY.md 3.6. None of this is a Phase 05 choice.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the prompt category, tool and tag taxonomies.
 *
 * All three are flat. The reasoning for `prompt_category` being flat rather
 * than hierarchical is in DECISIONS.md decision 12: at 40-60 prompts a
 * two-level hierarchy produces leaf categories with two prompts each, and a
 * two-prompt archive is a thin page. A flat taxonomy can become hierarchical
 * later without breaking term URLs; the reverse is not true.
 *
 * `show_in_rest` is true for all three: they classify public content, they are
 * needed by the block editor's taxonomy panels, and they expose nothing that is
 * not already on the frontend.
 *
 * Note on rewrite slugs: a slug containing a slash ("prompts/category") is
 * valid and produces the nested archive URLs frozen in Phase 04. Collisions
 * with prompt slugs are prevented by the reserved-slug guard in
 * includes/reserved-slugs.php.
 *
 * @return void
 */
function promptatlas_register_taxonomies() {

	// 1. Category - required, exactly one per prompt, indexable archive.
	register_taxonomy(
		'prompt_category',
		array( 'prompt' ),
		array(
			'labels'             => array(
				'name'              => _x( 'Prompt Categories', 'taxonomy general name', 'promptatlas' ),
				'singular_name'     => _x( 'Prompt Category', 'taxonomy singular name', 'promptatlas' ),
				'menu_name'         => __( 'Categories', 'promptatlas' ),
				'all_items'         => __( 'All Categories', 'promptatlas' ),
				'edit_item'         => __( 'Edit Category', 'promptatlas' ),
				'view_item'         => __( 'View Category', 'promptatlas' ),
				'update_item'       => __( 'Update Category', 'promptatlas' ),
				'add_new_item'      => __( 'Add New Category', 'promptatlas' ),
				'new_item_name'     => __( 'New Category Name', 'promptatlas' ),
				'search_items'      => __( 'Search Categories', 'promptatlas' ),
				'not_found'         => __( 'No categories found.', 'promptatlas' ),
				'back_to_items'     => __( 'Back to Categories', 'promptatlas' ),
				'no_terms'          => __( 'No categories', 'promptatlas' ),
				'items_list'        => __( 'Categories list', 'promptatlas' ),
				/* translators: Hidden accessibility text. */
				'parent_item'       => __( 'Parent Category', 'promptatlas' ),
				'parent_item_colon' => __( 'Parent Category:', 'promptatlas' ),
			),
			'description'        => __( 'The single primary category a prompt belongs to, chosen by the job the reader is doing.', 'promptatlas' ),
			'public'             => true,
			'publicly_queryable' => true,
			'hierarchical'       => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
			'show_tagcloud'      => false,
			'rewrite'            => array(
				'slug'         => 'prompts/category',
				'with_front'   => false,
				'hierarchical' => false,
			),
			'query_var'          => true,
		)
	);

	// 2. Tool - the AI tool or product family a prompt is written for.
	// Justified in DECISIONS.md decision 12: "midjourney prompts" is a real,
	// distinct query shape. Attached to collections as well, so a tool-led
	// collection can carry the same term.
	register_taxonomy(
		'prompt_tool',
		array( 'prompt', 'collection' ),
		array(
			'labels'             => array(
				'name'          => _x( 'AI Tools', 'taxonomy general name', 'promptatlas' ),
				'singular_name' => _x( 'AI Tool', 'taxonomy singular name', 'promptatlas' ),
				'menu_name'     => __( 'AI Tools', 'promptatlas' ),
				'all_items'     => __( 'All AI Tools', 'promptatlas' ),
				'edit_item'     => __( 'Edit AI Tool', 'promptatlas' ),
				'view_item'     => __( 'View AI Tool', 'promptatlas' ),
				'update_item'   => __( 'Update AI Tool', 'promptatlas' ),
				'add_new_item'  => __( 'Add New AI Tool', 'promptatlas' ),
				'new_item_name' => __( 'New AI Tool Name', 'promptatlas' ),
				'search_items'  => __( 'Search AI Tools', 'promptatlas' ),
				'not_found'     => __( 'No AI tools found.', 'promptatlas' ),
				'back_to_items' => __( 'Back to AI Tools', 'promptatlas' ),
				'no_terms'      => __( 'No AI tools', 'promptatlas' ),
				'items_list'    => __( 'AI Tools list', 'promptatlas' ),
			),
			'description'        => __( 'The AI tool or product family a prompt is written for. Name the product, never a version number.', 'promptatlas' ),
			'public'             => true,
			'publicly_queryable' => true,
			'hierarchical'       => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
			'show_tagcloud'      => false,
			'rewrite'            => array(
				'slug'       => 'prompts/tool',
				'with_front' => false,
			),
			'query_var'          => true,
		)
	);

	// 3. Tag - the specific artefact or scenario, cutting across categories.
	// Archives are noindex (Phase 12 implements the directive); they exist to
	// pass internal link equity and help a human browse, not to rank.
	register_taxonomy(
		'prompt_tag',
		array( 'prompt' ),
		array(
			'labels'             => array(
				'name'                       => _x( 'Prompt Tags', 'taxonomy general name', 'promptatlas' ),
				'singular_name'              => _x( 'Prompt Tag', 'taxonomy singular name', 'promptatlas' ),
				'menu_name'                  => __( 'Tags', 'promptatlas' ),
				'all_items'                  => __( 'All Tags', 'promptatlas' ),
				'edit_item'                  => __( 'Edit Tag', 'promptatlas' ),
				'view_item'                  => __( 'View Tag', 'promptatlas' ),
				'update_item'                => __( 'Update Tag', 'promptatlas' ),
				'add_new_item'               => __( 'Add New Tag', 'promptatlas' ),
				'new_item_name'              => __( 'New Tag Name', 'promptatlas' ),
				'search_items'               => __( 'Search Tags', 'promptatlas' ),
				'popular_items'              => __( 'Popular Tags', 'promptatlas' ),
				'separate_items_with_commas' => __( 'Separate tags with commas', 'promptatlas' ),
				'add_or_remove_items'        => __( 'Add or remove tags', 'promptatlas' ),
				'choose_from_most_used'      => __( 'Choose from the most used tags', 'promptatlas' ),
				'not_found'                  => __( 'No tags found.', 'promptatlas' ),
				'back_to_items'              => __( 'Back to Tags', 'promptatlas' ),
				'no_terms'                   => __( 'No tags', 'promptatlas' ),
				'items_list'                 => __( 'Tags list', 'promptatlas' ),
			),
			'description'        => __( 'The specific artefact or scenario a prompt produces, such as cold-email or unit-tests. Maximum five per prompt.', 'promptatlas' ),
			'public'             => true,
			'publicly_queryable' => true,
			'hierarchical'       => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
			'show_tagcloud'      => true,
			'rewrite'            => array(
				'slug'       => 'prompts/tag',
				'with_front' => false,
			),
			'query_var'          => true,
		)
	);
}

/*
 * Priority 5 - BEFORE the post types (priority 6). This ordering is load-bearing,
 * not cosmetic.
 *
 * WordPress builds its rewrite rules in the order permastructs were registered.
 * The `prompt` post type generates an attachment catch-all rule,
 * `prompts/[^/]+/([^/]+)/?$`, which matches ANY two segments under /prompts/ -
 * including /prompts/category/career-job-search/. With the post type registered
 * first, that rule sorted above the taxonomy rules and every category, tool and
 * tag archive returned 404 while the correct rules sat unreachable below it.
 *
 * Registering the taxonomies first puts their specific rules above the post
 * type's greedy one. Verified: all three archives return 200.
 *
 * If a later phase changes these priorities, re-test the taxonomy archives.
 */
add_action( 'init', 'promptatlas_register_taxonomies', 5 );
