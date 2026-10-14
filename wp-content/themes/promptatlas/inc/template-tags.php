<?php
/**
 * Template helpers.
 *
 * Presentation-only output. Anything that reads the content model calls the
 * accessors in promptatlas-core rather than touching meta keys directly, so the
 * theme never has to know how the model is stored.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output the site branding: the custom logo if set, otherwise the wordmark.
 *
 * The site name is never an h1, on any page including the homepage.
 *
 * The usual pattern of making it an h1 on the front page produces two h1
 * elements the moment the homepage has its own heading — which it does
 * ("Find a prompt that actually works"). That is both an accessibility problem,
 * because the document outline gains a second top-level heading that is really
 * site furniture, and a weaker signal, because the more descriptive heading
 * stops being the page's primary one.
 *
 * Every page therefore has exactly one h1, and it always belongs to the page's
 * own content.
 *
 * @return void
 */
function promptatlas_site_branding() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}

	?>
	<div class="site-title">
		<a class="site-title__link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php promptatlas_mark( 26 ); ?>
			<span class="site-title__text"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
		</a>
	</div>
	<?php
}

/**
 * Output the light/dark theme toggle.
 *
 * Hidden by CSS when JavaScript is unavailable: a toggle that cannot toggle is
 * worse than no toggle. `aria-pressed` is managed by the script; the button
 * carries an accessible name because it is icon-only.
 *
 * @return void
 */
function promptatlas_theme_toggle() {
	?>
	<button
		type="button"
		class="theme-toggle js-theme-toggle"
		aria-pressed="false"
		title="<?php esc_attr_e( 'Switch between light and dark theme', 'promptatlas' ); ?>"
		data-label-dark="<?php esc_attr_e( 'Switch to dark theme', 'promptatlas' ); ?>"
		data-label-light="<?php esc_attr_e( 'Switch to light theme', 'promptatlas' ); ?>"
	>
		<span class="theme-toggle__icon theme-toggle__icon--light" aria-hidden="true">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
				<circle cx="12" cy="12" r="4.5"/>
				<path d="M12 2.5v2M12 19.5v2M2.5 12h2M19.5 12h2M5.2 5.2l1.4 1.4M17.4 17.4l1.4 1.4M18.8 5.2l-1.4 1.4M6.6 17.4l-1.4 1.4"/>
			</svg>
		</span>
		<span class="theme-toggle__icon theme-toggle__icon--dark" aria-hidden="true">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a7 7 0 1 0 10.5 10.5Z"/>
			</svg>
		</span>
		<span class="screen-reader-text js-theme-toggle-label">
			<?php esc_html_e( 'Switch to dark theme', 'promptatlas' ); ?>
		</span>
	</button>
	<?php
}

/**
 * Output the published date, and an updated date when the change was material.
 *
 * CONTENT_STRATEGY.md 4.6: "Updated" appears only when the modified date is more
 * than seven days after publication, so fixing a typo does not relabel the page
 * as fresh. The gate lives in promptatlas-core.
 *
 * @return void
 */
function promptatlas_posted_on() {
	$post_id = get_the_ID();

	if ( ! $post_id ) {
		return;
	}

	printf(
		'<time class="entry-date" datetime="%1$s">%2$s</time>',
		esc_attr( (string) get_the_date( DATE_W3C ) ),
		esc_html( (string) get_the_date() )
	);

	if ( function_exists( 'promptatlas_should_show_updated_date' ) && promptatlas_should_show_updated_date( $post_id ) ) {
		printf(
			' <span class="entry-updated">%1$s <time datetime="%2$s">%3$s</time></span>',
			esc_html__( 'Updated', 'promptatlas' ),
			esc_attr( (string) get_the_modified_date( DATE_W3C ) ),
			esc_html( (string) get_the_modified_date() )
		);
	}
}

/**
 * Output a prompt's category as a link.
 *
 * @param int|null $post_id Prompt ID, or null for the current post.
 * @return void
 */
function promptatlas_the_category( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

	if ( ! function_exists( 'promptatlas_get_prompt_category' ) ) {
		return;
	}

	$term = promptatlas_get_prompt_category( $post_id );

	if ( ! $term ) {
		return;
	}

	printf(
		'<a class="category-link" href="%1$s">%2$s</a>',
		esc_url( (string) get_term_link( $term ) ),
		esc_html( $term->name )
	);
}

/**
 * Output a term list as styled pills.
 *
 * @param string   $taxonomy Taxonomy name.
 * @param int|null $post_id  Post ID, or null for the current post.
 * @param string   $extra_class Extra class for each pill.
 * @return void
 */
function promptatlas_the_terms( $taxonomy, $post_id = null, $extra_class = '' ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$terms   = get_the_terms( $post_id, $taxonomy );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}

	echo '<ul class="term-list">';

	foreach ( $terms as $term ) {
		printf(
			'<li><a class="term-pill %1$s" href="%2$s">%3$s</a></li>',
			esc_attr( $extra_class ),
			esc_url( (string) get_term_link( $term ) ),
			esc_html( $term->name )
		);
	}

	echo '</ul>';
}

/**
 * Output an honest empty state.
 *
 * Used wherever a listing has nothing to show. The content-integrity rule
 * requires an honest empty state rather than placeholder or invented content,
 * so this says plainly that there is nothing here yet.
 *
 * @param string $heading Short heading.
 * @param string $message One sentence of explanation.
 * @return void
 */
