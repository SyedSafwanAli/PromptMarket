<?php
/**
 * Post meta registration, sanitisation and accessors.
 *
 * Field list, requirements and purposes come from CONTENT_STRATEGY.md section 4.
 * Nothing here is invented at Phase 05.
 *
 * ---------------------------------------------------------------------------
 * Why every field is registered with `show_in_rest => false`
 * ---------------------------------------------------------------------------
 * These fields are edited through classic meta boxes (includes/admin-meta-boxes.php)
 * while the block editor is active. If the same meta were also exposed in REST,
 * the block editor would hold its own copy of the value in its entity record and
 * send it back on save, racing the meta box POST for the same keys. Keeping them
 * out of REST makes the meta box the single writer and removes the race entirely.
 *
 * The trade-off: these fields are not readable over the REST API. That is
 * acceptable because nothing consumes them over REST - the frontend is server
 * rendered PHP templates (DECISIONS.md, architecture). If a future phase needs
 * REST access, it must also move editing into a block-editor panel, which needs
 * a JavaScript build step that Phase 03 deliberately did not adopt.
 *
 * ---------------------------------------------------------------------------
 * Why the prompt text is NOT passed through sanitize_textarea_field()
 * ---------------------------------------------------------------------------
 * sanitize_textarea_field() strips anything that looks like an HTML tag. Prompt
 * text legitimately contains angle brackets: `<your text here>` placeholders,
 * generics like `List<String>`, and HTML in frontend-coding prompts. Stripping
 * them would silently corrupt the single most important field on the site, and
 * the editor would not notice until a visitor copied a broken prompt.
 *
 * So prompt text is stored close to raw - invalid UTF-8 removed, line endings
 * normalised, trimmed - and is ALWAYS escaped at the point of output with
 * esc_html(). That is the correct split of responsibility: store what the author
 * wrote, escape when rendering. Any template that outputs prompt text without
 * esc_html() is a bug.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta key for the prompt text itself - the thing visitors copy.
 */
const PROMPTATLAS_META_PROMPT_TEXT = '_promptatlas_prompt_text';

/**
 * Meta key for the placeholder variable definitions.
 */
const PROMPTATLAS_META_VARIABLES = '_promptatlas_variables';

/**
 * Meta key for real example output from a real run.
 */
const PROMPTATLAS_META_EXAMPLE_OUTPUT = '_promptatlas_example_output';

/**
 * Meta key for which tool produced the example output.
 */
const PROMPTATLAS_META_EXAMPLE_TOOL = '_promptatlas_example_tool';

/**
 * Meta key for source attribution when a prompt is adapted, not original.
 */
const PROMPTATLAS_META_ATTRIBUTION = '_promptatlas_attribution';

/**
 * Meta key for manually chosen related prompt IDs.
 */
const PROMPTATLAS_META_RELATED = '_promptatlas_related';

/**
 * Meta key on a collection: the ordered list of prompt IDs it contains.
 */
const PROMPTATLAS_META_COLLECTION_PROMPTS = '_promptatlas_collection_prompts';

/**
 * Meta key on a prompt: one row per collection containing it. Reverse index.
 */
const PROMPTATLAS_META_IN_COLLECTION = '_promptatlas_in_collection';

/**
 * Meta key on a collection: per-entry editorial notes, keyed by prompt ID.
 *
 * Added in Phase 11, which requires an editor to be able to add commentary for
 * individual entries. Kept as a separate map rather than folded into the
 * ordered ID list so that the existing list format - and any collection already
 * saved with it - stays valid.
 */
const PROMPTATLAS_META_COLLECTION_NOTES = '_promptatlas_collection_notes';

/**
 * Sanitise multi-line author text without destroying angle brackets.
 *
 * Used for the prompt text and the example output. See the file header for why
 * tag stripping is wrong for these fields.
 *
 * - Removes invalid UTF-8, which would otherwise break JSON and the database.
 * - Strips null bytes.
 * - Normalises CRLF and CR to LF so stored text is consistent regardless of the
 *   editor's platform.
 * - Collapses runs of more than two blank lines, which are always accidental.
 * - Trims leading and trailing whitespace.
 *
 * @param mixed $value Raw value from the editor.
 * @return string Sanitised text, safe to store. MUST still be escaped on output.
 */
