<?php
/**
 * Newsletter signup with double opt-in.
 *
 * ---------------------------------------------------------------------------
 * Decisions this implements (DECISIONS.md decision 4: "capture now, sending
 * deferred")
 * ---------------------------------------------------------------------------
 * WHERE IT APPEARS: at the end of a prompt page and on the prompt archive -
 * after someone has had something useful. Nowhere else.
 *
 * WHERE IT DOES NOT: no interstitial, no exit-intent modal, no popup of any
 * kind, nothing on the homepage above the content, and nothing that moves the
 * page after it loads. The block reserves its own space.
 *
 * WHAT THE SUBSCRIBER IS PROMISED: stated specifically in the form itself -
 * new prompts, roughly monthly, no more than that. Not "tips and updates".
 *
 * DOUBLE OPT-IN: an address is `pending` until someone clicks the link in the
 * confirmation email. Only `confirmed` addresses are a list. This is not
 * optional politeness - a single opt-in list collected from a public form will
 * contain addresses that never asked, and mailing them is what gets a sending
 * domain blocked.
 *
 * WHERE THEY ARE STORED: this site's database, in a table owned by this plugin.
 * No provider is chosen yet, because choosing a sending provider before there
 * is a list to send to is premature and depends on the domain (decision 1).
 *
 * IF A PROVIDER IS UNAVAILABLE: there is no provider, so there is nothing to be
 * unavailable. When one is added, the local table stays the source of truth and
 * the provider becomes a sync target - not the other way round.
 *
 * UNSUBSCRIBE: a one-click tokenised link, no login, no questions.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get the subscribers table name.
 *
 * @return string Prefixed table name.
 */
function promptatlas_subscribers_table() {
	global $wpdb;

	return $wpdb->prefix . 'promptatlas_subscribers';
}

/**
 * Create the subscribers table.
 *
 * @return void
 */
