<?php
/**
 * Search aliases: synonyms, alternate spellings and Roman Urdu terms.
 *
 * Stored as an editable option, not a hardcoded array, so the owner can extend
 * the list from the admin without a developer. Phase 09 requires exactly that.
 *
 * Format, one rule per line:
 *
 *     resume: cv, curriculum vitae, resumé
 *     job: naukri, naukari, rozgar
 *
 * The word before the colon is the canonical term. Everything after it is a
 * variant. Matching is two-way: searching "cv" finds prompts that say "resume",
 * and searching "resume" finds anything that happens to say "cv".
 *
 * Roman Urdu support is search-input only, per DECISIONS.md decision 3. No
 * content is translated; these entries let an Urdu-speaking visitor find
 * English prompts using the words they would actually type.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Option name holding the raw alias text.
 */
const PROMPTATLAS_OPTION_ALIASES = 'promptatlas_search_aliases';

/**
 * The default alias list, seeded on activation.
 *
 * Deliberately short. A long speculative list is worse than a short real one:
 * every wrong alias makes search less precise, and Phase 09's zero-result log
 * will show which aliases are actually needed rather than which were guessed.
 *
 * @return string Newline-separated rules.
 */
function promptatlas_default_aliases() {
	$rules = array(
		'# One rule per line:  canonical: variant, variant',
		'# Lines starting with # are ignored. Matching works both ways.',
		'',
		'# --- English synonyms and spellings ---',
		'resume: cv, curriculum vitae, resumé',
		'cover letter: covering letter',
		'email: e-mail, mail',
		'summarise: summarize, summary, tldr',
		'optimise: optimize',
		'code: coding, programming, developer, software',
		'debug: bug, error, fix, troubleshoot',
		'image: picture, photo, graphic, visual',
		'video: clip, footage',
		'social media: instagram, linkedin, twitter, facebook, tiktok',
		'interview: interviewing, interview questions',
		'presentation: slides, deck, powerpoint',
		'spreadsheet: excel, google sheets, csv',
		'blog: article, post',
		'teacher: teaching, lesson, classroom',
		'student: studying, revision, exam',
		'',
		'# --- Roman Urdu search input (content stays English) ---',
		'job: naukri, naukari, rozgar',
		'work: kaam, kam',
		'write: likhna, likho, likhai',
		'study: parhai, padhai, parhna',
		'business: karobar, kaarobar',
		'money: paisa, paise, rupay',
		'help: madad, madat',
		'make: banao, banana, banaye',
		'picture: tasveer, tasweer',
		'letter: khat',
		'teacher: ustad, ustaad',
		'student: talib ilm, talibilm',
		'explain: samjhao, samjhana',
		'easy: asaan, asan',
	);

	return implode( "\n", $rules );
}

/**
 * Get the alias map, parsed and cached.
 *
 * Returns a lookup of term => array of equivalent terms, including the term
 * itself. Both directions are present, so a lookup never needs to scan.
 *
 * Cached in a transient because this runs on every search request and parsing
 * is pure string work with no reason to repeat it.
 *
 * @return array<string, string[]> Term to equivalents.
 */
function promptatlas_get_alias_map() {
	$cached = get_transient( 'promptatlas_alias_map' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$raw = get_option( PROMPTATLAS_OPTION_ALIASES, '' );

	if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
		$raw = promptatlas_default_aliases();
	}

	$map = array();

	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );

		if ( '' === $line || str_starts_with( $line, '#' ) || ! str_contains( $line, ':' ) ) {
			continue;
		}

		list( $canonical, $variants ) = explode( ':', $line, 2 );

		$group = array_merge(
			array( $canonical ),
			explode( ',', $variants )
		);

		$group = array_values(
			array_unique(
				array_filter(
					array_map(
						static function ( $term ) {
							return promptatlas_normalise_search_term( $term );
						},
						$group
					),
					static function ( $term ) {
						return '' !== $term;
					}
				)
			)
		);

		if ( count( $group ) < 2 ) {
			continue;
		}

		// Both directions, so any member of the group finds all the others.
		foreach ( $group as $term ) {
			$existing     = isset( $map[ $term ] ) ? $map[ $term ] : array();
			$map[ $term ] = array_values( array_unique( array_merge( $existing, $group ) ) );
		}
	}

	set_transient( 'promptatlas_alias_map', $map, DAY_IN_SECONDS );

	return $map;
}

/**
 * Normalise a search term for comparison.
 *
 * Lowercases, strips accents so "resumé" and "resume" match, removes
 * punctuation, and collapses whitespace.
 *
 * @param string $term Raw term.
 * @return string Normalised term.
 */
