<?php
/**
 * PromptAtlas — rewrite route regression check.
 *
 * Run with WP-CLI, which loads WordPress for us:
 *
 *   C:\xampp\php\php.exe C:\xampp\wp-cli.phar eval-file tools/check-routes.php
 *
 * ---------------------------------------------------------------------------
 * Why this exists
 * ---------------------------------------------------------------------------
 * The `prompt` post type generates an attachment catch-all rewrite rule,
 * `prompts/[^/]+/([^/]+)/?$`, which matches ANY two segments under /prompts/.
 * If it sorts above the taxonomy rules, every category, tool and tag archive
 * silently 404s — the pages still exist, the terms still exist, and nothing
 * errors. It just stops working.
 *
 * That has now happened twice. Phase 05 fixed the `init` priorities, and
 * Phase 11 found it had come back, because the activation hook registers the
 * post types and taxonomies directly, in its own order, and then flushes. The
 * priorities did not cover that path.
 *
 * A comment was not enough to stop it recurring, so this check exists. It
 * resolves each frozen URL against the stored rewrite rules in order and
 * asserts which rule wins — the same thing WordPress does on a real request.
 *
 * Exit code 0 when every route resolves correctly, 1 otherwise.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 'This script must be run through WP-CLI.' );
}

/**
 * The frozen URL architecture from DECISIONS.md decision 13, and the query
 * variable each path must resolve to.
 *
 * Paths are relative to the site root and carry no leading slash, matching the
 * form WordPress compares rewrite rules against.
 */
$promptatlas_routes = array(
	'prompts/'                            => 'post_type=prompt',
	'prompts/some-prompt-slug/'           => 'prompt=',
	'prompts/category/career-job-search/' => 'prompt_category=',
	'prompts/tool/chatgpt/'               => 'prompt_tool=',
	'prompts/tag/resume/'                 => 'prompt_tag=',
	'collections/'                        => 'post_type=collection',
	'collections/some-collection/'        => 'collection=',
);

$promptatlas_rules = get_option( 'rewrite_rules' );

if ( ! is_array( $promptatlas_rules ) || empty( $promptatlas_rules ) ) {
	WP_CLI::error( 'No rewrite rules stored. Flush permalinks and try again.' );
}

$promptatlas_failures = 0;

WP_CLI::log( 'PromptAtlas route check' );
WP_CLI::log( str_repeat( '-', 72 ) );

foreach ( $promptatlas_routes as $promptatlas_path => $promptatlas_expected ) {

	$promptatlas_matched_rule  = '';
	$promptatlas_matched_query = '';

	foreach ( $promptatlas_rules as $promptatlas_pattern => $promptatlas_query ) {
		if ( preg_match( '#^' . $promptatlas_pattern . '#', $promptatlas_path ) ) {
			$promptatlas_matched_rule  = $promptatlas_pattern;
			$promptatlas_matched_query = $promptatlas_query;
			break;
		}
	}

	if ( '' === $promptatlas_matched_query ) {
		WP_CLI::log( sprintf( '  FAIL  %-38s no rule matched', $promptatlas_path ) );
		++$promptatlas_failures;
		continue;
	}

	if ( ! str_contains( $promptatlas_matched_query, $promptatlas_expected ) ) {
		WP_CLI::log( sprintf( '  FAIL  %-38s expected %s', $promptatlas_path, $promptatlas_expected ) );
		WP_CLI::log( sprintf( '        %-38s got      %s', '', $promptatlas_matched_query ) );
		WP_CLI::log( sprintf( '        %-38s via rule %s', '', $promptatlas_matched_rule ) );
		++$promptatlas_failures;
		continue;
	}

	WP_CLI::log( sprintf( '  OK    %-38s %s', $promptatlas_path, $promptatlas_expected ) );
}

WP_CLI::log( str_repeat( '-', 72 ) );

if ( $promptatlas_failures > 0 ) {
	WP_CLI::error(
		sprintf(
			'%d of %d routes resolve incorrectly. The usual cause is the prompt post type being registered before the taxonomies, which lets its attachment catch-all rule shadow them. Check the order in promptatlas_activate() and the init priorities.',
			$promptatlas_failures,
			count( $promptatlas_routes )
		)
	);
}

WP_CLI::success( sprintf( 'All %d routes resolve correctly.', count( $promptatlas_routes ) ) );
