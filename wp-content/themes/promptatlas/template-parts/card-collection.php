<?php
/**
 * Collection card.
 *
 * Shows a real count of the published prompts in the collection, read through
 * the plugin accessor so unpublished entries are not counted.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

$promptatlas_collection_id = (int) get_the_ID();
$promptatlas_prompt_count  = 0;

if ( function_exists( 'promptatlas_get_collection_prompts' ) ) {
	$promptatlas_prompt_count = count( promptatlas_get_collection_prompts( $promptatlas_collection_id ) );
}
?>
<article id="card-<?php echo esc_attr( (string) $promptatlas_collection_id ); ?>" <?php post_class( 'card card--collection' ); ?>>

	<?php if ( has_post_thumbnail() ) : ?>
		<a class="card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
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
		</a>
	<?php endif; ?>

	<div class="card__body">

		<div class="card__eyebrow">
			<span class="card__kicker"><?php esc_html_e( 'Collection', 'promptatlas' ); ?></span>
		</div>

		<h3 class="card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php if ( has_excerpt() ) : ?>
			<p class="card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<?php if ( $promptatlas_prompt_count > 0 ) : ?>
			<p class="card__count">
				<?php
				printf(
					/* translators: %s: number of prompts in the collection. */
					esc_html( _n( '%s prompt', '%s prompts', $promptatlas_prompt_count, 'promptatlas' ) ),
					esc_html( number_format_i18n( $promptatlas_prompt_count ) )
				);
				?>
			</p>
		<?php endif; ?>

	</div>
</article>