function promptatlas_sanitize_multiline_text( $value ) {
	if ( ! is_scalar( $value ) ) {
		return '';
	}

	$value = (string) $value;
	$value = wp_check_invalid_utf8( $value );
	$value = str_replace( "\0", '', $value );
	$value = str_replace( array( "\r\n", "\r" ), "\n", $value );
	$value = preg_replace( "/\n{3,}/", "\n\n", $value );

	return trim( (string) $value );
}

/**
 * Sanitise the variables repeater.
 *
 * Shape: list of array( 'name' => string, 'description' => string ).
 * Variable names are forced to the [UPPERCASE_UNDERSCORE] convention from
 * CONTENT_STRATEGY.md 4.5, so an editor typing `job title` still produces a
 * token that matches the prompt text.
 *
 * Rows with an empty name are dropped: an unnamed variable cannot be matched to
 * a token and would only render as a blank checklist item.
 *
 * @param mixed $value Raw repeater value.
 * @return array<int, array{name: string, description: string}> Clean rows.
 */
function promptatlas_sanitize_variables( $value ) {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$clean = array();

	foreach ( $value as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$name = isset( $row['name'] ) && is_scalar( $row['name'] ) ? (string) $row['name'] : '';
		$name = sanitize_text_field( $name );
		$name = strtoupper( $name );
		$name = preg_replace( '/[^A-Z0-9_]+/', '_', $name );
		$name = trim( (string) $name, '_' );

		if ( '' === $name ) {
			continue;
		}

		$description = isset( $row['description'] ) && is_scalar( $row['description'] ) ? (string) $row['description'] : '';

		$clean[] = array(
			'name'        => $name,
			'description' => sanitize_text_field( $description ),
		);

		// A prompt needing more than 12 placeholders is a template, not a
		// prompt. Cap it so a paste accident cannot create hundreds of rows.
		if ( count( $clean ) >= 12 ) {
			break;
		}
	}

	return $clean;
}

/**
 * Sanitise a list of related prompt IDs.
 *
 * Validates that each ID is a real, non-trashed prompt - not merely an integer.
 * Without that check a deleted or mistyped ID would render as a broken related
 * link. Capped at 3 per CONTENT_STRATEGY.md 4.8.
 *
 * @param mixed $value Raw ID list.
 * @return int[] Validated prompt IDs.
 */
function promptatlas_sanitize_related_ids( $value ) {
	if ( is_string( $value ) ) {
		$value = preg_split( '/[\s,]+/', $value, -1, PREG_SPLIT_NO_EMPTY );
	}

	if ( ! is_array( $value ) ) {
		return array();
	}

	$clean = array();

	foreach ( $value as $id ) {
		$id = absint( $id );

		if ( 0 === $id || in_array( $id, $clean, true ) ) {
			continue;
		}

		if ( 'prompt' !== get_post_type( $id ) ) {
			continue;
		}

		if ( 'trash' === get_post_status( $id ) ) {
			continue;
		}

		$clean[] = $id;

		if ( count( $clean ) >= 3 ) {
			break;
		}
	}

	return $clean;
}

/**
 * Sanitise a collection's ordered prompt ID list.
 *
 * Order is editorial and meaningful (CONTENT_STRATEGY.md 5.1), so this must
 * preserve the given sequence. Duplicates are removed, keeping first position.
 *
 * @param mixed $value Raw ID list.
 * @return int[] Validated, ordered prompt IDs.
 */
function promptatlas_sanitize_collection_prompts( $value ) {
	if ( is_string( $value ) ) {
		$value = preg_split( '/[\s,]+/', $value, -1, PREG_SPLIT_NO_EMPTY );
	}

	if ( ! is_array( $value ) ) {
		return array();
	}

	$clean = array();

	foreach ( $value as $id ) {
		$id = absint( $id );

		if ( 0 === $id || in_array( $id, $clean, true ) ) {
			continue;
		}

		if ( 'prompt' !== get_post_type( $id ) ) {
			continue;
		}

		if ( 'trash' === get_post_status( $id ) ) {
			continue;
		}

		$clean[] = $id;
	}

	return $clean;
}

/**
 * Sanitise the per-entry collection notes.
 *
 * Shape: array of prompt ID => note text. Keys are validated as real prompt
 * IDs, so a note cannot be attached to an arbitrary post, and empty notes are
 * dropped rather than stored as blanks.
 *
 * @param mixed $value Raw notes map.
 * @return array<int, string> Clean notes keyed by prompt ID.
 */
