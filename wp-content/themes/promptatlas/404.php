<?php
/**
 * Template for 404 Not Found.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main container" role="main">

	<div class="empty-state empty-state--404">
		<p class="empty-state__code" aria-hidden="true">404</p>
		<h1 class="empty-state__heading"><?php esc_html_e( 'That page does not exist', 'promptatlas' ); ?></h1>
		<p class="empty-state__message">
			<?php esc_html_e( 'The page you asked for could not be found. It may have moved, or the link may be wrong.', 'promptatlas' ); ?>
		</p>

		<div class="empty-state__search">
			<?php get_search_form(); ?>
		</div>

		<p class="empty-state__actions">
			<a class="button button--primary" href="<?php echo esc_url( (string) get_post_type_archive_link( 'prompt' ) ); ?>">
				<?php esc_html_e( 'Browse all prompts', 'promptatlas' ); ?>
			</a>
			<a class="button button--secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Go to the homepage', 'promptatlas' ); ?>
			</a>
		</p>
	</div>

</main>

<?php
get_footer();
