<?php
/**
 * Zero-result search logging.
 *
 * CONTENT_STRATEGY.md 6.5 calls this the most reliable input to the editorial
 * calendar there is: a repeated query that returns nothing is a content gap
 * stated in the visitor's own words.
 *
 * ---------------------------------------------------------------------------
 * PRIVACY POSTURE - this is the whole design constraint
 * ---------------------------------------------------------------------------
 * Stored, per row:
 *   - the normalised query string
 *   - how many times it has been searched
 *   - when it was last searched (date only matters for pruning)
 *
 * NOT stored, ever: IP address, user agent, session or user identifier,
 * referrer, timestamp of any individual search, or anything that could link two
 * searches to the same person. There is deliberately no column that could hold
 * one, so a future change cannot quietly start collecting it.
 *
 * Because rows are aggregated by query text, a row cannot be traced back to a
 * visitor. Two people searching the same thing produce one row with a count of
 * two, and nothing distinguishes them.
 *
 * Retention: 180 days since last seen, pruned daily. Table capped at 500 rows,
 * dropping the least recently seen first. Both are enforced in code below, not
 * merely documented.
 *
 * This description is what the Phase 17 privacy policy must say. If this file
 * changes, that policy changes.
 * ---------------------------------------------------------------------------
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Schema version, bumped when the table definition changes.
 */
const PROMPTATLAS_SEARCH_LOG_VERSION = '1.0.0';

/**
 * Maximum rows kept in the log.
 */
const PROMPTATLAS_SEARCH_LOG_MAX_ROWS = 500;

/**
 * Days a row is kept after it was last searched.
 */
const PROMPTATLAS_SEARCH_LOG_RETENTION_DAYS = 180;

/**
 * Get the log table name.
 *
 * @return string Prefixed table name.
 */
function promptatlas_search_log_table() {
	global $wpdb;

	return $wpdb->prefix . 'promptatlas_search_log';
}

/**
 * Create or update the log table.
 *
 * Called on activation. The UNIQUE key on `query_text` is what makes the
 * aggregate-only design enforceable at the database level rather than by
 * convention.
 *
 * @return void
 */
function promptatlas_create_search_log_table() {
	global $wpdb;

	$table   = promptatlas_search_log_table();
	$collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		query_text varchar(120) NOT NULL DEFAULT '',
		hits bigint(20) unsigned NOT NULL DEFAULT 1,
		last_seen datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY  (id),
		UNIQUE KEY query_text (query_text),
		KEY hits (hits),
		KEY last_seen (last_seen)
	) {$collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	update_option( 'promptatlas_search_log_version', PROMPTATLAS_SEARCH_LOG_VERSION );
}

/**
 * Record a search that returned nothing.
 *
 * Runs on the search results template only, after the query has run.
 *
 * @param string $raw Raw search query.
 * @return void
 */
function promptatlas_log_zero_result( $raw ) {
	global $wpdb;

	if ( ! is_string( $raw ) ) {
		return;
	}

	$query_text = promptatlas_normalise_search_term( $raw );

	/*
	 * Guards against logging noise rather than signal:
	 *  - too short to mean anything
	 *  - too long to be a human query, and would be truncated anyway
	 *  - contains a URL or SQL-ish payload, which is a scanner, not a visitor
	 */
	if ( strlen( $query_text ) < 2 || strlen( $query_text ) > 120 ) {
		return;
	}

	if ( preg_match( '/(https?|www\.|select\s|union\s|script)/i', $query_text ) ) {
		return;
	}

	$now = current_time( 'mysql', true );

	/*
	 * An upsert in one statement. Doing it as SELECT-then-INSERT would race two
	 * simultaneous searches for the same term and lose a count, and would need
	 * two round trips on a page that is already rendering.
	 */
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Aggregate counter table owned by this plugin; there is no WordPress API for it, and the value is a running count that must not be cached.
	$wpdb->query(
		$wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}promptatlas_search_log ( query_text, hits, last_seen )
			 VALUES ( %s, 1, %s )
			 ON DUPLICATE KEY UPDATE hits = hits + 1, last_seen = VALUES( last_seen )",
			$query_text,
			$now
		)
	);
}

/**
 * Log from the search results page when nothing was found.
 *
 * Hooked on `wp` so the main query has run and found_posts is known. Bots are
 * not excluded, because excluding them would need a user-agent check and this
 * design stores no user agent; the admin screen notes that a share of entries
 * will be automated traffic.
 *
 * @return void
 */
function promptatlas_maybe_log_zero_result() {
	if ( is_admin() || ! is_search() || ! is_main_query() ) {
		return;
	}

	if ( 0 !== (int) get_query_var( 'paged' ) && 1 !== (int) get_query_var( 'paged' ) ) {
		return;
	}

	if ( isset( $GLOBALS['wp_query'] ) && 0 !== (int) $GLOBALS['wp_query']->found_posts ) {
		return;
	}

	$raw = get_search_query( false );

	if ( '' === trim( (string) $raw ) ) {
		return;
	}

	promptatlas_log_zero_result( (string) $raw );
}
add_action( 'wp', 'promptatlas_maybe_log_zero_result' );