function promptatlas_create_subscribers_table() {
	global $wpdb;

	$table   = promptatlas_subscribers_table();
	$collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		email varchar(190) NOT NULL DEFAULT '',
		status varchar(20) NOT NULL DEFAULT 'pending',
		token char(64) NOT NULL DEFAULT '',
		created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		confirmed_at datetime DEFAULT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email),
		KEY status (status),
		KEY token (token)
	) {$collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

/**
 * Register the query variables used by the confirm and unsubscribe links.
 *
 * @param string[] $vars Query variables.
 * @return string[] Filtered variables.
 */
function promptatlas_newsletter_query_vars( $vars ) {
	$vars[] = 'pa_confirm';
	$vars[] = 'pa_unsubscribe';

	return $vars;
}
add_filter( 'query_vars', 'promptatlas_newsletter_query_vars' );

/**
 * Handle a signup submission.
 *
 * @return void
 */
function promptatlas_handle_signup() {
	if ( ! isset( $_POST['promptatlas_subscribe'] ) ) {
		return;
	}

	$nonce = isset( $_POST['promptatlas_subscribe_nonce'] )
		? sanitize_text_field( wp_unslash( $_POST['promptatlas_subscribe_nonce'] ) )
		: '';

	if ( ! wp_verify_nonce( $nonce, 'promptatlas_subscribe' ) ) {
		promptatlas_set_signup_state( 'error', __( 'Your session expired. Please try again.', 'promptatlas' ) );
		return;
	}

	// Same honeypot approach as the contact form.
	$trap = isset( $_POST['promptatlas_company'] )
		? trim( sanitize_text_field( wp_unslash( $_POST['promptatlas_company'] ) ) )
		: '';

	if ( '' !== $trap ) {
		promptatlas_set_signup_state( 'success', __( 'Almost there — check your inbox for a confirmation link.', 'promptatlas' ) );
		return;
	}

	$email = isset( $_POST['promptatlas_subscribe_email'] )
		? sanitize_email( wp_unslash( $_POST['promptatlas_subscribe_email'] ) )
		: '';

	if ( '' === $email || ! is_email( $email ) ) {
		promptatlas_set_signup_state( 'error', __( 'That does not look like an email address.', 'promptatlas' ) );
		return;
	}

	$throttle = promptatlas_throttle_key();

	if ( '' !== $throttle && false !== get_transient( $throttle . '_nl' ) ) {
		promptatlas_set_signup_state( 'error', __( 'Please wait a moment before trying again.', 'promptatlas' ) );
		return;
	}

	if ( '' !== $throttle ) {
		set_transient( $throttle . '_nl', 1, PROMPTATLAS_CONTACT_THROTTLE );
	}

	$result = promptatlas_add_subscriber( $email );

	if ( 'already_confirmed' === $result ) {
		/*
		 * Deliberately the same wording as a new signup. Saying "you are
		 * already subscribed" turns the form into a tool for checking whether
		 * an address is on the list, which is an information leak about other
		 * people's subscriptions.
		 */
		promptatlas_set_signup_state( 'success', __( 'Almost there — check your inbox for a confirmation link.', 'promptatlas' ) );
		return;
	}

	if ( false === $result ) {
		promptatlas_set_signup_state( 'error', __( 'Something went wrong. Please try again shortly.', 'promptatlas' ) );
		return;
	}

	promptatlas_send_confirmation_email( $email, $result );

	promptatlas_set_signup_state( 'success', __( 'Almost there — check your inbox for a confirmation link.', 'promptatlas' ) );
}
add_action( 'template_redirect', 'promptatlas_handle_signup' );

/**
 * Add or refresh a pending subscriber.
 *
 * @param string $email The address.
 * @return string|false The confirmation token, 'already_confirmed', or false on failure.
 */
function promptatlas_add_subscriber( $email ) {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Plugin-owned table with no WordPress API.
	$existing = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT id, status FROM {$wpdb->prefix}promptatlas_subscribers WHERE email = %s",
			$email
		)
	);

	if ( $existing && 'confirmed' === $existing->status ) {
		return 'already_confirmed';
	}

	$token = bin2hex( random_bytes( 32 ) );
	$now   = current_time( 'mysql', true );

	if ( $existing ) {
		// A pending or previously unsubscribed address: issue a fresh token
		// rather than creating a duplicate row.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Plugin-owned table.
		$updated = $wpdb->update(
			promptatlas_subscribers_table(),
			array(
				'status'     => 'pending',
				'token'      => $token,
				'created_at' => $now,
			),
			array( 'id' => (int) $existing->id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		return ( false === $updated ) ? false : $token;
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Plugin-owned table.
	$inserted = $wpdb->insert(
		promptatlas_subscribers_table(),
		array(
			'email'      => $email,
			'status'     => 'pending',
			'token'      => $token,
			'created_at' => $now,
		),
		array( '%s', '%s', '%s', '%s' )
	);

	return ( false === $inserted ) ? false : $token;
}

/**
 * Send the double opt-in confirmation email.
 *
 * @param string $email The address.
 * @param string $token The confirmation token.
 * @return bool Whether wp_mail() reported success.
 */
function promptatlas_send_confirmation_email( $email, $token ) {
	$confirm = add_query_arg( 'pa_confirm', $token, home_url( '/' ) );

	$body = implode(
		"\n",
		array(
			__( 'Thanks for signing up.', 'promptatlas' ),
			'',
			__( 'Click this link to confirm, and we will send you new prompts about once a month:', 'promptatlas' ),
			$confirm,
			'',
			__( 'If you did not sign up, ignore this email. Nothing happens unless you click the link, and we will delete the address shortly.', 'promptatlas' ),
		)
	);

	return (bool) wp_mail(
		$email,
		sprintf(
			/* translators: %s: site name. */
			__( 'Confirm your %s subscription', 'promptatlas' ),
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
		),
		$body,
		array( 'Content-Type: text/plain; charset=UTF-8' )
	);
}

/**
 * Handle confirm and unsubscribe links.
 *
 * @return void
 */
function promptatlas_handle_newsletter_links() {
	global $wpdb;

	$confirm     = get_query_var( 'pa_confirm' );
	$unsubscribe = get_query_var( 'pa_unsubscribe' );

	if ( ! $confirm && ! $unsubscribe ) {
		return;
	}

	$token = sanitize_text_field( (string) ( $confirm ? $confirm : $unsubscribe ) );

	// A token is 64 hex characters. Anything else is not worth a query.
	if ( ! preg_match( '/^[a-f0-9]{64}$/', $token ) ) {
		promptatlas_set_signup_state( 'error', __( 'That link is not valid. It may have already been used.', 'promptatlas' ) );
		return;
	}

	if ( $confirm ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Plugin-owned table.
		$rows = $wpdb->update(
			promptatlas_subscribers_table(),
			array(
				'status'       => 'confirmed',
				'confirmed_at' => current_time( 'mysql', true ),
			),
			array(
				'token'  => $token,
				'status' => 'pending',
			),
			array( '%s', '%s' ),
			array( '%s', '%s' )
		);

		if ( $rows ) {
			promptatlas_set_signup_state( 'success', __( 'Confirmed. You will get an email when there are new prompts worth reading.', 'promptatlas' ) );
		} else {
			promptatlas_set_signup_state( 'error', __( 'That confirmation link has already been used, or has expired.', 'promptatlas' ) );
		}

		return;
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Plugin-owned table.
	$rows = $wpdb->update(
		promptatlas_subscribers_table(),
		array( 'status' => 'unsubscribed' ),
		array( 'token' => $token ),
		array( '%s' ),
		array( '%s' )
	);

	if ( $rows ) {
		promptatlas_set_signup_state( 'success', __( 'You have been unsubscribed. We will not email you again.', 'promptatlas' ) );
	} else {
		promptatlas_set_signup_state( 'error', __( 'That link is not valid. It may have already been used.', 'promptatlas' ) );
	}
}
add_action( 'template_redirect', 'promptatlas_handle_newsletter_links', 5 );

/**
 * Stash the signup result for the template.
 *
 * @param string $type    Either 'success' or 'error'.
 * @param string $message The message.
 * @return void
 */
function promptatlas_set_signup_state( $type, $message ) {
	$GLOBALS['promptatlas_signup_state'] = array(
		'type'    => $type,
		'message' => $message,
	);
}

/**
 * Get the signup result.
 *
 * @return array{type: string, message: string}|null State, or null.
 */
function promptatlas_get_signup_state() {
	return isset( $GLOBALS['promptatlas_signup_state'] ) ? $GLOBALS['promptatlas_signup_state'] : null;
}

/**
 * Delete pending signups that were never confirmed.
 *
 * An address that never confirmed never consented, so it is not kept. Seven
 * days is long enough for someone to find the email.
 *
 * @return void
 */
function promptatlas_prune_subscribers() {
	global $wpdb;

	$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( 7 * DAY_IN_SECONDS ) );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Scheduled maintenance on a plugin-owned table.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}promptatlas_subscribers WHERE status = 'pending' AND created_at < %s",
			$cutoff
		)
	);
}
add_action( 'promptatlas_prune_subscribers', 'promptatlas_prune_subscribers' );

