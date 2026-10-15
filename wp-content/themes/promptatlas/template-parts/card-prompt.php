<?php
/**
 * Prompt card.
 *
 * Image-dominant layout: the artwork is the card, with small metadata beneath
 * and actions revealed on hover.
 *
 * ---------------------------------------------------------------------------
 * What this card does NOT show, and why
 * ---------------------------------------------------------------------------
 * The layout this was built from carries an author avatar, a PRO badge, a like
 * count and a view count. None of them are here:
 *
 * - There are no authors to show. Creator accounts are an explicit non-goal
 *   for this release; Phase 25 specifies them and does not build them.
 * - There are no like or view counts. The site records neither, and inventing
 *   them is the one thing the content-integrity rule forbids outright.
 *
 * The meta row instead carries data that genuinely exists: the category, and
 * the AI tools the prompt is written for. Both are real, both are useful to
 * someone scanning a grid, and neither had to be made up.
 *
 * The hover actions are real too: Save is the Phase 10 feature, working from
 * the prompt ID alone. There is no comment button, because comments are off
 * site-wide (decision 6).
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

$promptatlas_card_id    = (int) get_the_ID();
$promptatlas_card_title = get_the_title();
$promptatlas_card_tools = get_the_terms( $promptatlas_card_id, 'prompt_tool' );
$promptatlas_card_term  = function_exists( 'promptatlas_get_prompt_category' )
	? promptatlas_get_prompt_category( $promptatlas_card_id )
	: null;
?>
<article id="card-<?php echo esc_attr( (string) $promptatlas_card_id ); ?>" <?php post_class( 'card card--prompt' ); ?>>

	<div class="card__media">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php
			/*
			 * Decorative artwork: it carries no information the card does not
			 * state in text, so the alt is empty. Explicit dimensions and the
			 * reserved 4:3 slot mean nothing shifts while it loads.
			 */
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

		<?php
		/*
		 * The whole thumbnail is the link, stretched over the media box. It
		 * carries the accessible name, so there is exactly one link to the
		 * prompt per card rather than an image link and a title link pointing
		 * at the same place — which doubles the tab stops for no benefit.
		 */
		?>
		<a class="card__link" href="<?php the_permalink(); ?>">
			<span class="screen-reader-text"><?php echo esc_html( $promptatlas_card_title ); ?></span>
		</a>

		<div class="card__overlay" aria-hidden="true">
			<span class="card__overlay-title"><?php echo esc_html( $promptatlas_card_title ); ?></span>
		</div>

		<?php
		/*
		 * Save sits above the overlay and outside the link, so it is clickable
		 * and separately focusable. It is hidden entirely without JavaScript,
		 * since it cannot work then.
		 */
		?>
		<button
			type="button"
			class="card__save js-save-button"
			aria-pressed="false"
			data-prompt-id="<?php echo esc_attr( (string) $promptatlas_card_id ); ?>"
			data-label-save="<?php echo esc_attr( sprintf( /* translators: %s: prompt title. */ __( 'Save %s', 'promptatlas' ), $promptatlas_card_title ) ); ?>"
			data-label-saved="<?php echo esc_attr( sprintf( /* translators: %s: prompt title. */ __( 'Saved %s. Select to remove.', 'promptatlas' ), $promptatlas_card_title ) ); ?>"
		>
			<span class="card__save-icon" aria-hidden="true">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
					<path d="M6 3h12a1 1 0 0 1 1 1v16l-7-4-7 4V4a1 1 0 0 1 1-1z"/>
				</svg>
			</span>
			<span class="screen-reader-text js-save-button-label">
				<?php
				printf(
					/* translators: %s: prompt title. */
					esc_html__( 'Save %s', 'promptatlas' ),
					esc_html( $promptatlas_card_title )
				);
				?>
			</span>
		</button>
	</div>

	<div class="card__meta">
		<h3 class="card__title">
			<a href="<?php the_permalink(); ?>" tabindex="-1"><?php echo esc_html( $promptatlas_card_title ); ?></a>
		</h3>

		<?php
		/*
		 * Category and tools share a row BELOW the title, not beside it.
		 * Sitting them next to the title squeezed it into roughly half the
		 * card width, so almost every title truncated after two lines - and a
		 * prompt title is a sentence, so the truncated part was usually the
		 * part that distinguished it.
		 */
		?>
		<div class="card__meta-row">
			<?php if ( $promptatlas_card_term ) : ?>
				<a class="card__category" href="<?php echo esc_url( (string) get_term_link( $promptatlas_card_term ) ); ?>">
					<?php echo esc_html( $promptatlas_card_term->name ); ?>
				</a>
			<?php else : ?>
				<span></span>
			<?php endif; ?>

			<?php if ( ! is_wp_error( $promptatlas_card_tools ) && ! empty( $promptatlas_card_tools ) ) : ?>
				<ul class="card__tools">
					<?php foreach ( array_slice( $promptatlas_card_tools, 0, 2 ) as $promptatlas_card_tool ) : ?>
						<li class="card__tool"><?php echo esc_html( $promptatlas_card_tool->name ); ?></li>
					<?php endforeach; ?>
					<?php if ( count( $promptatlas_card_tools ) > 2 ) : ?>
						<li class="card__tool card__tool--more">
							<?php
							printf(
								/* translators: %s: number of additional tools. */
								esc_html__( '+%s', 'promptatlas' ),
								esc_html( number_format_i18n( count( $promptatlas_card_tools ) - 2 ) )
							);
							?>
						</li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>

</article>
