<?php
/**
 * Contact form.
 *
 * Custom, not a form plugin. DECISIONS.md decision 4: one form with four fields
 * does not justify a dependency that loads its own CSS and JavaScript on every
 * page of the site to render it.
 *
 * ---------------------------------------------------------------------------
 * Storage decision: EMAIL *AND* DATABASE
 * ---------------------------------------------------------------------------
 * Both, deliberately. Email alone is the obvious choice and it is wrong here:
 * wp_mail() through a shared host fails silently and often, and the messages
 * this form will carry include DMCA notices and content corrections. Losing one
 * because a mail server rejected it is a legal problem, not an inconvenience.
 *
 * So every submission is stored first, then emailed. If the email fails the
 * submission is still safe, the visitor is told the truth, and the owner sees
 * it in the admin.
 *
 * Messages are stored as a private post type: WordPress then provides the list
 * table, search, trash and capability checks without a custom table.
 *
 * Retention: 90 days, pruned daily. Phase 17's privacy policy must say so.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Days a stored message is kept.
 */
const PROMPTATLAS_MESSAGE_RETENTION_DAYS = 90;

/**
 * Seconds a visitor must wait between submissions.
 */
const PROMPTATLAS_CONTACT_THROTTLE = 60;

/**
 * Minimum seconds between the form rendering and being submitted.
 *
 * A human cannot read four fields and write a message in under three seconds.
 * A bot posting the form directly takes none.
 */
const PROMPTATLAS_CONTACT_MIN_SECONDS = 3;

/**
 * Register the private message post type.
 *
 * Not public, not queryable, not in search, no archive. It exists only as an
 * admin inbox.
 *
 * @return void
 */
function promptatlas_register_message_type() {
	register_post_type(
		'pa_message',
		array(
			'labels'              => array(
				'name'          => __( 'Messages', 'promptatlas' ),
				'singular_name' => __( 'Message', 'promptatlas' ),
				'menu_name'     => __( 'Messages', 'promptatlas' ),
				'all_items'     => __( 'Messages', 'promptatlas' ),
				'search_items'  => __( 'Search Messages', 'promptatlas' ),
				'not_found'     => __( 'No messages.', 'promptatlas' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=prompt',
			'show_in_rest'        => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array( 'title', 'editor' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'capabilities'        => array(
				// Nothing creates these except the form handler.
				'create_posts' => 'do_not_allow',
			),
		)
	);
}
add_action( 'init', 'promptatlas_register_message_type', 7 );

/**
 * Get a short-lived, non-reversible key for throttling.
 *
 * Rate limiting needs to tell two visitors apart for about a minute. It does
 * NOT need to know who they are, so the address is hashed with the site's salt
 * and the hash is stored in a transient that expires on its own. Nothing
 * identifying is written to the database, and nothing persists.
 *
 * @return string A hash, or an empty string when no address is available.
 */
function promptatlas_throttle_key() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] )
		? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
		: '';

	if ( '' === $ip ) {
		return '';
	}

	return 'promptatlas_contact_' . hash( 'sha256', $ip . wp_salt( 'nonce' ) );
}

/**
 * Validate a submission.
 *
 * Returns field-keyed errors so each message can be rendered next to the field
 * it belongs to, which is what makes the error useful rather than decorative.
 *
 * @param array<string, string> $input Unslashed raw input.
 * @return array<string, string> Errors keyed by field name. Empty when valid.
 */
