<?php
/**
 * Single prompt.
 *
 * Order on the page is deliberate and follows CONTENT_STRATEGY.md 2: title,
 * summary, then the prompt itself and the copy control, then what to replace,
 * then the explanation. The explanation sits BELOW the prompt because nobody
 * reads an essay to reach a copy button.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$promptatlas_id        = (int) get_the_ID();
	$promptatlas_variables = function_exists( 'promptatlas_get_variables' )
		? promptatlas_get_variables( $promptatlas_id )
		: array();
	$promptatlas_example   = get_post_meta( $promptatlas_id, '_promptatlas_example_output', true );
	$promptatlas_tool      = get_post_meta( $promptatlas_id, '_promptatlas_example_tool', true );
	$promptatlas_credit    = get_post_meta( $promptatlas_id, '_promptatlas_attribution', true );
	$promptatlas_related   = function_exists( 'promptatlas_get_related_prompts' )
		? promptatlas_get_related_prompts( $promptatlas_id, 4 )
		: array();
	$promptatlas_in_colls  = function_exists( 'promptatlas_get_collections_for_prompt' )
		? promptatlas_get_collections_for_prompt( $promptatlas_id )
		: array();
	?>

	<main id="primary" class="site-main container" role="main">

		<article id="post-<?php echo esc_attr( (string) $promptatlas_id ); ?>" <?php post_class( 'prompt-single' ); ?>>

			<?php promptatlas_breadcrumbs(); ?>

			<header class="entry-header prompt-single__header">
				<div class="card__eyebrow">
					<?php promptatlas_the_category( $promptatlas_id ); ?>
				</div>

				<h1 class="entry-title"><?php the_title(); ?></h1>

				<?php if ( has_excerpt() ) : ?>
					<p class="prompt-single__summary"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>

				<div class="entry-meta">
					<?php promptatlas_posted_on(); ?>
				</div>
			</header>

			<?php get_template_part( 'template-parts/prompt-block' ); ?>

			<?php if ( ! empty( $promptatlas_variables ) ) : ?>
				<section class="prompt-single__section" aria-labelledby="what-to-replace">
					<h2 id="what-to-replace" class="prompt-single__heading">
						<?php esc_html_e( 'What to replace', 'promptatlas' ); ?>
					</h2>
					<ul class="variable-list">
						<?php foreach ( $promptatlas_variables as $promptatlas_variable ) : ?>
							<li class="variable-list__item">
								<span class="variable-list__name">
									[<?php echo esc_html( isset( $promptatlas_variable['name'] ) ? $promptatlas_variable['name'] : '' ); ?>]
								</span>
								<?php if ( ! empty( $promptatlas_variable['description'] ) ) : ?>
									<p class="variable-list__description">
										<?php echo esc_html( $promptatlas_variable['description'] ); ?>
									</p>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<section class="prompt-single__section entry-content prose">
					<?php the_content(); ?>
				</section>
			<?php endif; ?>

			<?php
			/*
			 * Example output renders only when real output was recorded, and
			 * always names the tool that produced it. CONTENT_STRATEGY.md 6.4
			 * forbids invented examples, so an empty field renders nothing at
			 * all rather than a placeholder.
			 */
			if ( is_string( $promptatlas_example ) && '' !== $promptatlas_example ) :
				?>
				<section class="prompt-single__section" aria-labelledby="example-output">
					<h2 id="example-output" class="prompt-single__heading">
						<?php esc_html_e( 'Example output', 'promptatlas' ); ?>
					</h2>
					<?php if ( is_string( $promptatlas_tool ) && '' !== $promptatlas_tool ) : ?>
						<p class="prompt-single__example-meta">
							<?php
							printf(
								/* translators: %s: the AI tool used, e.g. ChatGPT. */
								esc_html__( 'Produced with %s. Your result will differ.', 'promptatlas' ),
								'<strong>' . esc_html( $promptatlas_tool ) . '</strong>'
							);
							?>
						</p>
					<?php endif; ?>
					<div class="example-output">
						<pre class="example-output__text"><?php echo esc_html( $promptatlas_example ); ?></pre>
					</div>
				</section>
			<?php endif; ?>

			<footer class="prompt-single__footer">

				<?php
				$promptatlas_tools = get_the_terms( $promptatlas_id, 'prompt_tool' );

				if ( ! is_wp_error( $promptatlas_tools ) && ! empty( $promptatlas_tools ) ) :
					?>
					<div class="prompt-single__meta-group">
						<h2 class="prompt-single__meta-label"><?php esc_html_e( 'Works with', 'promptatlas' ); ?></h2>
						<?php promptatlas_the_terms( 'prompt_tool', $promptatlas_id, 'term-pill--tool' ); ?>
					</div>
				<?php endif; ?>

				<?php
				$promptatlas_tags = get_the_terms( $promptatlas_id, 'prompt_tag' );

				if ( ! is_wp_error( $promptatlas_tags ) && ! empty( $promptatlas_tags ) ) :
					?>
					<div class="prompt-single__meta-group">
						<h2 class="prompt-single__meta-label"><?php esc_html_e( 'Tagged', 'promptatlas' ); ?></h2>
						<?php promptatlas_the_terms( 'prompt_tag', $promptatlas_id ); ?>
					</div>
				<?php endif; ?>

				<?php if ( is_string( $promptatlas_credit ) && '' !== $promptatlas_credit ) : ?>
					<div class="prompt-single__meta-group">
						<h2 class="prompt-single__meta-label"><?php esc_html_e( 'Source', 'promptatlas' ); ?></h2>
						<p class="prompt-single__attribution"><?php echo esc_html( $promptatlas_credit ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $promptatlas_in_colls ) ) : ?>
					<div class="prompt-single__meta-group">
						<h2 class="prompt-single__meta-label"><?php esc_html_e( 'Appears in', 'promptatlas' ); ?></h2>
						<ul class="term-list">
							<?php foreach ( $promptatlas_in_colls as $promptatlas_coll_id ) : ?>
								<li>
									<a class="term-pill" href="<?php echo esc_url( (string) get_permalink( $promptatlas_coll_id ) ); ?>">
										<?php echo esc_html( get_the_title( $promptatlas_coll_id ) ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

			</footer>

		</article>

		<?php
		/*
		 * Related prompts. CONTENT_STRATEGY.md 4.8 is explicit that the list is
		 * never padded with unrelated prompts to reach four, so when there are
		 * none the section is replaced with an honest route onward rather than
		 * filler.
		 */
		?>
		<aside class="related" aria-labelledby="related-heading">
			<h2 id="related-heading" class="section-header__title"><?php esc_html_e( 'Related prompts', 'promptatlas' ); ?></h2>

			<?php if ( ! empty( $promptatlas_related ) ) : ?>
				<div class="card-grid">
					<?php
					foreach ( $promptatlas_related as $promptatlas_related_id ) :
						$promptatlas_related_post = get_post( $promptatlas_related_id );

						if ( ! $promptatlas_related_post ) {
							continue;
						}

						$GLOBALS['post'] = $promptatlas_related_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Required by setup_postdata() to render a card for a post outside the main loop; wp_reset_postdata() restores it below.
						setup_postdata( $GLOBALS['post'] );

						get_template_part( 'template-parts/card', 'prompt' );
					endforeach;

					wp_reset_postdata();
					?>
				</div>
			<?php else : ?>
				<p class="related__empty">
					<?php esc_html_e( 'Nothing closely related yet — the library is still growing.', 'promptatlas' ); ?>
				</p>
				<p>
					<a class="button button--secondary" href="<?php echo esc_url( (string) get_post_type_archive_link( 'prompt' ) ); ?>">
						<?php esc_html_e( 'Browse all prompts', 'promptatlas' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</aside>

		<?php get_template_part( 'template-parts/newsletter' ); ?>

	</main>

	<?php
endwhile;

get_footer();
