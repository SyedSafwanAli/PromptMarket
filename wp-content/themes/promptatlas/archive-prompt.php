<?php
/**
 * The prompt archive at /prompts/.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

get_header();

$promptatlas_total = isset( $GLOBALS['wp_query']->found_posts ) ? (int) $GLOBALS['wp_query']->found_posts : 0;
?>

<main id="primary" class="site-main container" role="main">

	<?php promptatlas_breadcrumbs(); ?>

	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'All prompts', 'promptatlas' ); ?></h1>
		<p class="page-header__description">
			<?php esc_html_e( 'Every prompt in the library, newest first. Each one is written by hand and explained, so you know what to change and why it works.', 'promptatlas' ); ?>
		</p>
		<?php if ( $promptatlas_total > 0 ) : ?>
			<p class="page-header__meta">
				<?php
				printf(
					/* translators: %s: number of published prompts. */
					esc_html( _n( '%s prompt published', '%s prompts published', $promptatlas_total, 'promptatlas' ) ),
					esc_html( number_format_i18n( $promptatlas_total ) )
				);
				?>
			</p>
		<?php endif; ?>
		<div class="page-header__search">
			<?php get_search_form(); ?>
		</div>
	</header>

	<?php if ( have_posts() ) : ?>

		<div class="card-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card', 'prompt' );
			endwhile;
			?>
		</div>

		<?php promptatlas_pagination(); ?>

	<?php else : ?>

		<div class="empty-state">
			<h2 class="empty-state__heading"><?php esc_html_e( 'No prompts published yet', 'promptatlas' ); ?></h2>
			<p class="empty-state__message">
				<?php esc_html_e( 'The library is being written. Published prompts will appear here as they go live.', 'promptatlas' ); ?>
			</p>
			<?php if ( current_user_can( 'publish_posts' ) ) : ?>
				<p class="empty-state__message">
					<?php esc_html_e( 'You are signed in as an editor — add the first prompt from the dashboard.', 'promptatlas' ); ?>
				</p>
				<p>
					<a class="button button--primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=prompt' ) ); ?>">
						<?php esc_html_e( 'Add a prompt', 'promptatlas' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>

	<?php endif; ?>

	<?php get_template_part( 'template-parts/newsletter' ); ?>

</main>

<?php
get_footer();
