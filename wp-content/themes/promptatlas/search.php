<?php
/**
 * Search results.
 *
 * Three distinct states, because they need different things from the visitor:
 *   1. No query typed    - invite a search, offer browsing.
 *   2. Results           - show them, with a snippet proving why each matched.
 *   3. Query, no results - say so plainly and offer a real next step.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

get_header();

$promptatlas_query_text = get_search_query();
$promptatlas_has_query  = '' !== trim( $promptatlas_query_text );
$promptatlas_found      = isset( $GLOBALS['wp_query']->found_posts ) ? (int) $GLOBALS['wp_query']->found_posts : 0;
?>

<main id="primary" class="site-main container" role="main">

	<header class="page-header">
		<?php if ( $promptatlas_has_query ) : ?>
			<h1 class="page-title">
				<?php
				printf(
					/* translators: %s: the search query. */
					esc_html__( 'Results for %s', 'promptatlas' ),
					'<span class="search-query">' . esc_html( $promptatlas_query_text ) . '</span>'
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
		<?php else : ?>
			<h1 class="page-title"><?php esc_html_e( 'Search', 'promptatlas' ); ?></h1>
		<?php endif; ?>

		<div class="page-header__search">
			<?php get_search_form(); ?>
		</div>

		<?php
		/*
		 * The scope is stated rather than left implicit. Phase 09 requires the
		 * decision about what search covers to be visible to the visitor, and a
		 * person who searches "privacy" and gets nothing deserves to know that
		 * pages are not searched rather than conclude the site is broken.
		 */
		?>
		<p class="search-scope">
			<?php esc_html_e( 'Searches prompt titles, summaries, explanations and the prompt text itself, plus collections. Site pages such as About and Privacy are not included.', 'promptatlas' ); ?>
		</p>
	</header>

	<?php if ( ! $promptatlas_has_query ) : ?>

		<div class="empty-state">
			<h2 class="empty-state__heading"><?php esc_html_e( 'What are you trying to do?', 'promptatlas' ); ?></h2>
			<p class="empty-state__message">
				<?php esc_html_e( 'Type what you want the AI to produce — "rewrite my resume", "explain this code", "write a cold email". You can search in Roman Urdu too.', 'promptatlas' ); ?>
			</p>
			<p class="empty-state__actions">
				<a class="button button--primary" href="<?php echo esc_url( (string) get_post_type_archive_link( 'prompt' ) ); ?>">
					<?php esc_html_e( 'Browse all prompts', 'promptatlas' ); ?>
				</a>
			</p>
		</div>

	<?php elseif ( have_posts() ) : ?>

		<ol class="search-results">
			<?php
			while ( have_posts() ) :
				the_post();

				$promptatlas_result_id = (int) get_the_ID();
				$promptatlas_snippet   = function_exists( 'promptatlas_search_snippet' )
					? promptatlas_search_snippet( $promptatlas_result_id, $promptatlas_query_text )
					: get_the_excerpt();
				?>
				<li class="search-results__item">
					<div class="search-results__meta">
						<?php if ( 'collection' === get_post_type() ) : ?>
							<span class="card__kicker"><?php esc_html_e( 'Collection', 'promptatlas' ); ?></span>
						<?php else : ?>
							<?php promptatlas_the_category( $promptatlas_result_id ); ?>
						<?php endif; ?>
					</div>

					<h2 class="search-results__title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>

					<?php if ( '' !== $promptatlas_snippet ) : ?>
						<p class="search-results__snippet"><?php echo esc_html( $promptatlas_snippet ); ?></p>
					<?php endif; ?>
				</li>
				<?php
			endwhile;
			?>
		</ol>

		<?php promptatlas_pagination(); ?>

	<?php else : ?>

		<div class="empty-state">
			<h2 class="empty-state__heading"><?php esc_html_e( 'Nothing matched that', 'promptatlas' ); ?></h2>
			<p class="empty-state__message">
				<?php esc_html_e( 'Try fewer words, or a different way of describing the task. The library is still growing, so it may simply not be written yet.', 'promptatlas' ); ?>
			</p>

			<?php
			$promptatlas_suggest = get_terms(
				array(
					'taxonomy'   => 'prompt_category',
					'hide_empty' => true,
					'number'     => 6,
					'orderby'    => 'count',
					'order'      => 'DESC',
				)
			);

			if ( ! is_wp_error( $promptatlas_suggest ) && ! empty( $promptatlas_suggest ) ) :
				?>
				<p class="empty-state__message"><?php esc_html_e( 'Or browse a category:', 'promptatlas' ); ?></p>
				<ul class="term-list" style="justify-content: center;">
					<?php foreach ( $promptatlas_suggest as $promptatlas_term ) : ?>
						<li>
							<a class="term-pill" href="<?php echo esc_url( (string) get_term_link( $promptatlas_term ) ); ?>">
								<?php echo esc_html( $promptatlas_term->name ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php
			endif;
			?>

			<p class="empty-state__actions">
				<a class="button button--primary" href="<?php echo esc_url( (string) get_post_type_archive_link( 'prompt' ) ); ?>">
					<?php esc_html_e( 'Browse all prompts', 'promptatlas' ); ?>
				</a>
			</p>
		</div>

	<?php endif; ?>

</main>

<?php
get_footer();
