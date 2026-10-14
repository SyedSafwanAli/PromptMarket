<?php
/**
 * Enforce the comments-off decision.
 *
 * DECISIONS.md decision 6 turns comments off everywhere. Phase 02 set the
 * default status to closed and Phase 05 registered the post types without
 * comment support, but neither of those closes comments on content that
 * already exists — the "Hello world!" post still has them open, and so would
 * anything imported later.
 *
 * This file makes the decision true at runtime rather than by configuration,
 * so there is no state in which a comment form can be reached.
 *
 * Phase 14 may additionally tidy the admin menus; this is the frontend
 * guarantee.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Report comments as closed for every post type.
 *
 * @param bool $open    Whether comments are open.
 * @param int  $post_id Post ID.
 * @return bool Always false.
 */
function promptatlas_close_comments( $open, $post_id ) {
	unset( $open, $post_id );

	return false;
}
add_filter( 'comments_open', 'promptatlas_close_comments', 20, 2 );
add_filter( 'pings_open', 'promptatlas_close_comments', 20, 2 );

/**
 * Report zero existing comments.
 *
 * Without this, a post with historic comments would still render "2 Comments"
 * even though the form is closed.
 *
 * @param int $count   Comment count.
 * @param int $post_id Post ID.
 * @return int Always zero.
 */
function promptatlas_zero_comment_count( $count, $post_id ) {
	unset( $count, $post_id );

	return 0;
}
add_filter( 'get_comments_number', 'promptatlas_zero_comment_count', 20, 2 );

/*
 * Remove the comments feed link from the document head.
 *
 * This needs the dedicated filter, not an action removal. The comments feed
 * <link> is printed by core's `feed_links()`, which also prints the main posts
 * feed — so removing that action would take the useful feed with it.
 * `feed_links_extra()` is a different function, printing the per-post and
 * per-term feeds, and removing it does not touch the comments feed at all.
 *
 * Verified by inspecting the rendered <head>: the comments feed link is gone
 * and the posts feed link remains.
 */
add_filter( 'feed_links_show_comments_feed', '__return_false' );

/**
 * Remove the per-post and per-term feed links.
 *
 * Head clutter for feeds nobody subscribes to on a site of this kind. The main
 * site feed, printed by feed_links(), is kept.
 *
 * @return void
 */
function promptatlas_remove_extra_feeds() {
	remove_action( 'wp_head', 'feed_links_extra', 3 );
}
add_action( 'init', 'promptatlas_remove_extra_feeds' );
