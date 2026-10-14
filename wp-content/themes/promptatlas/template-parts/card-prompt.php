<?php
/**
 * Prompt card.
 *
 * Used in every listing: the homepage, the prompt archive, taxonomy archives
 * and search results.
 *
 * Shows the category and the summary, and nothing that would have to be
 * invented. There is deliberately no rating, no copy count and no "popular"
 * badge: none of those have real data behind them, and the content-integrity
 * rule forbids fabricating them.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

$promptatlas_card_id = (int) get_the_ID();
?>
<article id="card-<?php echo esc_attr( (string) $promptatlas_card_id ); ?>" <?php post_class( 'card card--prompt' ); ?>>

	<div class="card__body">

		<?php if ( function_exists( 'promptatlas_the_category' ) ) : ?>
			<div class="card__eyebrow">
				<?php promptatlas_the_category( $promptatlas_card_id ); ?>
			</div>
		<?php endif; ?>

		<h3 class="card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php if ( has_excerpt() ) : ?>
			<p class="card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<?php
		$promptatlas_card_tools = get_the_terms( $promptatlas_card_id, 'prompt_tool' );

		if ( ! is_wp_error( $promptatlas_card_tools ) && ! empty( $promptatlas_card_tools ) ) :
			?>
			<ul class="card__tools">
				<?php foreach ( array_slice( $promptatlas_card_tools, 0, 3 ) as $promptatlas_card_tool ) : ?>
					<li class="term-pill term-pill--tool"><?php echo esc_html( $promptatlas_card_tool->name ); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php
		endif;
		?>

	</div>
</article>