/**
 * Schedule the pending-signup prune.
 *
 * @return void
 */
function promptatlas_schedule_subscriber_prune() {
	if ( ! wp_next_scheduled( 'promptatlas_prune_subscribers' ) ) {
		wp_schedule_event( time() + ( 3 * HOUR_IN_SECONDS ), 'daily', 'promptatlas_prune_subscribers' );
	}
}

/**
 * Clear the pending-signup prune schedule.
 *
 * @return void
 */
function promptatlas_unschedule_subscriber_prune() {
	$timestamp = wp_next_scheduled( 'promptatlas_prune_subscribers' );

	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'promptatlas_prune_subscribers' );
	}
}

/**
 * Get subscriber counts by status.
 *
 * @return array<string, int> Counts keyed by status.
 */
function promptatlas_subscriber_counts() {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin reporting on a plugin-owned table.
	$rows = $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$wpdb->prefix}promptatlas_subscribers GROUP BY status" );

	$counts = array(
		'confirmed'    => 0,
		'pending'      => 0,
		'unsubscribed' => 0,
	);

	foreach ( (array) $rows as $row ) {
		$counts[ $row->status ] = (int) $row->total;
	}

	return $counts;
}

/**
 * Register the subscribers admin screen.
 *
 * @return void
 */
function promptatlas_register_subscribers_page() {
	add_submenu_page(
		'edit.php?post_type=prompt',
		__( 'Subscribers', 'promptatlas' ),
		__( 'Subscribers', 'promptatlas' ),
		'manage_options',
		'promptatlas-subscribers',
		'promptatlas_render_subscribers_page'
	);
}
add_action( 'admin_menu', 'promptatlas_register_subscribers_page' );

/**
 * Render the subscribers screen.
 *
 * @return void
 */
function promptatlas_render_subscribers_page() {
	global $wpdb;

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to view this page.', 'promptatlas' ) );
	}

	$counts = promptatlas_subscriber_counts();

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin reporting on a plugin-owned table.
	$rows = $wpdb->get_results( "SELECT email, status, created_at, confirmed_at FROM {$wpdb->prefix}promptatlas_subscribers ORDER BY created_at DESC LIMIT 200" );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Subscribers', 'promptatlas' ); ?></h1>

		<p>
			<strong><?php echo esc_html( number_format_i18n( $counts['confirmed'] ) ); ?></strong>
			<?php esc_html_e( 'confirmed', 'promptatlas' ); ?>
			&middot;
			<?php echo esc_html( number_format_i18n( $counts['pending'] ) ); ?>
			<?php esc_html_e( 'awaiting confirmation', 'promptatlas' ); ?>
			&middot;
			<?php echo esc_html( number_format_i18n( $counts['unsubscribed'] ) ); ?>
			<?php esc_html_e( 'unsubscribed', 'promptatlas' ); ?>
		</p>

		<p class="description" style="max-width: 46em;">
			<?php esc_html_e( 'Only confirmed addresses are a list. Pending addresses have not clicked the confirmation link and are deleted automatically after 7 days — an address that never confirmed never consented.', 'promptatlas' ); ?>
		</p>

		<?php if ( empty( $rows ) ) : ?>
			<div class="notice notice-info inline" style="margin-top:16px;">
				<p><?php esc_html_e( 'No signups yet.', 'promptatlas' ); ?></p>
			</div>
		<?php else : ?>
			<table class="widefat striped" style="margin-top:16px; max-width:46em;">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Email', 'promptatlas' ); ?></th>
						<th scope="col" style="width:140px;"><?php esc_html_e( 'Status', 'promptatlas' ); ?></th>
						<th scope="col" style="width:160px;"><?php esc_html_e( 'Signed up', 'promptatlas' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<td><?php echo esc_html( $row->email ); ?></td>
							<td><?php echo esc_html( $row->status ); ?></td>
							<td>
								<?php
								echo esc_html(
									date_i18n( (string) get_option( 'date_format' ), (int) strtotime( (string) $row->created_at ) )
								);
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}