function promptatlas_sanitize_collection_notes( $value ) {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$clean = array();

	foreach ( $value as $prompt_id => $note ) {
		$prompt_id = absint( $prompt_id );

		if ( 0 === $prompt_id || 'prompt' !== get_post_type( $prompt_id ) ) {
			continue;
		}

		if ( ! is_scalar( $note ) ) {
			continue;
		}

		$note = sanitize_text_field( (string) $note );

		if ( '' === $note ) {
			continue;
		}

		$clean[ $prompt_id ] = $note;
	}

	return $clean;
}

/**
 * Get a collection's per-entry notes.
 *
 * @param int $collection_id Collection ID.
 * @return array<int, string> Notes keyed by prompt ID.
 */
function promptatlas_get_collection_notes( $collection_id ) {
	$notes = get_post_meta( absint( $collection_id ), PROMPTATLAS_META_COLLECTION_NOTES, true );

	return is_array( $notes ) ? $notes : array();
}

/**
 * Authorise reads and writes of PromptAtlas meta.
 *
 * Registered as the `auth_callback` for every field. This is the server-side
 * capability check: a hidden form field is not a permission (CLAUDE.md 4.1).
 *
 * @param bool   $allowed Whether the user can add the meta. Unused; recomputed.
 * @param string $meta_key The meta key being checked. Unused.
 * @param int    $post_id The post ID.
 * @return bool True when the current user may edit this post.
 */
function promptatlas_meta_auth_callback( $allowed, $meta_key, $post_id ) {
	unset( $allowed, $meta_key );

	return current_user_can( 'edit_post', (int) $post_id );
}

/**
 * Register every PromptAtlas meta field.
 *
 * Each registration declares its type, a sanitize_callback and an auth_callback,
 * so that any write path - meta box, WP-CLI, or a future REST panel - is
 * sanitised and authorised by the same code.
 *
 * @return void
 */
function promptatlas_register_meta() {

	// --- prompt ------------------------------------------------------------

	register_post_meta(
		'prompt',
		PROMPTATLAS_META_PROMPT_TEXT,
		array(
			'type'              => 'string',
			'description'       => __( 'The prompt text a visitor copies.', 'promptatlas' ),
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => false,
			'sanitize_callback' => 'promptatlas_sanitize_multiline_text',
			'auth_callback'     => 'promptatlas_meta_auth_callback',
		)
	);

	register_post_meta(
		'prompt',
		PROMPTATLAS_META_VARIABLES,
		array(
			'type'              => 'array',
			'description'       => __( 'Placeholder variables used in the prompt text.', 'promptatlas' ),
			'single'            => true,
			'default'           => array(),
			'show_in_rest'      => false,
			'sanitize_callback' => 'promptatlas_sanitize_variables',
			'auth_callback'     => 'promptatlas_meta_auth_callback',
		)
	);

	register_post_meta(
		'prompt',
		PROMPTATLAS_META_EXAMPLE_OUTPUT,
		array(
			'type'              => 'string',
			'description'       => __( 'Real output from a real run of this prompt.', 'promptatlas' ),
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => false,
			'sanitize_callback' => 'promptatlas_sanitize_multiline_text',
			'auth_callback'     => 'promptatlas_meta_auth_callback',
		)
	);

	register_post_meta(
		'prompt',
		PROMPTATLAS_META_EXAMPLE_TOOL,
		array(
			'type'              => 'string',
			'description'       => __( 'Which tool produced the example output.', 'promptatlas' ),
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => false,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => 'promptatlas_meta_auth_callback',
		)
	);

	register_post_meta(
		'prompt',
		PROMPTATLAS_META_ATTRIBUTION,
		array(
			'type'              => 'string',
			'description'       => __( 'Source and licence when a prompt is adapted rather than original.', 'promptatlas' ),
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => false,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => 'promptatlas_meta_auth_callback',
		)
	);

	register_post_meta(
		'prompt',
		PROMPTATLAS_META_RELATED,
		array(
			'type'              => 'array',
			'description'       => __( 'Manually chosen related prompts, shown before automatic ones.', 'promptatlas' ),
			'single'            => true,
			'default'           => array(),
			'show_in_rest'      => false,
			'sanitize_callback' => 'promptatlas_sanitize_related_ids',
			'auth_callback'     => 'promptatlas_meta_auth_callback',
		)
	);

	// Reverse index, maintained by includes/collection-sync.php. Not single:
	// one row per collection, which is what makes "which collections contain
	// this prompt" a cheap meta query.
	register_post_meta(
		'prompt',
		PROMPTATLAS_META_IN_COLLECTION,
		array(
			'type'              => 'integer',
			'description'       => __( 'ID of a collection containing this prompt. Maintained automatically.', 'promptatlas' ),
			'single'            => false,
			'show_in_rest'      => false,
			'sanitize_callback' => 'absint',
			'auth_callback'     => 'promptatlas_meta_auth_callback',
		)
	);

	// --- collection --------------------------------------------------------

	register_post_meta(
		'collection',
		PROMPTATLAS_META_COLLECTION_NOTES,
		array(
			'type'              => 'array',
			'description'       => __( 'Editorial note per prompt in this collection, keyed by prompt ID.', 'promptatlas' ),
			'single'            => true,
			'default'           => array(),
			'show_in_rest'      => false,
			'sanitize_callback' => 'promptatlas_sanitize_collection_notes',
			'auth_callback'     => 'promptatlas_meta_auth_callback',
		)
	);

	register_post_meta(
		'collection',
		PROMPTATLAS_META_COLLECTION_PROMPTS,
		array(
			'type'              => 'array',
			'description'       => __( 'Ordered list of prompt IDs in this collection.', 'promptatlas' ),
			'single'            => true,
			'default'           => array(),
			'show_in_rest'      => false,
			'sanitize_callback' => 'promptatlas_sanitize_collection_prompts',
			'auth_callback'     => 'promptatlas_meta_auth_callback',
		)
	);
}
add_action( 'init', 'promptatlas_register_meta', 7 );

