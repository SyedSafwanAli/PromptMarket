<?php
/**
 * Outgoing mail configuration.
 *
 * ---------------------------------------------------------------------------
 * DECISION: no SMTP plugin, and no credentials anywhere in this repository
 * ---------------------------------------------------------------------------
 * WordPress sends through PHP's mail transport by default. On a shared host
 * that is unreliable and routinely lands in spam, which matters here because
 * the contact form carries DMCA notices and the newsletter carries confirmation
 * links that are useless if they do not arrive.
 *
 * Recommended for production, in order:
 *
 *   1. SMTP through a transactional provider on the real domain, with SPF,
 *      DKIM and DMARC configured. Free tiers from the usual providers cover
 *      this site's volume many times over, so this costs nothing against the
 *      decision 9 budget.
 *   2. The host's own SMTP, if the host offers authenticated SMTP.
 *   3. PHP mail(). Works, often arrives in spam, no deliverability reporting.
 *      Acceptable only as a stopgap.
 *
 * Credentials belong in `wp-config.php` as constants, injected by the host or
 * the deploy process. `wp-config.php` is git-ignored. They must never appear in
 * the theme, the plugin, a database option, or any JavaScript. This file reads
 * them if they are defined and does nothing if they are not — so configuring
 * SMTP is a deployment step, not a code change.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Set the From address to something valid on the site's own domain.
 *
 * The default is `wordpress@<host>`, which on this local install is
 * `wordpress@localhost` - an address with no TLD, which PHPMailer rejects
 * outright. That is the real reason local delivery fails, confirmed by the
 * error `Invalid address: (From): wordpress@localhost`.
 *
 * On a real domain the default would be valid, but it would still be the wrong
 * address to send from. Sending as the visitor's own address instead is the
 * fastest way to be rejected by SPF and DMARC, so the From stays on this
 * domain and the visitor goes in Reply-To.
 *
 * @param string $from The default From address.
 * @return string The address to send from.
 */
function promptatlas_mail_from( $from ) {
	$host = wp_parse_url( home_url(), PHP_URL_HOST );

	if ( ! is_string( $host ) || '' === $host ) {
		return $from;
	}

	$host = preg_replace( '/^www\./i', '', $host );

	// An address with no dot in the host is not deliverable anywhere, so leave
	// the default rather than producing a different invalid address.
	if ( ! str_contains( (string) $host, '.' ) ) {
		return $from;
	}

	return 'noreply@' . $host;
}
add_filter( 'wp_mail_from', 'promptatlas_mail_from' );

/**
 * Set the From name to the site name.
 *
 * @param string $name The default From name.
 * @return string The name to send as.
 */
function promptatlas_mail_from_name( $name ) {
	unset( $name );

	return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
}
add_filter( 'wp_mail_from_name', 'promptatlas_mail_from_name' );

/**
 * Configure SMTP when credentials are defined in wp-config.php.
 *
 * Does nothing unless PROMPTATLAS_SMTP_HOST is defined, so the absence of
 * credentials is a no-op rather than an error. Define these in wp-config.php:
 *
 *   define( 'PROMPTATLAS_SMTP_HOST', 'smtp.provider.com' );
 *   define( 'PROMPTATLAS_SMTP_PORT', 587 );
 *   define( 'PROMPTATLAS_SMTP_USER', '...' );
 *   define( 'PROMPTATLAS_SMTP_PASS', '...' );
 *   define( 'PROMPTATLAS_SMTP_SECURE', 'tls' );
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer The mailer instance.
 * @return void
 */
function promptatlas_configure_smtp( $phpmailer ) {
	if ( ! defined( 'PROMPTATLAS_SMTP_HOST' ) || '' === PROMPTATLAS_SMTP_HOST ) {
		return;
	}

	$phpmailer->isSMTP();
	$phpmailer->Host = PROMPTATLAS_SMTP_HOST; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer's public API.
	$phpmailer->Port = defined( 'PROMPTATLAS_SMTP_PORT' ) ? (int) PROMPTATLAS_SMTP_PORT : 587; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer's public API.

	if ( defined( 'PROMPTATLAS_SMTP_SECURE' ) && PROMPTATLAS_SMTP_SECURE ) {
		$phpmailer->SMTPSecure = PROMPTATLAS_SMTP_SECURE; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer's public API.
	}

	if ( defined( 'PROMPTATLAS_SMTP_USER' ) && PROMPTATLAS_SMTP_USER ) {
		$phpmailer->SMTPAuth = true; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer's public API.
		$phpmailer->Username = PROMPTATLAS_SMTP_USER; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer's public API.
		$phpmailer->Password = defined( 'PROMPTATLAS_SMTP_PASS' ) ? PROMPTATLAS_SMTP_PASS : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer's public API.
	}
}
add_action( 'phpmailer_init', 'promptatlas_configure_smtp' );

/**
 * Record the last mail failure so the admin can see it.
 *
 * Without this, a failing mail server is invisible until someone notices they
 * stopped receiving contact messages. Stores only the error text and a
 * timestamp - no recipient, no message body.
 *
 * @param WP_Error $error The mail error.
 * @return void
 */
function promptatlas_record_mail_failure( $error ) {
	update_option(
		'promptatlas_last_mail_error',
		array(
			'message' => $error->get_error_message(),
			'time'    => time(),
		),
		false
	);
}
add_action( 'wp_mail_failed', 'promptatlas_record_mail_failure' );

/**
 * Warn in the admin when mail has recently failed.
 *
 * @return void
 */
function promptatlas_mail_failure_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$error = get_option( 'promptatlas_last_mail_error' );

	if ( ! is_array( $error ) || empty( $error['message'] ) ) {
		return;
	}

	// Only surface recent failures; an old one that has since been fixed is
	// noise.
	if ( isset( $error['time'] ) && ( time() - (int) $error['time'] ) > WEEK_IN_SECONDS ) {
		return;
	}

	?>
	<div class="notice notice-warning">
		<p>
			<strong><?php esc_html_e( 'PromptAtlas: email delivery is failing.', 'promptatlas' ); ?></strong>
			<?php esc_html_e( 'Contact form messages are still being saved under Prompts → Messages, but no notification is reaching you.', 'promptatlas' ); ?>
		</p>
		<p><code><?php echo esc_html( (string) $error['message'] ); ?></code></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'promptatlas_mail_failure_notice' );
