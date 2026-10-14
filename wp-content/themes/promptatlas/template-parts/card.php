<?php
/**
 * Generic content card.
 *
 * The fallback used by get_template_part( 'template-parts/card', $post_type )
 * when no card-{type}.php exists.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

?>
<article id="card-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php
			the_post_thumbnail(
				'promptatlas-card',
				array(
					'loading' => 'lazy',
					'alt'     => '',
				)
			);
			?>
		</a>
	<?php endif; ?>

	<div class="card__body">
		<h3 class="card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php if ( has_excerpt() ) : ?>
			<p class="card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
	</div>
</article>
