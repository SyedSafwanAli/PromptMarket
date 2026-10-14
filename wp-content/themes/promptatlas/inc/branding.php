<?php
/**
 * Brand assets: icons and the inline mark.
 *
 * Asset files live in assets/img/ and are documented in DESIGN_SYSTEM.md.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get the URL of the default Open Graph share image.
 *
 * Phase 12 uses this as the fallback when a prompt or collection has no
 * featured image. Exposed as a function so Phase 12 never hardcodes the path.
 *
 * @return string Absolute URL to the default share image.
 */
function promptatlas_default_share_image() {
	return PROMPTATLAS_THEME_URI . 'assets/img/og-default.png';
}

/**
 * Output icon link tags.
 *
 * Skipped entirely when the owner has set a WordPress Site Icon, because core
 * then prints its own and two competing sets would be worse than either. The
 * theme's icons are a sensible default, not an override of the owner's choice.
 *
 * The SVG icon is listed first: browsers that support it get a mark that stays
 * sharp at any size and in any theme, and the PNG remains for those that do not.
 *
 * @return void
 */
function promptatlas_icon_links() {
	if ( has_site_icon() ) {
		return;
	}

	$img = PROMPTATLAS_THEME_URI . 'assets/img/';
	?>
	<link rel="icon" href="<?php echo esc_url( $img . 'favicon.svg' ); ?>" type="image/svg+xml">
	<link rel="icon" href="<?php echo esc_url( $img . 'favicon-32.png' ); ?>" sizes="32x32" type="image/png">
	<link rel="icon" href="<?php echo esc_url( $img . 'icon-192.png' ); ?>" sizes="192x192" type="image/png">
	<link rel="apple-touch-icon" href="<?php echo esc_url( $img . 'apple-touch-icon.png' ); ?>" sizes="180x180">
	<?php
}
add_action( 'wp_head', 'promptatlas_icon_links', 2 );

/**
 * Output the PromptAtlas mark as inline SVG.
 *
 * Inline rather than an <img> for two reasons: it costs no extra request, and
 * it inherits currentColor so the mark follows the theme instead of needing a
 * second dark-mode file.
 *
 * Marked aria-hidden because it sits next to the site name in the header — a
 * screen reader announcing "PromptAtlas PromptAtlas" is noise, not information.
 *
 * @param int $size Rendered size in pixels.
 * @return void
 */
function promptatlas_mark( $size = 28 ) {
	$size = absint( $size );
	?>
	<svg
		class="site-mark"
		width="<?php echo esc_attr( (string) $size ); ?>"
		height="<?php echo esc_attr( (string) $size ); ?>"
		viewBox="0 0 32 32"
		aria-hidden="true"
		focusable="false"
	>
		<path d="M12.5 4.5H7v23h5.5" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="square"/>
		<path d="M19.5 4.5H25v23h-5.5" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="square"/>
		<rect class="site-mark__slot" x="13.6" y="13.6" width="4.8" height="4.8" rx="1" fill="currentColor"/>
	</svg>
	<?php
}