function promptatlas_validate_contact( $input ) {
	$errors = array();

	$name = isset( $input['name'] ) ? trim( $input['name'] ) : '';
	if ( '' === $name ) {
		$errors['name'] = __( 'Please tell us your name.', 'promptatlas' );
	} elseif ( mb_strlen( $name ) > 100 ) {
		$errors['name'] = __( 'That name is too long.', 'promptatlas' );
	}

	$email = isset( $input['email'] ) ? trim( $input['email'] ) : '';
	if ( '' === $email ) {
		$errors['email'] = __( 'Please add an email address so we can reply.', 'promptatlas' );
	} elseif ( ! is_email( $email ) ) {
		$errors['email'] = __( 'That does not look like an email address.', 'promptatlas' );
	}

	$reason = isset( $input['reason'] ) ? trim( $input['reason'] ) : '';
	if ( ! in_array( $reason, array_keys( promptatlas_contact_reasons() ), true ) ) {
		$errors['reason'] = __( 'Please choose what this is about.', 'promptatlas' );
	}

	$message = isset( $input['message'] ) ? trim( $input['message'] ) : '';
	if ( '' === $message ) {
		$errors['message'] = __( 'Please write your message.', 'promptatlas' );
	} elseif ( mb_strlen( $message ) < 10 ) {
		$errors['message'] = __( 'That message is very short — please add a little more so we can help.', 'promptatlas' );
	} elseif ( mb_strlen( $message ) > 5000 ) {
		$errors['message'] = __( 'That message is too long. Please keep it under 5000 characters.', 'promptatlas' );
	}

	return $errors;
}

/**
 * The reasons a visitor can pick.
 *
 * DMCA and corrections are listed explicitly, because those are the messages
 * that must not be lost in a general enquiry pile.
 *
 * @return array<string, string> Value to label.
 */
function promptatlas_contact_reasons() {
	return array(
		'general'    => __( 'General question', 'promptatlas' ),
		'correction' => __( 'A prompt is wrong or out of date', 'promptatlas' ),
		'suggestion' => __( 'Suggest a prompt', 'promptatlas' ),
		'copyright'  => __( 'Copyright or DMCA', 'promptatlas' ),
		'privacy'    => __( 'Privacy request', 'promptatlas' ),
	);
}

/**
 * Handle a contact submission.
 *
 * Runs on `template_redirect` so it happens before any output, which lets a
 * successful submission redirect and avoid the browser offering to resubmit.
 *
 * @return void
 */
