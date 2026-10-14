<?php
/**
 * The homepage.
 *
 * Phase 06 scope is the reusable foundation only. The full homepage — the
 * curated sections, the category grid, the featured collections — is Phase 08,
 * which has the design system from Phase 07 to build with.
 *
 * What is here is deliberately restrained and, importantly, honest: it shows
 * the most recent real prompts, and an empty state when there are none. It does
 * not show "trending", "most copied" or "popular", because there is no real
 * data behind any of those and inventing it is forbidden.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

get_header();

$promptatlas_recent = new WP_Query(
	array(
		'post_type'           => 'prompt',
		'post_status'         => 'publish',
		'posts_per_page'      => 6,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
?>

<main id="primary" class="site-main" role="main">

	<section class="home-intro">
		<div class="container home-intro__inner">
			<h1 class="home-intro__title">
				<?php esc_html_e( 'Find a prompt that actually works', 'promptatlas' ); ?>
			</h1>
			<p class="home-intro__lead">
				<?php esc_html_e( 'A growing, hand-written library of AI prompts — each one explained, so you know what to change and why it works.', 'promptatlas' ); ?>
			</p>
			<div class="home-intro__search">
				<?php get_search_form(); ?>
			</div>
		</div>
	</section>

	<section class="home-section container">
		<header class="section-header">
			<h2 class="section-header__title"><?php esc_html_e( 'Latest prompts', 'promptatlas' ); ?></h2>
			<a class="section-header__link" href="<?php echo esc_url( (string) get_post_type_archive_link( 'prompt' ) ); ?>">
				<?php esc_html_e( 'Browse all', 'promptatlas' ); ?>
			</a>
		</header>

		<?php if ( $promptatlas_recent->have_posts() ) : ?>

			<div class="card-grid">
				<?php
				while ( $promptatlas_recent->have_posts() ) :
					$promptatlas_recent->the_post();
					get_template_part( 'template-parts/card', 'prompt' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>

		<?php else : ?>

			<?php
			promptatlas_empty_state(
				__( 'No prompts published yet', 'promptatlas' ),
				__( 'The library is being written. Published prompts will appear here as they go live.', 'promptatlas' )
			);
			?>

		<?php endif; ?>
	</section>

	<?php
	$promptatlas_categories = get_terms(
		array(
			'taxonomy'   => 'prompt_category',
			'hide_empty' => false,
			'orderby'    => 'name',
		)
	);

	if ( ! is_wp_error( $promptatlas_categories ) && ! empty( $promptatlas_categories ) ) :
		?>
		<section class="home-section container">
			<header class="section-header">
				<h2 class="section-header__title"><?php esc_html_e( 'Browse by category', 'promptatlas' ); ?></h2>
			</header>

			<ul class="category-list">
				<?php foreach ( $promptatlas_categories as $promptatlas_category ) : ?>
					<li class="category-list__item">
						<a class="category-list__link" href="<?php echo esc_url( (string) get_term_link( $promptatlas_category ) ); ?>">
							<span class="category-list__name"><?php echo esc_html( $promptatlas_category->name ); ?></span>
							<span class="category-list__count">
								<?php
								printf(
									/* translators: %s: number of prompts in the category. */
									esc_html( _n( '%s prompt', '%s prompts', (int) $promptatlas_category->count, 'promptatlas' ) ),
									esc_html( number_format_i18n( (int) $promptatlas_category->count ) )
								);
								?>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php
	endif;
	?>

</main>

<?php
get_footer();