/**
 * Prune the log: enforce retention, then the row cap.
 *
 * @return void
 */
function promptatlas_prune_search_log() {
	global $wpdb;

	$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( PROMPTATLAS_SEARCH_LOG_RETENTION_DAYS * DAY_IN_SECONDS ) );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Scheduled maintenance on a plugin-owned table.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}promptatlas_search_log WHERE last_seen < %s", $cutoff ) );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Scheduled maintenance on a plugin-owned table.
	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}promptatlas_search_log" );

	if ( $total <= PROMPTATLAS_SEARCH_LOG_MAX_ROWS ) {
		return;
	}

	$excess = $total - PROMPTATLAS_SEARCH_LOG_MAX_ROWS;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Scheduled maintenance on a plugin-owned table.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}promptatlas_search_log ORDER BY last_seen ASC, hits ASC LIMIT %d",
			$excess
		)
	);
}
add_action( 'promptatlas_prune_search_log', 'promptatlas_prune_search_log' );

/**
 * Schedule the daily prune.
 *
 * @return void
 */
function promptatlas_schedule_search_log_prune() {
	if ( ! wp_next_scheduled( 'promptatlas_prune_search_log' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'promptatlas_prune_search_log' );
	}
}

/**
 * Clear the scheduled prune on deactivation.
 *
 * @return void
 */
function promptatlas_unschedule_search_log_prune() {
	$timestamp = wp_next_scheduled( 'promptatlas_prune_search_log' );

	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'promptatlas_prune_search_log' );
	}
}

/**
 * Get the top zero-result queries.
 *
 * @param int $limit Maximum rows.
 * @return array<int, object> Rows with query_text, hits and last_seen.
 */
function promptatlas_get_search_gaps( $limit = 100 ) {
	global $wpdb;

	$limit = max( 1, min( 500, (int) $limit ) );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin reporting screen on a plugin-owned table.
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT query_text, hits, last_seen FROM {$wpdb->prefix}promptatlas_search_log ORDER BY hits DESC, last_seen DESC LIMIT %d",
			$limit
		)
	);

	return is_array( $rows ) ? $rows : array();
}

/**
 * Register the search gaps admin screen.
 *
 * @return void
 */
function promptatlas_register_search_gaps_page() {
	add_submenu_page(
		'edit.php?post_type=prompt',
		__( 'Search gaps', 'promptatlas' ),
		__( 'Search gaps', 'promptatlas' ),
		'edit_posts',
		'promptatlas-search-gaps',
		'promptatlas_render_search_gaps_page'
	);
}
add_action( 'admin_menu', 'promptatlas_register_search_gaps_page' );

/**
 * Render the search gaps screen.
 *
 * @return void
 */
function promptatlas_render_search_gaps_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'You do not have permission to view this page.', 'promptatlas' ) );
	}

	$rows = promptatlas_get_search_gaps( 100 );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Search gaps', 'promptatlas' ); ?></h1>

		<p class="description" style="max-width: 46em;">
			<?php
			esc_html_e(
				'What visitors searched for and found nothing. This is the clearest signal there is about what to write next — it is the gap in your own visitors\' words.',
				'promptatlas'
			);
			?>
		</p>

		<p class="description" style="max-width: 46em;">
			<strong><?php esc_html_e( 'Privacy:', 'promptatlas' ); ?></strong>
			<?php
			esc_html_e(
				'Only the search text and a count are stored. No IP address, no user agent, no session or visitor identifier — the table has no column that could hold one. Rows are deleted after 180 days, and the table is capped at 500 rows.',
				'promptatlas'
			);
			?>
		</p>

		<p class="description" style="max-width: 46em;">
			<em>
				<?php
				esc_html_e(
					'Expect some automated traffic in this list. Since nothing identifying is recorded, bots cannot be filtered out — judge an entry by whether a real person would plausibly search it.',
					'promptatlas'
				);
				?>
			</em>
		</p>

		<?php if ( empty( $rows ) ) : ?>
			<div class="notice notice-info inline" style="margin-top: 16px;">
				<p>
					<?php esc_html_e( 'Nothing logged yet. Entries appear here when a visitor searches for something and gets no results.', 'promptatlas' ); ?>
				</p>
			</div>
		<?php else : ?>
			<table class="widefat striped" style="margin-top: 16px; max-width: 46em;">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Search', 'promptatlas' ); ?></th>
						<th scope="col" style="width: 90px;"><?php esc_html_e( 'Times', 'promptatlas' ); ?></th>
						<th scope="col" style="width: 160px;"><?php esc_html_e( 'Last searched', 'promptatlas' ); ?></th>
						<th scope="col" style="width: 110px;"><?php esc_html_e( 'Action', 'promptatlas' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $row->query_text ); ?></strong></td>
							<td><?php echo esc_html( number_format_i18n( (int) $row->hits ) ); ?></td>
							<td>
								<?php
								echo esc_html(
									date_i18n(
										(string) get_option( 'date_format' ),
										(int) strtotime( (string) $row->last_seen )
									)
								);
								?>
							</td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=prompt' ) ); ?>">
									<?php esc_html_e( 'Write it', 'promptatlas' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}
