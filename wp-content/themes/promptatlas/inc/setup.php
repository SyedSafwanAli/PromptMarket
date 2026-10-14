<?php
/**
 * Theme setup: supports, menus, image sizes.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports and navigation menus.
 *
 * Notable omissions, all deliberate:
 *
 * - No `comments` anywhere. DECISIONS.md decision 6 turns comments off site
 *   wide, so the theme never declares support, never registers a comment
 *   template, and never enqueues comment-reply.js.
 * - No `custom-background` or `custom-header`. The design is art-directed
 *   (Phase 07); letting it be overridden from the Customiser would undermine
 *   the token system and the dark-mode contrast guarantees.
 * - No webfonts. System fonts only at launch, per the performance rules in
 *   CLAUDE.md section 6.
 *
 * @return void
 */
function promptatlas_theme_setup() {

	// Let WordPress manage the <title> element.
	add_theme_support( 'title-tag' );

	// Semantic HTML5 markup instead of WordPress's legacy div soup.
	add_theme_support(
		'html5',
		array(
			'search-form',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		)
	);

	add_theme_support( 'post-thumbnails' );
	add_post_type_support( 'prompt', 'thumbnail' );
	add_post_type_support( 'collection', 'thumbnail' );

	// An SVG wordmark is provided by the theme (Phase 07), but the owner may
	// replace it without touching code.
	add_theme_support(
		'custom-logo',
		array(
			'height'               => 48,
			'width'                => 220,
			'flex-height'          => true,
			'flex-width'           => true,
			'unlink-homepage-logo' => false,
		)
	);

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );

	// Translation-ready, per DECISIONS.md decision 3. No .po/.mo files ship
	// yet; the loader is here so adding one later needs no code change.
	load_theme_textdomain( 'promptatlas', PROMPTATLAS_THEME_DIR . 'languages' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary navigation', 'promptatlas' ),
			'footer'  => __( 'Footer navigation', 'promptatlas' ),
		)
	);

	/*
	 * Image sizes.
	 *
	 * Explicit, hard-cropped sizes so that every image in a listing has known
	 * dimensions and cannot shift layout. The CLS budget is 0.1.
	 */
	add_image_size( 'promptatlas-card', 640, 360, true );
	add_image_size( 'promptatlas-hero', 1200, 630, true );
}
add_action( 'after_setup_theme', 'promptatlas_theme_setup' );

/**
 * Set the content width used by oEmbeds and wide alignments.
 *
 * @return void
 */
function promptatlas_content_width() {
	$GLOBALS['content_width'] = 720;
}
add_action( 'after_setup_theme', 'promptatlas_content_width', 0 );

/**
 * Add useful classes to the body element.
 *
 * `promptatlas-no-js` is removed by the head script the moment JavaScript runs,
 * which lets CSS style the no-JavaScript case honestly rather than hiding
 * controls that would never work.
 *
 * @param string[] $classes Existing body classes.
 * @return string[] Filtered classes.
 */
function promptatlas_body_classes( $classes ) {
	$classes[] = 'promptatlas-no-js';

	if ( is_singular( 'prompt' ) ) {
		$classes[] = 'is-prompt';
	}

	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'has-no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'promptatlas_body_classes' );

/**
 * Make the excerpt length suit the card layout.
 *
 * Prompts and collections are required to have a hand-written excerpt of
 * 120-160 characters (CONTENT_STRATEGY.md 4.3), so this only affects content
 * types that fall back to an auto-generated excerpt.
 *
 * @param int $length Default length in words.
 * @return int Filtered length.
 */
function promptatlas_excerpt_length( $length ) {
	unset( $length );

	return 28;
}
add_filter( 'excerpt_length', 'promptatlas_excerpt_length' );

/**
 * Replace the excerpt ellipsis with a typographic one.
 *
 * @param string $more Default "more" string.
 * @return string Filtered string.
 */
function promptatlas_excerpt_more( $more ) {
	unset( $more );

	return '&hellip;';
}
add_filter( 'excerpt_more', 'promptatlas_excerpt_more' );