function promptatlas_normalise_search_term( $term ) {
	if ( ! is_string( $term ) ) {
		return '';
	}

	$term = remove_accents( $term );
	$term = strtolower( $term );
	$term = preg_replace( '/[^a-z0-9\s\-]/', ' ', $term );
	$term = preg_replace( '/\s+/', ' ', (string) $term );

	return trim( (string) $term );
}

/**
 * Expand a term into itself plus any aliases.
 *
 * @param string $term Normalised term.
 * @return string[] The term and its equivalents.
 */
function promptatlas_expand_term( $term ) {
	$map = promptatlas_get_alias_map();

	if ( isset( $map[ $term ] ) ) {
		return $map[ $term ];
	}

	return array( $term );
}

/**
 * Clear the cached alias map when the option changes.
 *
 * @return void
 */
function promptatlas_flush_alias_cache() {
	delete_transient( 'promptatlas_alias_map' );
}
add_action( 'update_option_' . PROMPTATLAS_OPTION_ALIASES, 'promptatlas_flush_alias_cache' );
add_action( 'add_option_' . PROMPTATLAS_OPTION_ALIASES, 'promptatlas_flush_alias_cache' );

/**
 * Register the alias settings page under the Prompts menu.
 *
 * @return void
 */
function promptatlas_register_alias_page() {
	add_submenu_page(
		'edit.php?post_type=prompt',
		__( 'Search aliases', 'promptatlas' ),
		__( 'Search aliases', 'promptatlas' ),
		'manage_options',
		'promptatlas-aliases',
		'promptatlas_render_alias_page'
	);
}
add_action( 'admin_menu', 'promptatlas_register_alias_page' );

/**
 * Register the alias setting.
 *
 * @return void
 */
function promptatlas_register_alias_setting() {
	register_setting(
		'promptatlas_aliases',
		PROMPTATLAS_OPTION_ALIASES,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'promptatlas_sanitize_aliases',
			'default'           => '',
			'show_in_rest'      => false,
		)
	);
}
add_action( 'admin_init', 'promptatlas_register_alias_setting' );

/**
 * Sanitise the alias textarea.
 *
 * Caps the total size so a paste accident cannot store something enormous that
 * is then parsed on every search.
 *
 * @param mixed $value Raw textarea value.
 * @return string Sanitised text.
 */
function promptatlas_sanitize_aliases( $value ) {
	if ( ! is_string( $value ) ) {
		return '';
	}

	$value = sanitize_textarea_field( $value );

	if ( strlen( $value ) > 20000 ) {
		$value = substr( $value, 0, 20000 );
	}

	return $value;
}

/**
 * Render the alias settings page.
 *
 * @return void
 */
function promptatlas_render_alias_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to view this page.', 'promptatlas' ) );
	}

	$value = get_option( PROMPTATLAS_OPTION_ALIASES, '' );

	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		$value = promptatlas_default_aliases();
	}

	$map = promptatlas_get_alias_map();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Search aliases', 'promptatlas' ); ?></h1>

		<p class="description" style="max-width: 46em;">
			<?php
			esc_html_e(
				'Words that should find each other. Matching works in both directions: with "resume: cv" in the list, searching for "cv" finds prompts that say resume, and searching for "resume" finds prompts that say cv.',
				'promptatlas'
			);
			?>
		</p>
		<p class="description" style="max-width: 46em;">
			<strong><?php esc_html_e( 'Roman Urdu note:', 'promptatlas' ); ?></strong>
			<?php
			esc_html_e(
				'Urdu words here let people search in Roman Urdu and find the English prompts. The prompts themselves stay in English — nothing is translated.',
				'promptatlas'
			);
			?>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'promptatlas_aliases' ); ?>

			<p>
				<label for="promptatlas-aliases" class="screen-reader-text">
					<?php esc_html_e( 'Alias rules', 'promptatlas' ); ?>
				</label>
				<textarea
					id="promptatlas-aliases"
					name="<?php echo esc_attr( PROMPTATLAS_OPTION_ALIASES ); ?>"
					rows="22"
					class="large-text code"
					spellcheck="false"
				><?php echo esc_textarea( $value ); ?></textarea>
			</p>

			<p class="description">
				<?php
				printf(
					/* translators: %s: number of indexed search terms. */
					esc_html__( 'Currently active: %s terms.', 'promptatlas' ),
					esc_html( number_format_i18n( count( $map ) ) )
				);
				?>
				<?php esc_html_e( 'One rule per line, in the form "word: variant, variant". Lines starting with # are ignored.', 'promptatlas' ); ?>
			</p>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