function promptatlas_empty_state( $heading, $message ) {
	?>
	<div class="empty-state">
		<h2 class="empty-state__heading"><?php echo esc_html( $heading ); ?></h2>
		<p class="empty-state__message"><?php echo esc_html( $message ); ?></p>
		<p>
			<a class="button button--secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Back to the homepage', 'promptatlas' ); ?>
			</a>
		</p>
	</div>
	<?php
}

/**
 * Output breadcrumbs.
 *
 * Deliberately simple and hand-rolled: one level of context plus the current
 * page. Structured data for breadcrumbs is Phase 12's job, not this function's.
 *
 * @return void
 */
function promptatlas_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$items = array(
		array(
			'label' => __( 'Home', 'promptatlas' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( is_singular( 'prompt' ) ) {
		$items[] = array(
			'label' => __( 'Prompts', 'promptatlas' ),
			'url'   => get_post_type_archive_link( 'prompt' ),
		);

		if ( function_exists( 'promptatlas_get_prompt_category' ) ) {
			$term = promptatlas_get_prompt_category( (int) get_the_ID() );

			if ( $term ) {
				$items[] = array(
					'label' => $term->name,
					'url'   => get_term_link( $term ),
				);
			}
		}
	} elseif ( is_singular( 'collection' ) ) {
		$items[] = array(
			'label' => __( 'Collections', 'promptatlas' ),
			'url'   => get_post_type_archive_link( 'collection' ),
		);
	} elseif ( is_tax( array( 'prompt_category', 'prompt_tool', 'prompt_tag' ) ) ) {
		$items[] = array(
			'label' => __( 'Prompts', 'promptatlas' ),
			'url'   => get_post_type_archive_link( 'prompt' ),
		);
	}

	?>
	<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'promptatlas' ); ?>">
		<ol class="breadcrumbs__list">
			<?php foreach ( $items as $item ) : ?>
				<li class="breadcrumbs__item">
					<a href="<?php echo esc_url( (string) $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
				</li>
			<?php endforeach; ?>
			<li class="breadcrumbs__item breadcrumbs__item--current" aria-current="page">
				<?php echo esc_html( promptatlas_current_page_label() ); ?>
			</li>
		</ol>
	</nav>
	<?php
}

/**
 * Get a short label for the current page, for breadcrumbs and headings.
 *
 * @return string Plain-text label.
 */
function promptatlas_current_page_label() {
	if ( is_singular() ) {
		return (string) get_the_title();
	}

	if ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();

		return $term instanceof WP_Term ? $term->name : __( 'Archive', 'promptatlas' );
	}

	if ( is_post_type_archive() ) {
		return (string) post_type_archive_title( '', false );
	}

	if ( is_search() ) {
		return __( 'Search results', 'promptatlas' );
	}

	if ( is_404() ) {
		return __( 'Page not found', 'promptatlas' );
	}

	return (string) get_the_title();
}

/**
 * Output accessible pagination.
 *
 * @return void
 */
function promptatlas_pagination() {
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'prev_text'          => esc_html__( 'Previous', 'promptatlas' ),
			'next_text'          => esc_html__( 'Next', 'promptatlas' ),
			'screen_reader_text' => esc_html__( 'Prompt navigation', 'promptatlas' ),
			'aria_label'         => esc_html__( 'Prompts', 'promptatlas' ),
			'class'              => 'pagination',
		)
	);
}

/**
 * Render prompt text with placeholder tokens highlighted.
 *
 * Order matters and is the whole security contract here: the text is escaped
 * FIRST with esc_html(), and only then are the [TOKEN] patterns wrapped in
 * markup. Doing it the other way round would mean injecting markup into
 * unescaped author text.
 *
 * Because escaping happens first, the only HTML in the result is the span this
 * function adds, so it is safe to echo. The token pattern matches
 * [UPPERCASE_UNDERSCORE] per CONTENT_STRATEGY.md 4.5, and angle brackets in the
 * prompt - <your name here>, List<String> - are already entities by this point
 * and cannot be confused for tags.
 *
 * @param string $text Raw prompt text.
 * @return string Escaped HTML, safe to echo.
 */
function promptatlas_format_prompt_text( $text ) {
	if ( ! is_string( $text ) || '' === $text ) {
		return '';
	}

	$escaped = esc_html( $text );

	$highlighted = preg_replace(
		'/\[([A-Z0-9_]{2,})\]/',
		'<span class="prompt-token">[$1]</span>',
		$escaped
	);

	return is_string( $highlighted ) ? $highlighted : $escaped;
}

/**
 * Check whether a page exists and is published, by slug.
 *
 * Used by the footer so policy links appear only once Phase 17 has created the
 * pages. Rendering a link to a page that does not exist yet would be a dead
 * link on a live site.
 *
 * @param string $slug Page slug.
 * @return string Permalink, or an empty string when the page is not published.
 */
function promptatlas_page_url( $slug ) {
	$page = get_page_by_path( sanitize_title( $slug ) );

	if ( ! $page || 'publish' !== $page->post_status ) {
		return '';
	}

	return (string) get_permalink( $page );
}
