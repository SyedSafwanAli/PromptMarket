<?php
/**
 * Search behaviour.
 *
 * WordPress-native, no external search service. Phase 09 forbids adding one
 * without a measured need, and at 40-60 prompts a LIKE-based search over an
 * indexed table is not remotely the bottleneck.
 *
 * What this adds over core WordPress search:
 *
 *   1. The prompt text itself is searched. Core searches title, excerpt and
 *      content only, so a prompt whose instruction says "cold email" but whose
 *      explanation never uses the phrase would otherwise be unfindable.
 *   2. Aliases. "cv" finds "resume"; "naukri" finds job prompts. See
 *      includes/search-aliases.php.
 *   3. Accent and punctuation normalisation, so "resumé" matches "resume".
 *   4. Scope limited to prompts and collections, and stated in the interface.
 *   5. Query length and term count clamps, so a pathological query cannot turn
 *      into an expensive scan.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Maximum characters accepted from a search query.
 *
 * Anything longer is truncated rather than rejected: a visitor pasting a
 * paragraph should get results for the start of it, not an error.
 */
const PROMPTATLAS_SEARCH_MAX_LENGTH = 120;

/**
 * Maximum number of terms used from one query.
 *
 * Each term adds a bracketed OR group to the SQL. Six is generous for real
 * queries and bounds the worst case.
 */
const PROMPTATLAS_SEARCH_MAX_TERMS = 6;

/**
 * Minimum length of a term that is searched on its own.
 *
 * One-character terms match almost everything and add cost for no precision.
 */
const PROMPTATLAS_SEARCH_MIN_TERM_LENGTH = 2;

/**
 * Restrict search to the content types that are worth searching.
 *
 * Decision, as Phase 09 requires it to be deliberate and visible: search covers
 * **prompts and collections**, not pages. Pages are About, Contact and the
 * policy documents — a visitor searching "resume" does not want the privacy
 * policy, and including pages would push real results down. The scope is stated
 * in the interface on the search results page.
 *
 * @param WP_Query $query The query.
 * @return void
 */
function promptatlas_search_scope( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$query->set( 'post_type', array( 'prompt', 'collection' ) );
	$query->set( 'post_status', 'publish' );
}
add_action( 'pre_get_posts', 'promptatlas_search_scope' );

/**
 * Get the normalised terms for the current search, with aliases expanded.
 *
 * Returns a list of groups. Each group is the set of acceptable spellings for
 * one term the visitor typed. A post must match at least one member of EVERY
 * group — AND between terms, OR within a group — which is what makes
 * "resume teacher" narrower than "resume", while still letting "cv teacher"
 * work.
 *
 * @param string $raw Raw search string.
 * @return array<int, string[]> Groups of equivalent terms.
 */
function promptatlas_search_term_groups( $raw ) {
	if ( ! is_string( $raw ) ) {
		return array();
	}

	if ( strlen( $raw ) > PROMPTATLAS_SEARCH_MAX_LENGTH ) {
		$raw = substr( $raw, 0, PROMPTATLAS_SEARCH_MAX_LENGTH );
	}

	$normalised = promptatlas_normalise_search_term( $raw );

	if ( '' === $normalised ) {
		return array();
	}

	$groups = array();

	/*
	 * Individual terms only. An earlier version also added the whole phrase as
	 * a group, which silently broke every multi-word search: because groups are
	 * ANDed, "resume job" then required the literal string "resume job" to
	 * appear somewhere, and a prompt titled "Rewrite a résumé for a specific job
	 * description" returned nothing.
	 *
	 * The phrase is still used, but for ORDERING only - see
	 * promptatlas_search_phrase() and the orderby filter. A phrase match should
	 * rank first, not be a requirement.
	 */
	foreach ( explode( ' ', $normalised ) as $term ) {
		if ( strlen( $term ) < PROMPTATLAS_SEARCH_MIN_TERM_LENGTH ) {
			continue;
		}

		$groups[] = promptatlas_expand_term( $term );

		if ( count( $groups ) >= PROMPTATLAS_SEARCH_MAX_TERMS ) {
			break;
		}
	}

	return $groups;
}

/**
 * Get the normalised full phrase for a query, when it has more than one word.
 *
 * Used for ranking only: a result containing the exact phrase the visitor typed
 * is the best possible match and should sort above results that merely contain
 * the words separately.
 *
 * @param string $raw Raw search string.
 * @return string Normalised phrase, or an empty string for single-word queries.
 */