function promptatlas_handle_contact() {
	if ( ! isset( $_POST['promptatlas_contact_submit'] ) ) {
		return;
	}

	$nonce = isset( $_POST['promptatlas_contact_nonce'] )
		? sanitize_text_field( wp_unslash( $_POST['promptatlas_contact_nonce'] ) )
		: '';

	if ( ! wp_verify_nonce( $nonce, 'promptatlas_contact' ) ) {
		promptatlas_set_contact_state(
			array( 'form' => __( 'Your session expired. Please try sending that again.', 'promptatlas' ) ),
			array()
		);
		return;
	}

	$input = array(
		'name'    => isset( $_POST['promptatlas_name'] ) ? sanitize_text_field( wp_unslash( $_POST['promptatlas_name'] ) ) : '',
		'email'   => isset( $_POST['promptatlas_email'] ) ? sanitize_text_field( wp_unslash( $_POST['promptatlas_email'] ) ) : '',
		'reason'  => isset( $_POST['promptatlas_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['promptatlas_reason'] ) ) : '',
		'message' => isset( $_POST['promptatlas_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['promptatlas_message'] ) ) : '',
	);

	/*
	 * Spam checks, in order of cheapness. Neither depends on a third-party
	 * script, which Phase 13 rules out, and neither asks the visitor to solve a
	 * puzzle.
	 *
	 * 1. Honeypot: a field hidden from people. Anything that fills it is not a
	 *    person. Silently accepted so the bot does not learn it was caught.
	 * 2. Timing: the form records when it was rendered.
	 */
	// Sanitised even though the value is only tested for emptiness and never
	// stored or echoed — there is no reason for raw input to exist in a
	// variable at all.
	$honeypot = isset( $_POST['promptatlas_website'] )
		? trim( sanitize_text_field( wp_unslash( $_POST['promptatlas_website'] ) ) )
		: '';

	if ( '' !== $honeypot ) {
		promptatlas_set_contact_state( array(), array( 'sent' => true ) );
		return;
	}

	$rendered = isset( $_POST['promptatlas_rendered'] ) ? absint( $_POST['promptatlas_rendered'] ) : 0;

	if ( $rendered > 0 && ( time() - $rendered ) < PROMPTATLAS_CONTACT_MIN_SECONDS ) {
		promptatlas_set_contact_state( array(), array( 'sent' => true ) );
		return;
	}

	// Rate limiting.
	$throttle_key = promptatlas_throttle_key();

	if ( '' !== $throttle_key && false !== get_transient( $throttle_key ) ) {
		promptatlas_set_contact_state(
			array( 'form' => __( 'You just sent a message. Please wait a minute before sending another.', 'promptatlas' ) ),
			$input
		);
		return;
	}

	$errors = promptatlas_validate_contact( $input );

	if ( ! empty( $errors ) ) {
		promptatlas_set_contact_state( $errors, $input );
		return;
	}

	// Store first. If the email fails, the message is still safe.
	$reasons = promptatlas_contact_reasons();

	$message_id = wp_insert_post(
		array(
			'post_type'    => 'pa_message',
			'post_status'  => 'publish',
			'post_title'   => sprintf(
				/* translators: 1: reason label, 2: sender name. */
				__( '%1$s — from %2$s', 'promptatlas' ),
				isset( $reasons[ $input['reason'] ] ) ? $reasons[ $input['reason'] ] : $input['reason'],
				$input['name']
			),
			'post_content' => $input['message'],
		),
		true
	);

	if ( is_wp_error( $message_id ) ) {
		promptatlas_set_contact_state(
			array( 'form' => __( 'Something went wrong saving your message. Please try again shortly.', 'promptatlas' ) ),
			$input
		);
		return;
	}

	update_post_meta( $message_id, '_promptatlas_from_name', $input['name'] );
	update_post_meta( $message_id, '_promptatlas_from_email', $input['email'] );
	update_post_meta( $message_id, '_promptatlas_reason', $input['reason'] );

	if ( '' !== $throttle_key ) {
		set_transient( $throttle_key, 1, PROMPTATLAS_CONTACT_THROTTLE );
	}

	$sent = promptatlas_send_contact_email( $input, (int) $message_id );

	update_post_meta( $message_id, '_promptatlas_emailed', $sent ? '1' : '0' );

	promptatlas_set_contact_state(
		array(),
		array(
			'sent'      => true,
			'email_ok'  => $sent,
			'reference' => (int) $message_id,
		)
	);
}
add_action( 'template_redirect', 'promptatlas_handle_contact' );

/**
 * Email a submission to the site administrator.
 *
 * Reply-To carries the sender, while From stays on the site's own domain —
 * sending as the visitor's address is the single fastest way to get mail
 * rejected by SPF and DMARC.
 *
 * @param array<string, string> $input      Validated input.
 * @param int                   $message_id Stored message ID.
 * @return bool Whether wp_mail() reported success.
 */
function promptatlas_send_contact_email( $input, $message_id ) {
	$to      = get_option( 'admin_email' );
	$reasons = promptatlas_contact_reasons();
	$label   = isset( $reasons[ $input['reason'] ] ) ? $reasons[ $input['reason'] ] : $input['reason'];

	$subject = sprintf(
		/* translators: 1: site name, 2: reason label. */
		__( '[%1$s] %2$s', 'promptatlas' ),
		wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		$label
	);

	$body = implode(
		"\n",
		array(
			sprintf( /* translators: %s: sender name. */ __( 'From: %s', 'promptatlas' ), $input['name'] ),
			sprintf( /* translators: %s: sender email. */ __( 'Email: %s', 'promptatlas' ), $input['email'] ),
			sprintf( /* translators: %s: reason label. */ __( 'About: %s', 'promptatlas' ), $label ),
			'',
			$input['message'],
			'',
			'---',
			sprintf(
				/* translators: %s: admin URL for the stored message. */
				__( 'Stored copy: %s', 'promptatlas' ),
				admin_url( 'post.php?post=' . $message_id . '&action=edit' )
			),
		)
	);

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $input['name'] . ' <' . $input['email'] . '>',
	);

	return (bool) wp_mail( $to, $subject, $body, $headers );
}

/**
 * Stash the result of a submission for the template to render.
 *
 * Held in a global rather than the session, because the handler and the
 * template run in the same request.
 *
 * @param array<string, string> $errors Field errors.
 * @param array<string, mixed>  $state  Values and flags.
 * @return void
 */
function promptatlas_set_contact_state( $errors, $state ) {
	$GLOBALS['promptatlas_contact_state'] = array(
		'errors' => $errors,
		'state'  => $state,
	);
}

/**
 * Get the current submission state.
 *
 * @return array{errors: array<string, string>, state: array<string, mixed>} State.
 */
function promptatlas_get_contact_state() {
	if ( isset( $GLOBALS['promptatlas_contact_state'] ) && is_array( $GLOBALS['promptatlas_contact_state'] ) ) {
		return $GLOBALS['promptatlas_contact_state'];
	}

	return array(
		'errors' => array(),
		'state'  => array(),
	);
}

/**
 * Show the sender's details on the message edit screen.
 *
 * @return void
 */
function promptatlas_message_meta_box() {
	add_meta_box(
		'promptatlas-message-from',
		__( 'Sender', 'promptatlas' ),
		'promptatlas_render_message_meta',
		'pa_message',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'promptatlas_message_meta_box' );

/**
 * Render the sender details.
 *
 * @param WP_Post $post The message.
 * @return void
 */
function promptatlas_render_message_meta( $post ) {
	$name    = get_post_meta( $post->ID, '_promptatlas_from_name', true );
	$email   = get_post_meta( $post->ID, '_promptatlas_from_email', true );
	$emailed = get_post_meta( $post->ID, '_promptatlas_emailed', true );
	?>
	<p><strong><?php esc_html_e( 'Name', 'promptatlas' ); ?></strong><br><?php echo esc_html( (string) $name ); ?></p>
	<p>
		<strong><?php esc_html_e( 'Email', 'promptatlas' ); ?></strong><br>
		<a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( (string) $email ); ?></a>
	</p>
	<p>
		<strong><?php esc_html_e( 'Email notification', 'promptatlas' ); ?></strong><br>
		<?php if ( '1' === $emailed ) : ?>
			<span style="color:#486c2f;"><?php esc_html_e( 'Sent to the site admin address.', 'promptatlas' ); ?></span>
		<?php else : ?>
			<span style="color:#a3231b;">
				<?php esc_html_e( 'Not sent — mail delivery failed. This stored copy is the only record.', 'promptatlas' ); ?>
			</span>
		<?php endif; ?>
	</p>
	<?php
}

/**
 * Delete messages past the retention period.
 *
 * @return void
 */
function promptatlas_prune_messages() {
	$old = get_posts(
		array(
			'post_type'      => 'pa_message',
			'post_status'    => 'any',
			'posts_per_page' => 100,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'date_query'     => array(
				array(
					'before' => PROMPTATLAS_MESSAGE_RETENTION_DAYS . ' days ago',
				),
			),
		)
	);

	foreach ( $old as $id ) {
		wp_delete_post( (int) $id, true );
	}
}
add_action( 'promptatlas_prune_messages', 'promptatlas_prune_messages' );

/**
 * Schedule the message prune.
 *
 * @return void
 */
function promptatlas_schedule_message_prune() {
	if ( ! wp_next_scheduled( 'promptatlas_prune_messages' ) ) {
		wp_schedule_event( time() + ( 2 * HOUR_IN_SECONDS ), 'daily', 'promptatlas_prune_messages' );
	}
}

/**
 * Clear the message prune schedule.
 *
 * @return void
 */
function promptatlas_unschedule_message_prune() {
	$timestamp = wp_next_scheduled( 'promptatlas_prune_messages' );

	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'promptatlas_prune_messages' );
	}
}
