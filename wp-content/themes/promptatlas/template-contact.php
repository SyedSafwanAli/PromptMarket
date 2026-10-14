<?php
/**
 * Template Name: Contact
 *
 * Renders the contact form. The handler lives in promptatlas-core, because
 * receiving a DMCA notice must not depend on which theme is active.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

get_header();

$promptatlas_contact = function_exists( 'promptatlas_get_contact_state' )
	? promptatlas_get_contact_state()
	: array(
		'errors' => array(),
		'state'  => array(),
	);

$promptatlas_errors  = $promptatlas_contact['errors'];
$promptatlas_state   = $promptatlas_contact['state'];
$promptatlas_sent    = ! empty( $promptatlas_state['sent'] );
$promptatlas_reasons = function_exists( 'promptatlas_contact_reasons' ) ? promptatlas_contact_reasons() : array();

/**
 * Get a previously submitted value, so a failed submission does not lose it.
 *
 * @param string               $key   Field key.
 * @param array<string, mixed> $state Submission state.
 * @return string The value.
 */
function promptatlas_contact_value( $key, $state ) {
	return isset( $state[ $key ] ) && is_string( $state[ $key ] ) ? $state[ $key ] : '';
}
?>

<main id="primary" class="site-main container" role="main">

	<?php
	while ( have_posts() ) :
		the_post();
		?>

		<header class="entry-header">
			<h1 class="entry-title"><?php the_title(); ?></h1>
		</header>

		<?php if ( get_the_content() ) : ?>
			<div class="entry-content prose">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>

		<?php
	endwhile;
	?>

	<div class="contact-form-wrap">

		<?php if ( $promptatlas_sent ) : ?>

			<div class="notice-message notice-message--success" role="status">
				<strong><?php esc_html_e( 'Thank you — your message has been received.', 'promptatlas' ); ?></strong>
				<?php if ( isset( $promptatlas_state['email_ok'] ) && ! $promptatlas_state['email_ok'] ) : ?>
					<br>
					<?php
					/*
					 * Honest failure state. The message IS saved, so the
					 * submission genuinely succeeded — but the notification
					 * email did not go out, and the visitor should know their
					 * message may be seen later rather than immediately.
					 */
					esc_html_e(
						'One thing to note: our notification email did not go out, so we may not see this straight away. Your message is saved. If it is urgent, please also email us directly.',
						'promptatlas'
					);
					?>
				<?php endif; ?>
			</div>

		<?php else : ?>

			<?php if ( ! empty( $promptatlas_errors['form'] ) ) : ?>
				<div class="notice-message notice-message--error" role="alert">
					<?php echo esc_html( $promptatlas_errors['form'] ); ?>
				</div>
			<?php elseif ( ! empty( $promptatlas_errors ) ) : ?>
				<div class="notice-message notice-message--error" role="alert">
					<?php esc_html_e( 'Please check the fields marked below.', 'promptatlas' ); ?>
				</div>
			<?php endif; ?>

			<form class="contact-form" method="post" action="">
				<?php wp_nonce_field( 'promptatlas_contact', 'promptatlas_contact_nonce' ); ?>
				<input type="hidden" name="promptatlas_rendered" value="<?php echo esc_attr( (string) time() ); ?>">

				<?php
				/*
				 * Honeypot. Hidden from people with CSS and from assistive
				 * technology with aria-hidden and tabindex, so a screen reader
				 * user is never asked to fill a trap field. Bots that parse the
				 * HTML fill it; people never see it.
				 */
				?>
				<div class="contact-form__trap" aria-hidden="true">
					<label for="promptatlas_website"><?php esc_html_e( 'Leave this field empty', 'promptatlas' ); ?></label>
					<input type="text" id="promptatlas_website" name="promptatlas_website" tabindex="-1" autocomplete="off" value="">
				</div>

				<p class="contact-form__field">
					<label for="promptatlas_name"><?php esc_html_e( 'Your name', 'promptatlas' ); ?></label>
					<input
						type="text"
						id="promptatlas_name"
						name="promptatlas_name"
						value="<?php echo esc_attr( promptatlas_contact_value( 'name', $promptatlas_state ) ); ?>"
						required
						autocomplete="name"
						<?php echo isset( $promptatlas_errors['name'] ) ? 'aria-invalid="true" aria-describedby="promptatlas_name_error"' : ''; ?>
					>
					<?php if ( isset( $promptatlas_errors['name'] ) ) : ?>
						<span class="contact-form__error" id="promptatlas_name_error"><?php echo esc_html( $promptatlas_errors['name'] ); ?></span>
					<?php endif; ?>
				</p>

				<p class="contact-form__field">
					<label for="promptatlas_email"><?php esc_html_e( 'Your email', 'promptatlas' ); ?></label>
					<input
						type="email"
						id="promptatlas_email"
						name="promptatlas_email"
						value="<?php echo esc_attr( promptatlas_contact_value( 'email', $promptatlas_state ) ); ?>"
						required
						autocomplete="email"
						<?php echo isset( $promptatlas_errors['email'] ) ? 'aria-invalid="true" aria-describedby="promptatlas_email_error"' : ''; ?>
					>
					<span class="form-hint"><?php esc_html_e( 'Only used to reply to you.', 'promptatlas' ); ?></span>
					<?php if ( isset( $promptatlas_errors['email'] ) ) : ?>
						<span class="contact-form__error" id="promptatlas_email_error"><?php echo esc_html( $promptatlas_errors['email'] ); ?></span>
					<?php endif; ?>
				</p>

				<p class="contact-form__field">
					<label for="promptatlas_reason"><?php esc_html_e( 'What is this about?', 'promptatlas' ); ?></label>
					<select
						id="promptatlas_reason"
						name="promptatlas_reason"
						required
						<?php echo isset( $promptatlas_errors['reason'] ) ? 'aria-invalid="true" aria-describedby="promptatlas_reason_error"' : ''; ?>
					>
						<option value=""><?php esc_html_e( 'Choose one…', 'promptatlas' ); ?></option>
						<?php foreach ( $promptatlas_reasons as $promptatlas_value => $promptatlas_label ) : ?>
							<option
								value="<?php echo esc_attr( $promptatlas_value ); ?>"
								<?php selected( promptatlas_contact_value( 'reason', $promptatlas_state ), $promptatlas_value ); ?>
							>
								<?php echo esc_html( $promptatlas_label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<?php if ( isset( $promptatlas_errors['reason'] ) ) : ?>
						<span class="contact-form__error" id="promptatlas_reason_error"><?php echo esc_html( $promptatlas_errors['reason'] ); ?></span>
					<?php endif; ?>
				</p>

				<p class="contact-form__field">
					<label for="promptatlas_message"><?php esc_html_e( 'Your message', 'promptatlas' ); ?></label>
					<textarea
						id="promptatlas_message"
						name="promptatlas_message"
						rows="8"
						required
						<?php echo isset( $promptatlas_errors['message'] ) ? 'aria-invalid="true" aria-describedby="promptatlas_message_error"' : ''; ?>
					><?php echo esc_textarea( promptatlas_contact_value( 'message', $promptatlas_state ) ); ?></textarea>
					<?php if ( isset( $promptatlas_errors['message'] ) ) : ?>
						<span class="contact-form__error" id="promptatlas_message_error"><?php echo esc_html( $promptatlas_errors['message'] ); ?></span>
					<?php endif; ?>
				</p>

				<p>
					<button type="submit" name="promptatlas_contact_submit" value="1" class="button button--primary">
						<?php esc_html_e( 'Send message', 'promptatlas' ); ?>
					</button>
				</p>

				<p class="form-hint">
					<?php esc_html_e( 'We keep messages for 90 days so we can follow up, then delete them.', 'promptatlas' ); ?>
				</p>
			</form>

		<?php endif; ?>

	</div>

</main>

<?php
get_footer();