function promptatlas_search_phrase( $raw ) {
	if ( ! is_string( $raw ) ) {
		return '';
	}

	if ( strlen( $raw ) > PROMPTATLAS_SEARCH_MAX_LENGTH ) {
		$raw = substr( $raw, 0, PROMPTATLAS_SEARCH_MAX_LENGTH );
	}

	$normalised = promptatlas_normalise_search_term( $raw );

	return str_contains( $normalised, ' ' ) ? $normalised : '';
}

/**
 * Join the prompt-text meta row so search can read it.
 *
 * A LEFT JOIN, so a prompt with no prompt text still appears when its title
 * matches. Restricted to the one meta key, so this never scans the whole meta
 * table.
 *
 * @param string   $join  Existing JOIN clause.
 * @param WP_Query $query The query.
 * @return string Modified JOIN.
 */
function promptatlas_search_join( $join, $query ) {
	global $wpdb;

	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return $join;
	}

	$join .= $wpdb->prepare(
		" LEFT JOIN {$wpdb->postmeta} AS promptatlas_search_meta
		  ON ( {$wpdb->posts}.ID = promptatlas_search_meta.post_id
		  AND promptatlas_search_meta.meta_key = %s ) ",
		PROMPTATLAS_META_SEARCH_BLOB
	);

	return $join;
}
add_filter( 'posts_join', 'promptatlas_search_join', 10, 2 );

/**
 * Replace the core search WHERE clause.
 *
 * Every value goes through $wpdb->prepare() with $wpdb->esc_like(), so a query
 * containing %, _ or quotes is matched literally and cannot alter the SQL.
 *
 * @param string   $search Existing search SQL.
 * @param WP_Query $query  The query.
 * @return string Modified search SQL.
 */
function promptatlas_search_where( $search, $query ) {
	global $wpdb;

	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return $search;
	}

	$raw = $query->get( 's' );

	$groups = promptatlas_search_term_groups( is_string( $raw ) ? $raw : '' );

	/*
	 * A query that produced no usable terms - "a", "!!", a single character -
	 * must return nothing, not everything. Falling through to core's clause
	 * here meant searching for "a" matched every prompt on the site, which
	 * looks like the search is broken in the other direction.
	 */
	if ( empty( $groups ) ) {
		if ( is_string( $raw ) && '' !== trim( $raw ) ) {
			return ' AND 1=0 ';
		}

		return $search;
	}

	$clauses = array();

	foreach ( $groups as $group ) {
		$alternatives = array();

		foreach ( $group as $term ) {
			$like = '%' . $wpdb->esc_like( $term ) . '%';

			/*
			 * Two fields only: the title, and the derived plain-text index.
			 * The index already contains the excerpt, the tag-stripped content,
			 * the prompt text, the placeholder descriptions and the term names,
			 * so searching post_content directly is both redundant and wrong -
			 * it would match HTML tag names. See includes/search-index.php.
			 */
			$alternatives[] = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $like );
			$alternatives[] = $wpdb->prepare( 'promptatlas_search_meta.meta_value LIKE %s', $like );
		}

		if ( ! empty( $alternatives ) ) {
			$clauses[] = '(' . implode( ' OR ', $alternatives ) . ')';
		}
	}

	if ( empty( $clauses ) ) {
		return $search;
	}

	return ' AND (' . implode( ' AND ', $clauses ) . ') ';
}
add_filter( 'posts_search', 'promptatlas_search_where', 10, 2 );

/**
 * De-duplicate search results.
 *
 * The meta JOIN produces one row per matching meta row. In practice the prompt
 * text is single-valued so duplicates should not arise, but a stray duplicate
 * meta row would otherwise show the same prompt twice, which looks broken.
 *
 * @param string   $distinct Existing DISTINCT clause.
 * @param WP_Query $query    The query.
 * @return string Modified clause.
 */
function promptatlas_search_distinct( $distinct, $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return $distinct;
	}

	return 'DISTINCT';
}
add_filter( 'posts_distinct', 'promptatlas_search_distinct', 10, 2 );

/**
 * Order search results by where the match was found.
 *
 * A title match is a much stronger signal than a mention somewhere in the
 * explanation, so title matches sort first, then excerpt, then everything else.
 * Within a band, newest first.
 *
 * Core's own relevance ordering is built from the unexpanded terms, so it does
 * not know about aliases. This replaces it with ordering that does.
 *
 * @param string   $orderby Existing ORDER BY clause.
 * @param WP_Query $query   The query.
 * @return string Modified clause.
 */
