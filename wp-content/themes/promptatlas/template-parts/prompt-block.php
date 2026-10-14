<?php
/**
 * The prompt block: the text a visitor copies, plus the copy control.
 *
 * The single most important element on the site. Placed high on the page and
 * before the explanation, because the audience research in CONTENT_STRATEGY.md 2
 * is explicit that visitors come to copy, not to read an essay first.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

$promptatlas_block_post_id = (int) get_the_ID();
$promptatlas_text          = function_exists( 'promptatlas_get_prompt_text' )
	? promptatlas_get_prompt_text( $promptatlas_block_post_id )
	: '';

if ( '' === $promptatlas_text ) {
	/*
	 * Honest fallback. A prompt post with no prompt text is an editing mistake,
	 * not a visitor-facing feature, so say so plainly rather than rendering an
	 * empty black box that looks broken.
	 */
	?>
	<div class="notice-message notice-message--error">
		<?php esc_html_e( 'This prompt has no prompt text yet.', 'promptatlas' ); ?>
	</div>
	<?php
	return;
}

$promptatlas_copy_id = 'prompt-text-' . $promptatlas_block_post_id;
?>

<div class="prompt-block">
	<div class="prompt-block__header">
		<span class="prompt-block__label"><?php esc_html_e( 'Prompt', 'promptatlas' ); ?></span>
		<div class="prompt-block__actions">
		<button
			type="button"
			class="copy-button js-copy-button"
			data-copy-target="<?php echo esc_attr( $promptatlas_copy_id ); ?>"
			data-prompt-id="<?php echo esc_attr( (string) $promptatlas_block_post_id ); ?>"
			data-label-default="<?php esc_attr_e( 'Copy prompt', 'promptatlas' ); ?>"
			data-label-copied="<?php esc_attr_e( 'Copied', 'promptatlas' ); ?>"
			data-label-failed="<?php esc_attr_e( 'Press Ctrl+C to copy', 'promptatlas' ); ?>"
		>
			<span class="js-copy-button-label"><?php esc_html_e( 'Copy prompt', 'promptatlas' ); ?></span>
		</button>

		<button
			type="button"
			class="save-button js-save-button"
			aria-pressed="false"
			data-prompt-id="<?php echo esc_attr( (string) $promptatlas_block_post_id ); ?>"
			data-label-save="<?php esc_attr_e( 'Save', 'promptatlas' ); ?>"
			data-label-saved="<?php esc_attr_e( 'Saved', 'promptatlas' ); ?>"
		>
			<span class="js-save-button-label"><?php esc_html_e( 'Save', 'promptatlas' ); ?></span>
		</button>
		</div>
	</div>

	<?php
	/*
	 * `pre` preserves the author's line breaks and indentation, which are
	 * meaningful in a prompt. CSS sets white-space: pre-wrap so long lines wrap
	 * instead of forcing the page to scroll sideways on a phone.
	 *
	 * The content is escaped inside promptatlas_format_prompt_text() before any
	 * markup is added, so the only HTML here is the token span.
	 */
	?>
	<pre
		id="<?php echo esc_attr( $promptatlas_copy_id ); ?>"
		class="prompt-block__text"
		tabindex="0"
	><?php echo promptatlas_format_prompt_text( $promptatlas_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped with esc_html() inside the function before token markup is added; see its docblock. ?></pre>

	<p class="prompt-block__status" role="status" aria-live="polite"></p>
</div>

<?php
/*
 * Decision, which Phase 10 requires to be stated and made obvious: COPY
 * DELIVERS THE RAW PROMPT, placeholders included.
 *
 * The alternative - a form that fills the placeholders before copying - was
 * rejected. It would turn a one-click action into a form to complete, and its
 * real failure mode is worse: a visitor who skips a field silently copies a
 * prompt containing an empty or half-filled slot and never notices. Leaving
 * [JOB_TITLE] visible in the pasted text is self-evident; a blank where it
 * should have been is not.
 *
 * The placeholders are highlighted in the block above, this line says so
 * explicitly, and the "What to replace" section below explains each one.
 */
$promptatlas_block_vars = function_exists( 'promptatlas_get_variables' )
	? promptatlas_get_variables( $promptatlas_block_post_id )
	: array();

if ( ! empty( $promptatlas_block_vars ) ) :
	?>
	<p class="prompt-block__note">
		<?php
		printf(
			esc_html(
				/* translators: %s: the number of placeholders in the prompt. */
				_n(
					'Copies exactly as shown, including the %s highlighted placeholder — replace it before you use the prompt.',
					'Copies exactly as shown, including the %s highlighted placeholders — replace them before you use the prompt.',
					count( $promptatlas_block_vars ),
					'promptatlas'
				)
			),
			esc_html( number_format_i18n( count( $promptatlas_block_vars ) ) )
		);
		?>
	</p>
	<?php
endif;
?>
