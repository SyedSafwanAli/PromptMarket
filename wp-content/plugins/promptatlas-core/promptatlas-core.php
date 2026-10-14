<?php
/**
 * Plugin Name:       PromptAtlas Core
 * Plugin URI:        https://github.com/SyedSafwanAli/PromptMarket
 * Description:       Content model and site functionality for PromptAtlas: the prompt and collection post types, their taxonomies, and their metadata. Lives in a plugin rather than the theme so that content and URLs survive a theme change.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            PromptAtlas
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       promptatlas
 * Domain Path:       /languages
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin version. Bump on every release; used to cache-bust and to detect the
 * need for a rewrite flush after an update.
 */
define( 'PROMPTATLAS_CORE_VERSION', '1.0.0' );

/**
 * Absolute path to this plugin's main file.
 */
define( 'PROMPTATLAS_CORE_FILE', __FILE__ );

/**
 * Absolute path to this plugin's directory, with a trailing slash.
 */
define( 'PROMPTATLAS_CORE_DIR', plugin_dir_path( __FILE__ ) );

require_once PROMPTATLAS_CORE_DIR . 'includes/post-types.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/taxonomies.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/meta.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/reserved-slugs.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/terms-seed.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/collection-sync.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/admin-meta-boxes.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/queries.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/search-index.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/search-aliases.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/search.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/search-log.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/rest.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/seo.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/mail.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/contact.php';
require_once PROMPTATLAS_CORE_DIR . 'includes/newsletter.php';

/**
 * Load the plugin text domain.
 *
 * Content is English only, but every string is translation-ready per
 * DECISIONS.md decision 3, so the text domain is loaded properly.
 *
 * @return void
 */
function promptatlas_load_textdomain() {
	load_plugin_textdomain( 'promptatlas', false, dirname( plugin_basename( PROMPTATLAS_CORE_FILE ) ) . '/languages' );
}
add_action( 'init', 'promptatlas_load_textdomain' );

/**
 * Run on plugin activation.
 *
 * Registration must happen before the flush, otherwise the rewrite rules for
 * the post types and taxonomies do not exist yet and the flush writes nothing.
 *
 * Rewrite rules are flushed here and nowhere else. Flushing on every request is
 * a well-known performance mistake: it rebuilds and re-saves every rule in the
 * site on each page load.
 *
 * @return void
 */
function promptatlas_activate() {
	/*
	 * ORDER IS LOAD-BEARING: taxonomies before post types.
	 *
	 * The same ordering requirement as the init priorities in taxonomies.php
	 * (5) and post-types.php (6), and for the same reason: the `prompt` post
	 * type generates an attachment catch-all rule, prompts/[^/]+/([^/]+)/?$,
	 * which matches any two segments under /prompts/ and would otherwise sort
	 * above the taxonomy rules and 404 every category, tool and tag archive.
	 *
	 * Those init priorities do NOT cover this path. Activation calls these
	 * functions directly, in whatever order they appear here, and then flushes
	 * - so getting it right on `init` and wrong here still writes broken rules
	 * to the database. That is exactly what happened: the archives worked,
	 * then silently 404'd again after the next plugin reactivation.
	 */
	promptatlas_register_taxonomies();
	promptatlas_register_post_types();

	/*
	 * The /llms.txt route must be registered here too, for the same reason as
	 * the types above: flush_rewrite_rules() below writes whatever is currently
	 * registered, and anything added only on `init` may not be present during
	 * activation. Without this the route silently vanished after a
	 * reactivation and /llms.txt started returning 404.
	 */
	promptatlas_add_llms_route();

	promptatlas_seed_terms();

	promptatlas_rebuild_all_search_blobs();
	promptatlas_create_search_log_table();
	promptatlas_schedule_search_log_prune();
	promptatlas_schedule_message_prune();
	promptatlas_create_subscribers_table();
	promptatlas_schedule_subscriber_prune();

	// Seed the alias list only on a first install, so an owner who has edited
	// it does not have their edits overwritten on reactivation.
	if ( false === get_option( PROMPTATLAS_OPTION_ALIASES, false ) ) {
		add_option( PROMPTATLAS_OPTION_ALIASES, promptatlas_default_aliases() );
	}

	flush_rewrite_rules();

	update_option( 'promptatlas_core_version', PROMPTATLAS_CORE_VERSION );
}
register_activation_hook( __FILE__, 'promptatlas_activate' );

/**
 * Run on plugin deactivation.
 *
 * Clears the rewrite rules so the /prompts/ and /collections/ routes stop
 * resolving once the plugin is off. Content is deliberately left untouched:
 * deactivating a plugin must never delete the owner's work.
 *
 * @return void
 */
function promptatlas_deactivate() {
	promptatlas_unschedule_search_log_prune();
	promptatlas_unschedule_message_prune();
	promptatlas_unschedule_subscriber_prune();

	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'promptatlas_deactivate' );

/**
 * Flush rewrite rules once after a version change.
 *
 * Covers the case where the plugin files are updated in place — by a Git pull,
 * for example — without deactivating and reactivating. Without this, a change
 * to a rewrite slug would not take effect until permalinks were saved by hand.
 *
 * @return void
 */
function promptatlas_maybe_flush_rewrites() {
	if ( get_option( 'promptatlas_core_version' ) === PROMPTATLAS_CORE_VERSION ) {
		return;
	}

	flush_rewrite_rules();
	update_option( 'promptatlas_core_version', PROMPTATLAS_CORE_VERSION );
}
add_action( 'init', 'promptatlas_maybe_flush_rewrites', 99 );
