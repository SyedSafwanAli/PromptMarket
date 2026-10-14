<?php
/**
 * The collection index at /collections/.
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
		<h1 class="page-title"><?php esc_html_e( 'Collections', 'promptatlas' ); ?></h1>
		<p class="page-header__description">
			<?php esc_html_e( 'Hand-picked sets of prompts for a particular job, in the order we would use them. Each one explains why the prompts belong together and what to do with them.', 'promptatlas' ); ?>
		</p>
		<?php if ( $promptatlas_total > 0 ) : ?>
			<p class="page-header__meta">
				<?php
				printf(
					/* translators: %s: number of published collections. */
					esc_html( _n( '%s collection', '%s collections', $promptatlas_total, 'promptatlas' ) ),
					esc_html( number_format_i18n( $promptatlas_total ) )
				);
				?>
			</p>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>

		<div class="card-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card', 'collection' );
			endwhile;
			?>
		</div>

		<?php promptatlas_pagination(); ?>

	<?php else : ?>

		<div class="empty-state">
			<h2 class="empty-state__heading"><?php esc_html_e( 'No collections yet', 'promptatlas' ); ?></h2>
			<p class="empty-state__message">
				<?php esc_html_e( 'Collections are curated sets of prompts. None have been published so far.', 'promptatlas' ); ?>
			</p>
			<p>
				<a class="button button--secondary" href="<?php echo esc_url( (string) get_post_type_archive_link( 'prompt' ) ); ?>">
					<?php esc_html_e( 'Browse all prompts', 'promptatlas' ); ?>
				</a>
			</p>
		</div>

	<?php endif; ?>

</main>

<?php
get_footer();
