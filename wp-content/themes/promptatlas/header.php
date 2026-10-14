<?php
/**
 * The document head and site header.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="promptatlas-no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php
	/*
	 * theme-color is set per scheme so the mobile browser chrome matches the
	 * page instead of flashing white above a dark page.
	 */
	?>
	<meta name="theme-color" content="#FCFAF6" media="(prefers-color-scheme: light)">
	<meta name="theme-color" content="#17150F" media="(prefers-color-scheme: dark)">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#primary">
	<?php esc_html_e( 'Skip to main content', 'promptatlas' ); ?>
</a>

<div class="site-wrap">

	<header class="site-header" role="banner">
		<div class="container site-header__inner">

			<div class="site-header__brand">
				<?php promptatlas_site_branding(); ?>
			</div>

			<?php
			/*
			 * The mobile menu is a checkbox-driven disclosure rather than a
			 * JavaScript one, so navigation works with scripting unavailable.
			 * The checkbox is visually hidden but focusable, and the <label>
			 * carries the accessible name.
			 */
			?>
			<input
				type="checkbox"
				id="promptatlas-menu-toggle"
				class="menu-toggle__checkbox"
				aria-hidden="true"
				tabindex="-1"
			>
			<label for="promptatlas-menu-toggle" class="menu-toggle">
				<span class="menu-toggle__bars" aria-hidden="true">
					<span></span><span></span><span></span>
				</span>
				<span class="menu-toggle__text"><?php esc_html_e( 'Menu', 'promptatlas' ); ?></span>
			</label>

			<nav class="site-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'promptatlas' ); ?>">
				<?php
				if ( has_nav_menu( 'primary' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => 'site-nav__list',
							'depth'          => 2,
							'fallback_cb'    => false,
						)
					);
				} else {
					/*
					 * No menu assigned yet. Rather than rendering nothing — which
					 * would leave a brand-new site with no navigation at all —
					 * fall back to the real archives that exist.
					 */
					?>
					<ul class="site-nav__list">
						<li>
							<a href="<?php echo esc_url( (string) get_post_type_archive_link( 'prompt' ) ); ?>">
								<?php esc_html_e( 'Prompts', 'promptatlas' ); ?>
							</a>
						</li>
						<li>
							<a href="<?php echo esc_url( (string) get_post_type_archive_link( 'collection' ) ); ?>">
								<?php esc_html_e( 'Collections', 'promptatlas' ); ?>
							</a>
						</li>
					</ul>
					<?php
				}
				?>
			</nav>

			<div class="site-header__actions">
				<?php get_search_form(); ?>
				<?php promptatlas_theme_toggle(); ?>
			</div>

		</div>
	</header>

	<?php
	/*
	 * Site-wide notice region for newsletter confirm and unsubscribe results.
	 *
	 * Those links land on the homepage, where the signup block is not rendered,
	 * so without this the visitor clicked "confirm", the database changed, and
	 * they saw an ordinary homepage with no indication that anything happened.
	 * The state is only ever set by those two actions, so this stays empty on
	 * every normal page view.
	 */
	$promptatlas_notice = function_exists( 'promptatlas_get_signup_state' )
		? promptatlas_get_signup_state()
		: null;

	if ( $promptatlas_notice && ( get_query_var( 'pa_confirm' ) || get_query_var( 'pa_unsubscribe' ) ) ) :
		?>
		<div class="container site-notice">
			<p
				class="notice-message notice-message--<?php echo esc_attr( 'success' === $promptatlas_notice['type'] ? 'success' : 'error' ); ?>"
				role="status"
			>
				<?php echo esc_html( $promptatlas_notice['message'] ); ?>
			</p>
		</div>
		<?php
	endif;
	?>