function promptatlas_search_orderby( $orderby, $query ) {
	global $wpdb;

	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return $orderby;
	}

	$raw    = $query->get( 's' );
	$groups = promptatlas_search_term_groups( is_string( $raw ) ? $raw : '' );

	if ( empty( $groups ) ) {
		return $orderby;
	}

	$title_matches   = array();
	$excerpt_matches = array();

	foreach ( $groups as $group ) {
		foreach ( $group as $term ) {
			$like = '%' . $wpdb->esc_like( $term ) . '%';

			$title_matches[]   = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $like );
			$excerpt_matches[] = $wpdb->prepare( "{$wpdb->posts}.post_excerpt LIKE %s", $like );
		}
	}

	$title_sql   = '(' . implode( ' OR ', $title_matches ) . ')';
	$excerpt_sql = '(' . implode( ' OR ', $excerpt_matches ) . ')';

	/*
	 * Exact-phrase matches rank above everything. This is where the full phrase
	 * is used - as a ranking signal rather than a requirement, so "resume job"
	 * still finds posts containing both words separately, but a post containing
	 * the literal phrase sorts first.
	 */
	$phrase = promptatlas_search_phrase( is_string( $raw ) ? $raw : '' );

	if ( '' !== $phrase ) {
		$phrase_like = '%' . $wpdb->esc_like( $phrase ) . '%';

		$phrase_sql = '(' . $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $phrase_like )
			. ' OR ' . $wpdb->prepare( 'promptatlas_search_meta.meta_value LIKE %s', $phrase_like ) . ')';

		return "CASE WHEN {$phrase_sql} THEN 0 WHEN {$title_sql} THEN 1 WHEN {$excerpt_sql} THEN 2 ELSE 3 END ASC, {$wpdb->posts}.post_date DESC";
	}

	return "CASE WHEN {$title_sql} THEN 0 WHEN {$excerpt_sql} THEN 1 ELSE 2 END ASC, {$wpdb->posts}.post_date DESC";
}
add_filter( 'posts_orderby', 'promptatlas_search_orderby', 10, 2 );

/**
 * Stop an empty search from matching the whole site.
 *
 * WordPress treats /?s= as a search for nothing and returns every post, which
 * reads as "here is everything" rather than "you did not type anything". The
 * search template renders a prompt-to-search state instead.
 *
 * @param WP_Query $query The query.
 * @return void
 */
function promptatlas_handle_empty_search( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$raw = $query->get( 's' );

	if ( is_string( $raw ) && '' === trim( $raw ) ) {
		$query->set( 'post__in', array( 0 ) );
	}
}
add_action( 'pre_get_posts', 'promptatlas_handle_empty_search', 20 );

/**
 * Build a search result snippet highlighting where the match was found.
 *
 * Prefers the hand-written excerpt. When the match is only in the prompt text,
 * shows the surrounding window of that instead — otherwise a result can look
 * irrelevant because the reason it matched is invisible.
 *
 * @param int    $post_id Post ID.
 * @param string $raw     Raw search query.
 * @return string Plain-text snippet. Escape before output.
 */
function promptatlas_search_snippet( $post_id, $raw ) {
	$excerpt = get_the_excerpt( $post_id );

	$groups = promptatlas_search_term_groups( is_string( $raw ) ? $raw : '' );
	$terms  = array();

	foreach ( $groups as $group ) {
		foreach ( $group as $term ) {
			$terms[] = $term;
		}
	}

	if ( empty( $terms ) ) {
		return $excerpt;
	}

	// If the excerpt already shows why this matched, use it.
	$haystack = promptatlas_normalise_search_term( $excerpt . ' ' . get_the_title( $post_id ) );

	foreach ( $terms as $term ) {
		if ( '' !== $term && str_contains( $haystack, $term ) ) {
			return $excerpt;
		}
	}

	// Otherwise show the window of prompt text around the first match.
	$prompt_text = promptatlas_get_prompt_text( $post_id );

	if ( '' === $prompt_text ) {
		return $excerpt;
	}

	$normalised = promptatlas_normalise_search_term( $prompt_text );

	foreach ( $terms as $term ) {
		if ( '' === $term ) {
			continue;
		}

		$position = strpos( $normalised, $term );

		if ( false === $position ) {
			continue;
		}

		$start   = max( 0, $position - 60 );
		$snippet = substr( $prompt_text, $start, 180 );
		$snippet = trim( preg_replace( '/\s+/', ' ', (string) $snippet ) );

		if ( '' === $snippet ) {
			continue;
		}

		return ( $start > 0 ? '…' : '' ) . $snippet . '…';
	}

	return $excerpt;
}
