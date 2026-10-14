<?php
/**
 * Template for search results.
 *
 * Phase 09 owns search behaviour, filters and the Roman Urdu synonym handling.
 * This renders the default WordPress search honestly in the meantime.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

get_header();

$promptatlas_found = isset( $GLOBALS['wp_query']->found_posts ) ? (int) $GLOBALS['wp_query']->found_posts : 0;
?>

<main id="primary" class="site-main container" role="main">

	<header class="page-header">
		<h1 class="page-title">
			<?php
			printf(
				/* translators: %s: the search query. */
				esc_html__( 'Results for %s', 'promptatlas' ),
				'<span class="search-query">' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>
		<p class="page-header__meta">
			<?php
			printf(
				/* translators: %s: number of results found. */
				esc_html( _n( '%s result', '%s results', $promptatlas_found, 'promptatlas' ) ),
				esc_html( number_format_i18n( $promptatlas_found ) )
			);
			?>
		</p>
		<div class="page-header__search">
			<?php get_search_form(); ?>
		</div>
	</header>

	<?php if ( have_posts() ) : ?>

		<div class="card-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card', get_post_type() );
			endwhile;
			?>
		</div>

		<?php promptatlas_pagination(); ?>

	<?php else : ?>

		<div class="empty-state">
			<h2 class="empty-state__heading"><?php esc_html_e( 'No results', 'promptatlas' ); ?></h2>
			<p class="empty-state__message">
				<?php esc_html_e( 'Nothing matched that search. Try a shorter phrase, or browse the categories instead.', 'promptatlas' ); ?>
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
