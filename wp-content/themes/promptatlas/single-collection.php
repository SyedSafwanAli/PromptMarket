<?php
/**
 * Single collection.
 *
 * Phase 11 owns collections properly. This renders the real content model so
 * the page is genuinely functional now rather than a placeholder: the intro,
 * then the prompts in the editorial order the collection stores.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$promptatlas_coll_id       = (int) get_the_ID();
	$promptatlas_prompts       = function_exists( 'promptatlas_get_collection_prompts' )
		? promptatlas_get_collection_prompts( $promptatlas_coll_id )
		: array();
	$promptatlas_count         = count( $promptatlas_prompts );
	$promptatlas_notes         = function_exists( 'promptatlas_get_collection_notes' )
		? promptatlas_get_collection_notes( $promptatlas_coll_id )
		: array();
	$promptatlas_related_colls = function_exists( 'promptatlas_get_related_collections' )
		? promptatlas_get_related_collections( $promptatlas_coll_id, 3 )
		: array();
	?>

	<main id="primary" class="site-main container" role="main">

		<article id="post-<?php echo esc_attr( (string) $promptatlas_coll_id ); ?>" <?php post_class( 'collection-single' ); ?>>

			<?php promptatlas_breadcrumbs(); ?>

			<header class="entry-header">
				<div class="card__eyebrow">
					<span class="card__kicker"><?php esc_html_e( 'Collection', 'promptatlas' ); ?></span>
				</div>

				<h1 class="entry-title"><?php the_title(); ?></h1>

				<?php if ( has_excerpt() ) : ?>
					<p class="prompt-single__summary"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>

				<div class="entry-meta">
					<?php promptatlas_posted_on(); ?>
					<?php if ( $promptatlas_count > 0 ) : ?>
						<span class="entry-meta__sep" aria-hidden="true">·</span>
						<?php
						printf(
							/* translators: %s: number of prompts in the collection. */
							esc_html( _n( '%s prompt', '%s prompts', $promptatlas_count, 'promptatlas' ) ),
							esc_html( number_format_i18n( $promptatlas_count ) )
						);
						?>
					<?php endif; ?>
				</div>
			</header>

			<?php if ( get_the_content() ) : ?>
				<div class="entry-content prose">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

		</article>

		<?php
		if ( ! empty( $promptatlas_prompts ) && function_exists( 'promptatlas_prime_posts' ) ) {
			promptatlas_prime_posts( $promptatlas_prompts, false );
		}
		?>
		<section class="collection-prompts" aria-labelledby="collection-prompts-heading">
			<h2 id="collection-prompts-heading" class="section-header__title">
				<?php esc_html_e( 'Prompts in this collection', 'promptatlas' ); ?>
			</h2>

			<?php if ( ! empty( $promptatlas_prompts ) ) : ?>
				<ol class="collection-list">
					<?php foreach ( $promptatlas_prompts as $promptatlas_index => $promptatlas_prompt_id ) : ?>
						<li class="collection-list__item">
							<span class="collection-list__number" aria-hidden="true">
								<?php echo esc_html( number_format_i18n( $promptatlas_index + 1 ) ); ?>
							</span>
							<div class="collection-list__body">
								<h3 class="collection-list__title">
									<a href="<?php echo esc_url( (string) get_permalink( $promptatlas_prompt_id ) ); ?>">
										<?php echo esc_html( get_the_title( $promptatlas_prompt_id ) ); ?>
									</a>
								</h3>
								<?php
								$promptatlas_item_excerpt = get_the_excerpt( $promptatlas_prompt_id );

								if ( '' !== $promptatlas_item_excerpt ) :
									?>
									<p class="collection-list__excerpt">
										<?php echo esc_html( $promptatlas_item_excerpt ); ?>
									</p>
									<?php
								endif;

								/*
								 * The editor's note on this entry. This is the
								 * thing a bare list of links cannot do: it says
								 * why this prompt is here and when to reach for
								 * it, which is the editorial value of the
								 * collection.
								 */
								if ( ! empty( $promptatlas_notes[ $promptatlas_prompt_id ] ) ) :
									?>
									<p class="collection-list__note">
										<?php echo esc_html( $promptatlas_notes[ $promptatlas_prompt_id ] ); ?>
									</p>
									<?php
								endif;
								?>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php else : ?>
				<p class="related__empty">
					<?php esc_html_e( 'This collection does not list any published prompts yet.', 'promptatlas' ); ?>
				</p>
			<?php endif; ?>
		</section>

		<?php if ( ! empty( $promptatlas_related_colls ) ) : ?>
			<aside class="related" aria-labelledby="related-collections-heading">
				<h2 id="related-collections-heading" class="section-header__title">
					<?php esc_html_e( 'Related collections', 'promptatlas' ); ?>
				</h2>
				<ul class="term-list" style="margin-top: var(--space-md);">
					<?php foreach ( $promptatlas_related_colls as $promptatlas_other_id ) : ?>
						<li>
							<a class="term-pill" href="<?php echo esc_url( (string) get_permalink( $promptatlas_other_id ) ); ?>">
								<?php echo esc_html( get_the_title( $promptatlas_other_id ) ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</aside>
		<?php endif; ?>

	</main>

	<?php
endwhile;

get_footer();
