<?php
/**
 * Newsletter signup block.
 *
 * Appears at the END of a prompt page and on the prompt archive — after someone
 * has had something useful from the site. Never as a popup, never as an
 * interstitial, and never above the content.
 *
 * The block reserves its own space in the layout, so it cannot shift anything
 * when it renders. There is no fabricated subscriber count and no social
 * proof: the site has no numbers worth quoting, and inventing them is forbidden.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

$promptatlas_signup = function_exists( 'promptatlas_get_signup_state' )
	? promptatlas_get_signup_state()
	: null;
?>

<aside class="signup" aria-labelledby="signup-heading">

	<h2 id="signup-heading" class="signup__title">
		<?php esc_html_e( 'Get new prompts by email', 'promptatlas' ); ?>
	</h2>

	<p class="signup__promise">
		<?php
		/*
		 * A specific promise, not "tips and updates". The reader can tell
		 * whether they want this, which is the only way a list stays healthy.
		 */
		esc_html_e( 'About once a month, the prompts worth knowing about. Nothing else, and one click to stop.', 'promptatlas' );
		?>
	</p>

	<?php if ( $promptatlas_signup ) : ?>
		<p
			class="notice-message notice-message--<?php echo esc_attr( 'success' === $promptatlas_signup['type'] ? 'success' : 'error' ); ?>"
			role="status"
		>
			<?php echo esc_html( $promptatlas_signup['message'] ); ?>
		</p>
	<?php endif; ?>

	<form class="signup__form" method="post" action="">
		<?php wp_nonce_field( 'promptatlas_subscribe', 'promptatlas_subscribe_nonce' ); ?>

		<div class="contact-form__trap" aria-hidden="true">
			<label for="promptatlas_company"><?php esc_html_e( 'Leave this field empty', 'promptatlas' ); ?></label>
			<input type="text" id="promptatlas_company" name="promptatlas_company" tabindex="-1" autocomplete="off" value="">
		</div>

		<label class="screen-reader-text" for="promptatlas_subscribe_email">
			<?php esc_html_e( 'Your email address', 'promptatlas' ); ?>
		</label>
		<input
			type="email"
			id="promptatlas_subscribe_email"
			name="promptatlas_subscribe_email"
			class="signup__input"
			placeholder="<?php esc_attr_e( 'you@example.com', 'promptatlas' ); ?>"
			required
			autocomplete="email"
		>

		<button type="submit" name="promptatlas_subscribe" value="1" class="button button--accent">
			<?php esc_html_e( 'Subscribe', 'promptatlas' ); ?>
		</button>
	</form>

	<p class="signup__small">
		<?php esc_html_e( 'We send a confirmation link first — you are not subscribed until you click it. We never share your address.', 'promptatlas' ); ?>
	</p>

</aside>
