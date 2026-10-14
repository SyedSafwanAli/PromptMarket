<?php
/**
 * PromptAtlas theme bootstrap.
 *
 * The theme owns presentation only. Post types, taxonomies, meta and content
 * relationships live in the promptatlas-core plugin so they survive a theme
 * change. See CLAUDE.md section 3 for the rule.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme version, used to cache-bust enqueued assets.
 */
define( 'PROMPTATLAS_THEME_VERSION', '1.0.0' );

/**
 * Absolute path to the theme directory, with a trailing slash.
 */
define( 'PROMPTATLAS_THEME_DIR', trailingslashit( get_template_directory() ) );

/**
 * URL of the theme directory, with a trailing slash.
 */
define( 'PROMPTATLAS_THEME_URI', trailingslashit( get_template_directory_uri() ) );

require_once PROMPTATLAS_THEME_DIR . 'inc/setup.php';
require_once PROMPTATLAS_THEME_DIR . 'inc/assets.php';
require_once PROMPTATLAS_THEME_DIR . 'inc/template-tags.php';
require_once PROMPTATLAS_THEME_DIR . 'inc/comments-off.php';
