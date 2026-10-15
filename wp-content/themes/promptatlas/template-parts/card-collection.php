<?php
/**
 * Collection card.
 *
 * Same image-dominant shell as the prompt card, so a mixed grid reads as one
 * set. The difference is the metadata: a collection shows how many prompts it
 * holds, which is a real count read through the plugin accessor, so
 * unpublished entries are not counted.
 *
 * No save button: Saved applies to prompts, not collections.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

$promptatlas_collection_id    = (int) get_the_ID();
$promptatlas_collection_title = get_the_title();
$promptatlas_prompt_count     = 0;

if ( function_exists( 'promptatlas_get_collection_prompts' ) ) {
	$promptatlas_prompt_count = count( promptatlas_get_collection_prompts( $promptatlas_collection_id ) );
}
?>
<article id="card-<?php echo esc_attr( (string) $promptatlas_collection_id ); ?>" <?php post_class( 'card card--collection' ); ?>>

	<div class="card__media">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php
			the_post_thumbnail(
				'promptatlas-card',
				array(
					'loading' => 'lazy',
					'alt'     => '',
					'width'   => 640,
					'height'  => 360,
				)
			);
			?>
		<?php endif; ?>

		<a class="card__link" href="<?php the_permalink(); ?>">
			<span class="screen-reader-text"><?php echo esc_html( $promptatlas_collection_title ); ?></span>
		</a>

		<div class="card__overlay" aria-hidden="true">
			<span class="card__overlay-title"><?php echo esc_html( $promptatlas_collection_title ); ?></span>
		</div>

		<span class="card__flag"><?php esc_html_e( 'Collection', 'promptatlas' ); ?></span>
	</div>

	<div class="card__meta">
		<div class="card__meta-main">
			<h3 class="card__title">
				<a href="<?php the_permalink(); ?>" tabindex="-1"><?php echo esc_html( $promptatlas_collection_title ); ?></a>
			</h3>

			<?php if ( $promptatlas_prompt_count > 0 ) : ?>
				<span class="card__category">
					<?php
					printf(
						/* translators: %s: number of prompts in the collection. */
						esc_html( _n( '%s prompt', '%s prompts', $promptatlas_prompt_count, 'promptatlas' ) ),
						esc_html( number_format_i18n( $promptatlas_prompt_count ) )
					);
					?>
				</span>
			<?php endif; ?>
		</div>
	</div>

</article>
