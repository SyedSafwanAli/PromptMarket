<?php
/**
 * Asset enqueuing.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get a cache-busting version string for a theme asset.
 *
 * Uses the file's modification time locally so an edit is picked up without
 * bumping the theme version by hand. In production it returns the theme version
 * instead, because filemtime() is a filesystem stat on every request for every
 * asset — cheap individually, wasteful at traffic, and pointless when the files
 * only change on deploy.
 *
 * @param string $relative_path Path relative to the theme root.
 * @return string Version string for wp_enqueue_*.
 */
function promptatlas_asset_version( $relative_path ) {
	if ( ! wp_get_environment_type() || 'production' === wp_get_environment_type() ) {
		return PROMPTATLAS_THEME_VERSION;
	}

	$absolute = PROMPTATLAS_THEME_DIR . ltrim( $relative_path, '/' );

	if ( ! file_exists( $absolute ) ) {
		return PROMPTATLAS_THEME_VERSION;
	}

	return (string) filemtime( $absolute );
}

/**
 * Enqueue frontend styles and scripts.
 *
 * Load order matters: tokens must be parsed before anything that consumes them.
 *
 * Nothing is enqueued that the theme does not use, and the only JavaScript is
 * the theme toggle — small, deferred, and entirely optional to the page working.
 *
 * @return void
 */
function promptatlas_enqueue_assets() {

	wp_enqueue_style(
		'promptatlas-tokens',
		PROMPTATLAS_THEME_URI . 'assets/css/tokens.css',
		array(),
		promptatlas_asset_version( 'assets/css/tokens.css' )
	);

	wp_enqueue_style(
		'promptatlas-theme',
		PROMPTATLAS_THEME_URI . 'assets/css/theme.css',
		array( 'promptatlas-tokens' ),
		promptatlas_asset_version( 'assets/css/theme.css' )
	);

	wp_enqueue_style(
		'promptatlas-components',
		PROMPTATLAS_THEME_URI . 'assets/css/components.css',
		array( 'promptatlas-theme' ),
		promptatlas_asset_version( 'assets/css/components.css' )
	);

	// style.css carries only the theme header, so it is never enqueued.

	wp_enqueue_script(
		'promptatlas-theme-toggle',
		PROMPTATLAS_THEME_URI . 'assets/js/theme-toggle.js',
		array(),
		promptatlas_asset_version( 'assets/js/theme-toggle.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	/*
	 * The copy script loads only where a prompt block exists. Shipping it on the
	 * homepage and every archive would be bytes parsed for a control that is not
	 * on the page — exactly the "do not enqueue an asset the page does not use"
	 * rule from Phase 06.
	 */
	if ( is_singular( 'prompt' ) ) {
		wp_enqueue_script(
			'promptatlas-copy',
			PROMPTATLAS_THEME_URI . 'assets/js/copy.js',
			array(),
			promptatlas_asset_version( 'assets/js/copy.js' ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'promptatlas_enqueue_assets' );

/**
 * Print the critical inline head script.
 *
 * This is the one piece of blocking inline JavaScript in the theme, and it earns
 * its place for two reasons:
 *
 * 1. It applies the saved theme BEFORE first paint. Deferring it would render
 *    the light theme first and then repaint dark — a visible flash on every
 *    page load for every dark-mode visitor.
 * 2. It removes the `promptatlas-no-js` class, so CSS can hide
 *    JavaScript-dependent controls when scripting is unavailable rather than
 *    showing a dead button.
 *
 * Every localStorage access is wrapped: it throws in some private-browsing
 * modes and whenever site data is blocked, and the page must still render.
 *
 * @return void
 */
function promptatlas_print_head_script() {
	?>
	<script>
	(function () {
		var root = document.documentElement;
		root.classList.remove( 'promptatlas-no-js' );
		document.addEventListener( 'DOMContentLoaded', function () {
			document.body.classList.remove( 'promptatlas-no-js' );
		} );
		try {
			var saved = window.localStorage.getItem( 'promptatlas-theme' );
			if ( saved === 'dark' || saved === 'light' ) {
				root.setAttribute( 'data-theme', saved );
			}
		} catch ( e ) {
			/* Blocked storage. The OS preference via prefers-color-scheme still applies. */
		}
	})();
	</script>
	<?php
}
add_action( 'wp_head', 'promptatlas_print_head_script', 1 );

/**
 * Remove the WordPress emoji scripts.
 *
 * Two HTTP requests and a DOM scan on every page load to convert emoji that the
 * site does not use, on operating systems that have rendered emoji natively for
 * a decade. Removed here rather than in Phase 15 because it costs nothing to do
 * now and there is no argument for keeping it.
 *
 * @return void
 */
function promptatlas_remove_emoji_assets() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'promptatlas_remove_emoji_assets' );

/**
 * Dequeue the block library stylesheet on the frontend.
 *
 * The theme styles its own content presentation and uses no core blocks that
 * depend on this sheet. It is a non-trivial render-blocking stylesheet, so
 * loading it unused directly contradicts the Phase 06 rule about not enqueuing
 * assets the theme does not use.
 *
 * If a later phase starts using core blocks that need it — galleries, columns,
 * the cover block — this must be reverted and the cost re-measured in Phase 15.
 *
 * @return void
 */
function promptatlas_dequeue_block_styles() {
	if ( is_admin() ) {
		return;
	}

	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'promptatlas_dequeue_block_styles', 100 );