/**
 * Get a prompt's text.
 *
 * @param int $post_id Prompt ID.
 * @return string Raw prompt text. Escape before output.
 */
function promptatlas_get_prompt_text( $post_id ) {
	$text = get_post_meta( (int) $post_id, PROMPTATLAS_META_PROMPT_TEXT, true );

	return is_string( $text ) ? $text : '';
}

/**
 * Get a prompt's placeholder variables.
 *
 * @param int $post_id Prompt ID.
 * @return array<int, array{name: string, description: string}> Variable rows.
 */
function promptatlas_get_variables( $post_id ) {
	$variables = get_post_meta( (int) $post_id, PROMPTATLAS_META_VARIABLES, true );

	return is_array( $variables ) ? $variables : array();
}

/**
 * Find placeholder tokens present in a prompt's text.
 *
 * Tokens follow the [UPPERCASE_UNDERSCORE] convention from
 * CONTENT_STRATEGY.md 4.5.
 *
 * @param string $text Prompt text.
 * @return string[] Unique token names, without brackets.
 */
function promptatlas_extract_tokens( $text ) {
	if ( ! is_string( $text ) || '' === $text ) {
		return array();
	}

	preg_match_all( '/\[([A-Z0-9_]{2,})\]/', $text, $matches );

	if ( empty( $matches[1] ) ) {
		return array();
	}

	return array_values( array_unique( $matches[1] ) );
}

/**
 * Compare the tokens in the prompt text against the declared variables.
 *
 * Powers the editor warning described in CONTENT_STRATEGY.md 4.5. It warns and
 * never blocks, because an editor must be able to save a half-written draft.
 *
 * @param int $post_id Prompt ID.
 * @return array{undeclared: string[], unused: string[]} Mismatches.
 */
function promptatlas_get_variable_mismatches( $post_id ) {
	$tokens = promptatlas_extract_tokens( promptatlas_get_prompt_text( $post_id ) );

	$declared = array();
	foreach ( promptatlas_get_variables( $post_id ) as $variable ) {
		if ( isset( $variable['name'] ) ) {
			$declared[] = $variable['name'];
		}
	}

	return array(
		'undeclared' => array_values( array_diff( $tokens, $declared ) ),
		'unused'     => array_values( array_diff( $declared, $tokens ) ),
	);
}

/**
 * Should an "Updated" date be shown for this post?
 *
 * CONTENT_STRATEGY.md 4.6: show it only when the modified date is more than
 * seven days after publication, so that fixing a typo does not relabel a page
 * as freshly updated.
 *
 * @param int $post_id Post ID.
 * @return bool True when the update is substantial enough to display.
 */
function promptatlas_should_show_updated_date( $post_id ) {
	$published = get_post_time( 'U', true, $post_id );
	$modified  = get_post_modified_time( 'U', true, $post_id );

	if ( empty( $published ) || empty( $modified ) ) {
		return false;
	}

	return ( $modified - $published ) > ( 7 * DAY_IN_SECONDS );
}
