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
		<button
			type="button"
			class="copy-button js-copy-button"
			data-copy-target="<?php echo esc_attr( $promptatlas_copy_id ); ?>"
			data-label-default="<?php esc_attr_e( 'Copy prompt', 'promptatlas' ); ?>"
			data-label-copied="<?php esc_attr_e( 'Copied', 'promptatlas' ); ?>"
			data-label-failed="<?php esc_attr_e( 'Press Ctrl+C to copy', 'promptatlas' ); ?>"
		>
			<span class="js-copy-button-label"><?php esc_html_e( 'Copy prompt', 'promptatlas' ); ?></span>
		</button>
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
