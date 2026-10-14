<?php
/**
 * Read-only REST endpoint for resolving stored prompt IDs.
 *
 * Phase 10 keeps Recently Copied and Saved entirely in the visitor's browser.
 * That creates one problem the browser cannot solve on its own: a stored ID may
 * point at a prompt that has since been unpublished, deleted or renamed. The
 * requirement is that such an entry disappears cleanly rather than rendering a
 * dead link, which means the browser has to ask the server what is still valid.
 *
 * This endpoint answers exactly that and nothing more.
 *
 * Why no nonce and no authentication: it returns only data that is already
 * public on the frontend - the title, URL and category of published prompts.
 * A nonce would add nothing, and requiring one would break the page for a
 * visitor whose cached page outlived the nonce lifetime.
 *
 * It accepts nothing but integers, returns nothing but published prompts, and
 * caps the batch size, so it cannot be used to enumerate drafts or to make the
 * server do expensive work.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Maximum IDs resolved in one request.
 *
 * Comfortably above the stored history limit, and bounded so a crafted request
 * cannot ask for thousands.
 */
const PROMPTATLAS_REST_MAX_IDS = 60;

/**
 * Register the REST route.
 *
 * @return void
 */
function promptatlas_register_rest_routes() {
	register_rest_route(
		'promptatlas/v1',
		'/prompts',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'promptatlas_rest_resolve_prompts',
			'permission_callback' => '__return_true',
			'args'                => array(
				'ids' => array(
					'required'          => true,
					'type'              => 'string',
					'description'       => __( 'Comma-separated prompt IDs.', 'promptatlas' ),
					'validate_callback' => static function ( $value ) {
						return is_string( $value ) && '' !== trim( $value ) && (bool) preg_match( '/^[0-9,\s]+$/', $value );
					},
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'promptatlas_register_rest_routes' );

/**
 * Resolve prompt IDs to their public data.
 *
 * Returns only published prompts. An ID that is unknown, draft, trashed or of
 * another post type is simply absent from the response, which is what lets the
 * browser prune it from storage.
 *
 * @param WP_REST_Request $request The request.
 * @return WP_REST_Response Resolved prompts.
 */
function promptatlas_rest_resolve_prompts( $request ) {
	$raw = (string) $request->get_param( 'ids' );

	$ids = array();

	foreach ( preg_split( '/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY ) as $candidate ) {
		$id = absint( $candidate );

		if ( 0 === $id || in_array( $id, $ids, true ) ) {
			continue;
		}

		$ids[] = $id;

		if ( count( $ids ) >= PROMPTATLAS_REST_MAX_IDS ) {
			break;
		}
	}

	if ( empty( $ids ) ) {
		return rest_ensure_response( array( 'prompts' => array() ) );
	}

	$posts = get_posts(
		array(
			'post_type'              => 'prompt',
			'post_status'            => 'publish',
			'post__in'               => $ids,
			'posts_per_page'         => count( $ids ),
			'orderby'                => 'post__in',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
		)
	);

	$results = array();

	foreach ( $posts as $post ) {
		$category = promptatlas_get_prompt_category( $post->ID );

		/*
		 * Entities are decoded on the way out. WordPress stores "Career & Job
		 * Search" with an encoded ampersand, and the client renders these with
		 * textContent - correctly, since they must never be parsed as markup -
		 * which would print the literal "&amp;". Decoding here means the client
		 * receives real text and stays safe by construction.
		 */
		$results[] = array(
			'id'       => (int) $post->ID,
			'title'    => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'url'      => (string) get_permalink( $post ),
			'category' => $category instanceof WP_Term
				? html_entity_decode( $category->name, ENT_QUOTES, 'UTF-8' )
				: '',
		);
	}

	$response = rest_ensure_response( array( 'prompts' => $results ) );

	// Public, derived data. A short cache keeps a visitor opening their list
	// repeatedly from hitting the database every time.
	$response->header( 'Cache-Control', 'public, max-age=300' );

	return $response;
}
