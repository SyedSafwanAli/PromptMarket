<?php
/**
 * Technical SEO: metadata, social cards, structured data, crawler policy.
 *
 * Owned here, in the plugin, not by an SEO plugin. DECISIONS.md decision 4:
 * WordPress core already emits titles, canonicals, robots directives and
 * sitemaps; what it lacks is meta descriptions and social cards, which is a
 * few hundred lines rather than a dependency with its own database tables,
 * admin assets and migration cost.
 *
 * It lives in the plugin rather than the theme because metadata describes
 * content, not presentation: switching themes must not change what search
 * engines and social platforms see.
 *
 * NOTHING HERE HARDCODES A DOMAIN. Every URL comes from home_url(),
 * get_permalink() or get_term_link(), so moving from localhost to staging to
 * production is a configuration change, not a code change. Phase 22 depends on
 * that and it is verified in the completion report.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Option holding the Google Search Console verification token.
 *
 * Empty until the owner supplies a real one in Phase 18. Nothing is invented.
 */
const PROMPTATLAS_OPTION_GSC_TOKEN = 'promptatlas_gsc_token';

// ---------------------------------------------------------------------------
// 1. INDEXATION POLICY
// ---------------------------------------------------------------------------

/**
 * Decide the robots directive for the current request.
 *
 * The full policy, with the reasoning for each ruling:
 *
 * | Page type              | Policy          | Why                           |
 * |------------------------|-----------------|-------------------------------|
 * | Home, prompt, collection, prompt and collection archives, category and tool archives, pages | index, follow | Real, unique content |
 * | Tag archives           | noindex, follow | DECISIONS.md 12: tags exist to pass link equity and help a human browse, not to rank. A 3-prompt tag page is thin |
 * | Empty taxonomy archive | noindex, follow | A term page with nothing on it is the definition of a thin page, whatever the taxonomy |
 * | Search results         | noindex, follow | Infinite URL space, no unique content, and a classic source of index bloat |
 * | Paged archives (2+)    | index, follow   | Each page lists different prompts, so each is genuinely unique. Self-canonical, never canonicalised to page one - that would hide the deeper prompts from discovery |
 * | Author / date archives | noindex, follow | Single-author site: these duplicate the main archive exactly |
 * | Attachment pages       | redirected      | See the redirect below - they are a page per uploaded file with no content |
 * | My prompts             | noindex, nofollow | Per-visitor, empty for a crawler. Set by its template |
 * | Style reference        | noindex, nofollow | Internal. Set by its template |
 * | Any ?query= URL        | canonicalised   | Never indexed as a separate URL |
 *
 * @return string The directive, or an empty string to leave WordPress alone.
 */
function promptatlas_robots_directive() {
	/*
	 * The two internal page templates. Ruled on here rather than by each
	 * template printing its own meta tag, which produced TWO robots tags on
	 * those pages - core's and the template's. One policy, one tag, one place.
	 */
	if ( is_page_template( 'template-my-prompts.php' ) || is_page_template( 'template-style-reference.php' ) ) {
		return 'noindex, nofollow';
	}

	if ( is_search() ) {
		return 'noindex, follow';
	}

	if ( is_author() || is_date() ) {
		return 'noindex, follow';
	}

	if ( is_tax( 'prompt_tag' ) ) {
		return 'noindex, follow';
	}

	if ( is_tax() || is_category() || is_tag() ) {
		// An empty term archive is thin regardless of which taxonomy it is in.
		if ( isset( $GLOBALS['wp_query'] ) && 0 === (int) $GLOBALS['wp_query']->found_posts ) {
			return 'noindex, follow';
		}
	}

	return '';
}

/**
 * Apply the indexation policy.
 *
 * Filters core's robots output rather than printing a second tag, so there is
 * exactly one robots meta on every page. When the site is set to discourage
 * search engines, core's own noindex wins and this adds nothing.
 *
 * @param array<string, mixed> $robots Core's robots directives.
 * @return array<string, mixed> Filtered directives.
 */
function promptatlas_filter_robots( $robots ) {
	$directive = promptatlas_robots_directive();

	if ( '' === $directive ) {
		return $robots;
	}

	if ( str_contains( $directive, 'noindex' ) ) {
		$robots['noindex'] = true;
		unset( $robots['index'] );
	}

	if ( str_contains( $directive, 'nofollow' ) ) {
		$robots['nofollow'] = true;
		unset( $robots['follow'] );

		return $robots;
	}

	/*
	 * Only add `follow` when nothing has already asked for `nofollow`.
	 *
	 * When the site is set to discourage search engines, core emits
	 * `noindex, nofollow` for the whole site. Adding `follow` on top produced
	 * the self-contradicting `noindex, nofollow, follow` on tag archives.
	 * Core's site-wide decision wins; this policy only refines the cases core
	 * has no opinion about.
	 */
	if ( empty( $robots['nofollow'] ) ) {
		$robots['follow'] = true;
	}

	return $robots;
}
add_filter( 'wp_robots', 'promptatlas_filter_robots', 20 );

/**
 * Redirect attachment pages to the file they contain.
 *
 * WordPress creates a page for every uploaded file. They have no content, they
 * compete with real pages, and nobody wants to land on one from search.
 *
 * @return void
 */
function promptatlas_redirect_attachments() {
	if ( ! is_attachment() ) {
		return;
	}

	$url = wp_get_attachment_url( (int) get_queried_object_id() );

	if ( $url ) {
		wp_safe_redirect( $url, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'promptatlas_redirect_attachments' );

// ---------------------------------------------------------------------------
// 2. DESCRIPTIONS AND CANONICALS
// ---------------------------------------------------------------------------

/**
 * Get the meta description for the current request.
 *
 * Prefers the hand-written excerpt, which the content model requires to be
 * 120-160 characters and written as a standalone sentence precisely so it can
 * serve here (CONTENT_STRATEGY.md 4.3).
 *
 * The fallback is honest: a generated sentence describing what the page
 * actually is. It never truncates body text mid-sentence, which produces the
 * "...and that is why you should" descriptions that read as broken.
 *
 * @return string Plain-text description, or an empty string.
 */
function promptatlas_meta_description() {
	if ( is_front_page() ) {
		$tagline = get_bloginfo( 'description' );

		return $tagline ? $tagline : __( 'Hand-written AI prompts, each one explained.', 'promptatlas' );
	}

	if ( is_singular() ) {
		$excerpt = get_the_excerpt();

		if ( '' !== trim( (string) $excerpt ) ) {
			return trim( (string) $excerpt );
		}

		return '';
	}

	if ( is_post_type_archive( 'prompt' ) ) {
		return __( 'Every prompt in the library, each one written by hand and explained so you know what to change and why it works.', 'promptatlas' );
	}

	if ( is_post_type_archive( 'collection' ) ) {
		return __( 'Hand-picked sets of AI prompts for a particular job, in the order we would use them.', 'promptatlas' );
	}

	if ( is_tax() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			if ( '' !== trim( (string) $term->description ) ) {
				return trim( wp_strip_all_tags( $term->description ) );
			}

			return sprintf(
				/* translators: %s: the term name. */
				__( 'AI prompts for %s, each one explained so you know what to change before you use it.', 'promptatlas' ),
				$term->name
			);
		}
	}

	return '';
}

/**
 * Get the canonical URL for the current request.
 *
 * Built from WordPress's own URL functions so it follows the site address, and
 * deliberately preserves the page number: page two of an archive canonicals to
 * itself, not to page one. Pointing deep pages at page one is a common mistake
 * that hides everything past the first ten prompts from search entirely.
 *
 * @return string Absolute canonical URL, or an empty string.
 */
function promptatlas_canonical_url() {
	$url  = '';
	$page = max( 1, (int) get_query_var( 'paged' ) );

	if ( is_front_page() ) {
		$url = home_url( '/' );
	} elseif ( is_singular() ) {
		$url = (string) get_permalink();
	} elseif ( is_post_type_archive() ) {
		$url = (string) get_post_type_archive_link( (string) get_query_var( 'post_type' ) );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );
			$url  = is_wp_error( $link ) ? '' : (string) $link;
		}
	} elseif ( is_home() ) {
		$url = home_url( '/' );
	}

	if ( '' === $url ) {
		return '';
	}

	if ( $page > 1 && ! is_singular() ) {
		$url = trailingslashit( $url ) . 'page/' . $page . '/';
	}

	return $url;
}

// ---------------------------------------------------------------------------
// 3. HEAD OUTPUT
// ---------------------------------------------------------------------------

/**
 * Get the share image for the current request.
 *
 * Featured image if there is one, otherwise the Phase 07 default. The default
 * is read through the theme's accessor rather than a hardcoded path.
 *
 * @return array{url: string, width: int, height: int} Image details.
 */
function promptatlas_share_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$id  = (int) get_post_thumbnail_id();
		$src = wp_get_attachment_image_src( $id, 'promptatlas-hero' );

		if ( is_array( $src ) && ! empty( $src[0] ) ) {
			return array(
				'url'    => (string) $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
			);
		}
	}

	$default = function_exists( 'promptatlas_default_share_image' )
		? promptatlas_default_share_image()
		: '';

	return array(
		'url'    => (string) $default,
		'width'  => 1200,
		'height' => 630,
	);
}

/**
 * Print the description, canonical, social cards and verification token.
 *
 * Every tag is printed exactly once, from this one place, which is what makes
 * the "no duplicate tags" requirement verifiable rather than hopeful.
 *
 * @return void
 */
function promptatlas_print_head_meta() {
	$description = promptatlas_meta_description();
	$canonical   = promptatlas_canonical_url();
	$image       = promptatlas_share_image();
	$title       = wp_get_document_title();

	if ( '' !== $description ) {
		printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( $description ) );
	}

	if ( '' !== $canonical ) {
		printf( "<link rel=\"canonical\" href=\"%s\">\n", esc_url( $canonical ) );
	}

	// --- Open Graph ---
	printf( "<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( "<meta property=\"og:title\" content=\"%s\">\n", esc_attr( $title ) );

	if ( '' !== $description ) {
		printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $description ) );
	}

	if ( '' !== $canonical ) {
		printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $canonical ) );
	}

	printf( "<meta property=\"og:type\" content=\"%s\">\n", esc_attr( is_singular() && ! is_page() ? 'article' : 'website' ) );
	printf( "<meta property=\"og:locale\" content=\"%s\">\n", esc_attr( get_locale() ) );

	if ( '' !== $image['url'] ) {
		printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( $image['url'] ) );
		printf( "<meta property=\"og:image:width\" content=\"%d\">\n", (int) $image['width'] );
		printf( "<meta property=\"og:image:height\" content=\"%d\">\n", (int) $image['height'] );
		printf(
			"<meta property=\"og:image:alt\" content=\"%s\">\n",
			esc_attr( sprintf( /* translators: %s: site name. */ __( '%s — AI prompt library', 'promptatlas' ), get_bloginfo( 'name' ) ) )
		);
	}

	if ( is_singular( 'prompt' ) || is_singular( 'collection' ) ) {
		printf( "<meta property=\"article:published_time\" content=\"%s\">\n", esc_attr( (string) get_the_date( DATE_W3C ) ) );
		printf( "<meta property=\"article:modified_time\" content=\"%s\">\n", esc_attr( (string) get_the_modified_date( DATE_W3C ) ) );
	}

	// --- Twitter ---
	printf( "<meta name=\"twitter:card\" content=\"%s\">\n", esc_attr( '' !== $image['url'] ? 'summary_large_image' : 'summary' ) );
	printf( "<meta name=\"twitter:title\" content=\"%s\">\n", esc_attr( $title ) );

	if ( '' !== $description ) {
		printf( "<meta name=\"twitter:description\" content=\"%s\">\n", esc_attr( $description ) );
	}

	if ( '' !== $image['url'] ) {
		printf( "<meta name=\"twitter:image\" content=\"%s\">\n", esc_url( $image['url'] ) );
	}

	/*
	 * Search Console verification. Printed ONLY when the owner has supplied a
	 * real token. No placeholder, no invented value - an invented verification
	 * token is worse than none, because it looks configured while failing.
	 */
	$token = get_option( PROMPTATLAS_OPTION_GSC_TOKEN, '' );

	if ( is_string( $token ) && '' !== trim( $token ) ) {
		printf( "<meta name=\"google-site-verification\" content=\"%s\">\n", esc_attr( trim( $token ) ) );
	}
}
add_action( 'wp_head', 'promptatlas_print_head_meta', 3 );

/**
 * Remove core's canonical, since this file prints its own.
 *
 * Without this there would be two canonical tags whenever the two disagreed,
 * which is exactly the duplicate-tag problem Phase 12 asks to be verified.
 *
 * @return void
 */
function promptatlas_remove_core_canonical() {
	remove_action( 'wp_head', 'rel_canonical' );
}
add_action( 'wp', 'promptatlas_remove_core_canonical' );

// ---------------------------------------------------------------------------
// 4. STRUCTURED DATA
// ---------------------------------------------------------------------------

/**
 * Print JSON-LD structured data.
 *
 * Only types the visible content genuinely supports. There is deliberately no
 * AggregateRating, no Review and no interaction count anywhere: the site has no
 * ratings and no review data, and marking up numbers that do not exist is both
 * a policy violation and the content-integrity rule's central prohibition.
 *
 * @return void
 */
function promptatlas_print_structured_data() {
	$graph = array();

	// WebSite with a search action. Honest: the site genuinely has search at
	// this URL and it genuinely accepts this parameter.
	if ( is_front_page() ) {
		$graph[] = array(
			'@type'           => 'WebSite',
			'@id'             => home_url( '/#website' ),
			'url'             => home_url( '/' ),
			'name'            => get_bloginfo( 'name' ),
			'description'     => get_bloginfo( 'description' ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	// Article for a prompt or collection. The explanation is a real article:
	// original prose with an author and a publication date.
	if ( is_singular( array( 'prompt', 'collection' ) ) ) {
		$image = promptatlas_share_image();

		$article = array(
			'@type'            => 'Article',
			'@id'              => get_permalink() . '#article',
			'headline'         => wp_strip_all_tags( get_the_title() ),
			'url'              => get_permalink(),
			'datePublished'    => get_the_date( DATE_W3C ),
			'dateModified'     => get_the_modified_date( DATE_W3C ),
			'inLanguage'       => get_bloginfo( 'language' ),
			'mainEntityOfPage' => array(
				'@type' => 'WebPage',
				'@id'   => get_permalink(),
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			),
			'author'           => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			),
		);

		$description = promptatlas_meta_description();

		if ( '' !== $description ) {
			$article['description'] = $description;
		}

		if ( '' !== $image['url'] ) {
			$article['image'] = $image['url'];
		}

		$graph[] = $article;
	}

	// ItemList for a collection. It is literally an ordered list of pages, so
	// the markup matches what the visitor sees exactly.
	if ( is_singular( 'collection' ) && function_exists( 'promptatlas_get_collection_prompts' ) ) {
		$prompt_ids = promptatlas_get_collection_prompts( (int) get_the_ID() );

		if ( ! empty( $prompt_ids ) ) {
			$elements = array();
			$position = 1;

			foreach ( $prompt_ids as $prompt_id ) {
				$elements[] = array(
					'@type'    => 'ListItem',
					'position' => $position,
					'url'      => get_permalink( $prompt_id ),
					'name'     => wp_strip_all_tags( get_the_title( $prompt_id ) ),
				);

				++$position;
			}

			$graph[] = array(
				'@type'           => 'ItemList',
				'@id'             => get_permalink() . '#itemlist',
				'itemListOrder'   => 'https://schema.org/ItemListOrderAscending',
				'numberOfItems'   => count( $elements ),
				'itemListElement' => $elements,
			);
		}
	}

	// Breadcrumbs, matching the visible trail from the theme.
	$crumbs = promptatlas_breadcrumb_data();

	if ( count( $crumbs ) > 1 ) {
		$items    = array();
		$position = 1;

		foreach ( $crumbs as $crumb ) {
			$item = array(
				'@type'    => 'ListItem',
				'position' => $position,
				'name'     => $crumb['label'],
			);

			if ( '' !== $crumb['url'] ) {
				$item['item'] = $crumb['url'];
			}

			$items[] = $item;
			++$position;
		}

		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => promptatlas_canonical_url() . '#breadcrumbs',
			'itemListElement' => $items,
		);
	}

	if ( empty( $graph ) ) {
		return;
	}

	$payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	printf(
		"<script type=\"application/ld+json\">%s</script>\n",
		wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'promptatlas_print_structured_data', 4 );

/**
 * Build the breadcrumb trail as data.
 *
 * Shared by the structured data here and the visible breadcrumbs in the theme,
 * so the two cannot drift apart — markup that disagrees with the visible trail
 * is a spam signal, not a technicality.
 *
 * @return array<int, array{label: string, url: string}> Trail, in order.
 */
function promptatlas_breadcrumb_data() {
	$crumbs = array(
		array(
			'label' => __( 'Home', 'promptatlas' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( is_front_page() ) {
		return $crumbs;
	}

	if ( is_singular( 'prompt' ) ) {
		$crumbs[] = array(
			'label' => __( 'Prompts', 'promptatlas' ),
			'url'   => (string) get_post_type_archive_link( 'prompt' ),
		);

		if ( function_exists( 'promptatlas_get_prompt_category' ) ) {
			$term = promptatlas_get_prompt_category( (int) get_the_ID() );

			if ( $term ) {
				$link = get_term_link( $term );

				$crumbs[] = array(
					'label' => $term->name,
					'url'   => is_wp_error( $link ) ? '' : (string) $link,
				);
			}
		}
	} elseif ( is_singular( 'collection' ) ) {
		$crumbs[] = array(
			'label' => __( 'Collections', 'promptatlas' ),
			'url'   => (string) get_post_type_archive_link( 'collection' ),
		);
	} elseif ( is_tax( array( 'prompt_category', 'prompt_tool', 'prompt_tag' ) ) ) {
		$crumbs[] = array(
			'label' => __( 'Prompts', 'promptatlas' ),
			'url'   => (string) get_post_type_archive_link( 'prompt' ),
		);
	}

	$crumbs[] = array(
		'label' => function_exists( 'promptatlas_current_page_label' )
			? promptatlas_current_page_label()
			: wp_strip_all_tags( get_the_title() ),
		'url'   => '',
	);

	return $crumbs;
}

// ---------------------------------------------------------------------------
// 5. SITEMAPS
// ---------------------------------------------------------------------------

/**
 * Keep noindexed content out of the sitemap.
 *
 * A URL that is in the sitemap and also noindexed sends two contradictory
 * instructions. Tag archives are noindex by decision 12, so they must not be
 * submitted.
 *
 * @param array<string, mixed> $args     Query args.
 * @param string               $taxonomy Taxonomy name.
 * @return array<string, mixed> Filtered args.
 */
function promptatlas_sitemap_taxonomies( $args, $taxonomy ) {
	if ( 'prompt_tag' === $taxonomy ) {
		$args['hide_empty'] = true;
	}

	return $args;
}
add_filter( 'wp_sitemaps_taxonomies_query_args', 'promptatlas_sitemap_taxonomies', 10, 2 );

/**
 * Remove the tag taxonomy and unused types from the sitemap entirely.
 *
 * @param array<string, WP_Taxonomy> $taxonomies Taxonomy objects.
 * @return array<string, WP_Taxonomy> Filtered list.
 */
function promptatlas_sitemap_remove_tags( $taxonomies ) {
	unset( $taxonomies['prompt_tag'], $taxonomies['post_tag'], $taxonomies['category'] );

	return $taxonomies;
}
add_filter( 'wp_sitemaps_taxonomies', 'promptatlas_sitemap_remove_tags' );

/**
 * Exclude noindexed pages from the sitemap.
 *
 * The My Prompts and Style Reference pages are noindex by template, so listing
 * them would be the same contradiction.
 *
 * @param array<string, mixed> $args      Query args.
 * @param string               $post_type Post type.
 * @return array<string, mixed> Filtered args.
 */
function promptatlas_sitemap_exclude_pages( $args, $post_type ) {
	if ( 'page' !== $post_type ) {
		return $args;
	}

	$excluded = array();

	foreach ( array( 'my-prompts', 'style-reference' ) as $slug ) {
		$page = get_page_by_path( $slug );

		if ( $page ) {
			$excluded[] = (int) $page->ID;
		}
	}

	if ( ! empty( $excluded ) ) {
		$args['post__not_in'] = array_merge(
			isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(),
			$excluded
		);
	}

	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'promptatlas_sitemap_exclude_pages', 10, 2 );

/**
 * Remove the users sitemap.
 *
 * Author archives are noindex under the policy above, because this is a
 * single-author site and they duplicate the main archive exactly. Submitting
 * them in a sitemap while telling crawlers not to index them is the exact
 * contradiction Phase 12 asks to be checked for.
 *
 * @param array<string, WP_Sitemaps_Provider> $providers Sitemap providers.
 * @return array<string, WP_Sitemaps_Provider> Filtered providers.
 */
function promptatlas_remove_users_sitemap( $providers ) {
	unset( $providers['users'] );

	return $providers;
}
add_filter( 'wp_sitemaps_add_provider', 'promptatlas_sitemap_provider_filter', 10, 2 );

/**
 * Drop the users provider as it is registered.
 *
 * @param WP_Sitemaps_Provider $provider The provider.
 * @param string               $name     Provider name.
 * @return WP_Sitemaps_Provider|false The provider, or false to skip it.
 */
function promptatlas_sitemap_provider_filter( $provider, $name ) {
	if ( 'users' === $name ) {
		return false;
	}

	return $provider;
}

// ---------------------------------------------------------------------------
// 6. CRAWLER POLICY
// ---------------------------------------------------------------------------

/**
 * The AI crawler policy from DECISIONS.md decision 7.
 *
 * Selective: allow crawlers that retrieve and cite with a link, because an
 * assistant recommending a PromptAtlas page is real traffic; block training-only
 * crawlers, because they take the entire product and return nothing.
 *
 * Kept as data in one function precisely because this is a strategic choice
 * that may be reversed - changing the policy means editing these two arrays and
 * nothing else.
 *
 * @return array{blocked: string[], allowed: string[]} User agents by policy.
 */
function promptatlas_crawler_policy() {
	return array(
		// Training-only: no attribution, no referral traffic.
		'blocked' => array(
			'GPTBot',
			'CCBot',
			'Google-Extended',
			'anthropic-ai',
			'ClaudeBot',
			'Applebot-Extended',
			'Meta-ExternalAgent',
			'Bytespider',
			'Omgilibot',
			'Diffbot',
		),
		// Retrieval and citation: these send readers back.
		'allowed' => array(
			'OAI-SearchBot',
			'ChatGPT-User',
			'PerplexityBot',
			'Claude-SearchBot',
			'Claude-User',
		),
	);
}

/**
 * Add the crawler policy to robots.txt.
 *
 * Honest limitation, stated in the file itself: robots.txt is voluntary. It
 * stops well-behaved crawlers and nothing else. It is not a technical
 * protection measure and must not be described as one.
 *
 * When the site is set to discourage search engines, WordPress emits
 * `Disallow: /` and this adds nothing on top - there is no point publishing a
 * nuanced policy while the whole site is closed.
 *
 * @param string $output Existing robots.txt content.
 * @param bool   $is_public Whether the site is public.
 * @return string Filtered content.
 */
function promptatlas_robots_txt( $output, $is_public ) {
	if ( ! $is_public ) {
		return $output . "\n# Search engine visibility is currently discouraged in WordPress settings.\n";
	}

	$policy = promptatlas_crawler_policy();
	$lines  = array( '' );

	$lines[] = '# PromptAtlas AI crawler policy.';
	$lines[] = '# Retrieval crawlers that cite with a link are welcome.';
	$lines[] = '# Training-only crawlers are not: they take the content and return nothing.';
	$lines[] = '# See ' . home_url( '/llms.txt' );
	$lines[] = '# Note: robots.txt is voluntary. It is a request, not a protection measure.';
	$lines[] = '';

	foreach ( $policy['allowed'] as $agent ) {
		$lines[] = 'User-agent: ' . $agent;
		$lines[] = 'Allow: /';
		$lines[] = '';
	}

	foreach ( $policy['blocked'] as $agent ) {
		$lines[] = 'User-agent: ' . $agent;
		$lines[] = 'Disallow: /';
		$lines[] = '';
	}

	// Per-visitor and internal pages: no value in any index.
	$lines[] = 'User-agent: *';
	$lines[] = 'Disallow: /my-prompts/';
	$lines[] = 'Disallow: /style-reference/';
	$lines[] = 'Disallow: /*?s=';
	$lines[] = '';
	$lines[] = 'Sitemap: ' . home_url( '/wp-sitemap.xml' );

	return $output . implode( "\n", $lines ) . "\n";
}
add_filter( 'robots_txt', 'promptatlas_robots_txt', 10, 2 );

// ---------------------------------------------------------------------------
// 7. llms.txt
// ---------------------------------------------------------------------------

/**
 * Register the /llms.txt route.
 *
 * A rewrite rule rather than a real file, so it stays in version control as
 * code and cannot drift from the policy in robots.txt.
 *
 * @return void
 */
function promptatlas_add_llms_route() {
	add_rewrite_rule( '^llms\.txt$', 'index.php?promptatlas_llms=1', 'top' );
}
add_action( 'init', 'promptatlas_add_llms_route', 10 );

/**
 * Register the query variable for the llms.txt route.
 *
 * @param string[] $vars Query variables.
 * @return string[] Filtered variables.
 */
function promptatlas_llms_query_var( $vars ) {
	$vars[] = 'promptatlas_llms';

	return $vars;
}
add_filter( 'query_vars', 'promptatlas_llms_query_var' );

/**
 * Stop WordPress redirecting /llms.txt to /llms.txt/.
 *
 * `redirect_canonical()` adds a trailing slash to any URL it does not recognise
 * as a file, which turned the route into a 301 to a path that then 404s. The
 * URL is deliberately file-like, because that is the convention the file is
 * published under.
 *
 * @param string|false $redirect The URL WordPress wants to redirect to.
 * @return string|false False to cancel the redirect, otherwise unchanged.
 */
function promptatlas_no_canonical_for_llms( $redirect ) {
	if ( get_query_var( 'promptatlas_llms' ) ) {
		return false;
	}

	return $redirect;
}
add_filter( 'redirect_canonical', 'promptatlas_no_canonical_for_llms' );

/**
 * Serve llms.txt.
 *
 * States the policy in the emerging convention, in plain language, and links
 * the real entry points. Contains no claim that it is enforceable.
 *
 * @return void
 */
function promptatlas_serve_llms_txt() {
	if ( ! get_query_var( 'promptatlas_llms' ) ) {
		return;
	}

	$policy = promptatlas_crawler_policy();

	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: noindex' );

	/*
	 * Tags are stripped from the two site-owned values rather than escaped.
	 * This response is text/plain: there is no HTML context, so entity-encoding
	 * is not a safety measure here - it is a rendering bug. Escaping turned the
	 * markdown blockquote marker into "&gt;". Stripping tags removes the only
	 * thing that could matter if this were ever served as HTML.
	 */
	$name    = wp_strip_all_tags( get_bloginfo( 'name' ) );
	$tagline = wp_strip_all_tags( get_bloginfo( 'description' ) );

	$lines = array(
		'# ' . $name,
		'',
		'> ' . $tagline,
		'',
		$name . ' is a library of AI prompts. Every prompt is written by hand and',
		'published with an original explanation of what it does, what to change in it,',
		'and why it is built the way it is. That explanation is the work.',
		'',
		'## Using this content',
		'',
		'You are welcome to read these pages and to cite them with a link.',
		'If you answer a question using a prompt from this site, please link to the page',
		'it came from so the reader can see the explanation that goes with it.',
		'',
		'Please do not use this content to train a model. Crawlers that exist only to',
		'collect training data are disallowed in robots.txt.',
		'',
		'Retrieval and citation crawlers allowed: ' . implode( ', ', $policy['allowed'] ) . '.',
		'Training-only crawlers disallowed: ' . implode( ', ', $policy['blocked'] ) . '.',
		'',
		'## Main entry points',
		'',
		'- [All prompts](' . get_post_type_archive_link( 'prompt' ) . '): every published prompt.',
		'- [Collections](' . get_post_type_archive_link( 'collection' ) . '): curated sets for a particular job.',
		'- [Sitemap](' . home_url( '/wp-sitemap.xml' ) . '): the full URL list.',
		'',
		'## Note',
		'',
		'robots.txt and this file are requests, not technical protection measures.',
		'They describe what we would like. They do not prevent anything.',
		'',
	);

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- text/plain response. Every dynamic value is a site option passed through wp_strip_all_tags() or a URL built by WordPress. HTML-escaping here is not a safety measure, it is a rendering bug: it turned the markdown blockquote marker into "&gt;".
	echo implode( "\n", $lines );
	exit;
}
add_action( 'template_redirect', 'promptatlas_serve_llms_txt' );

// ---------------------------------------------------------------------------
// 8. SEARCH CONSOLE PREPARATION
// ---------------------------------------------------------------------------

/**
 * Register the verification token setting.
 *
 * The integration point only. The token itself is supplied by the owner in
 * Phase 18 — nothing is invented here, and the meta tag is printed only when a
 * real value exists. An invented verification token is worse than none: it
 * looks configured while silently failing.
 *
 * @return void
 */
function promptatlas_register_seo_settings() {
	register_setting(
		'promptatlas_seo',
		PROMPTATLAS_OPTION_GSC_TOKEN,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'promptatlas_sanitize_gsc_token',
			'default'           => '',
			'show_in_rest'      => false,
		)
	);
}
add_action( 'admin_init', 'promptatlas_register_seo_settings' );

/**
 * Sanitise the verification token.
 *
 * Google's token is an opaque base64url-ish string. Anything else is a paste
 * mistake — usually the whole meta tag — so the content attribute is extracted
 * when that happens rather than storing markup.
 *
 * @param mixed $value Raw value.
 * @return string Clean token.
 */
function promptatlas_sanitize_gsc_token( $value ) {
	if ( ! is_string( $value ) ) {
		return '';
	}

	$value = trim( $value );

	// Someone pasting the whole <meta> tag is the common case, not an attack.
	if ( preg_match( '/content=["\']([^"\']+)["\']/', $value, $matches ) ) {
		$value = $matches[1];
	}

	$value = preg_replace( '/[^A-Za-z0-9_\-]/', '', $value );

	return (string) $value;
}

/**
 * Register the SEO settings page.
 *
 * @return void
 */
function promptatlas_register_seo_page() {
	add_submenu_page(
		'edit.php?post_type=prompt',
		__( 'SEO and verification', 'promptatlas' ),
		__( 'SEO', 'promptatlas' ),
		'manage_options',
		'promptatlas-seo',
		'promptatlas_render_seo_page'
	);
}
add_action( 'admin_menu', 'promptatlas_register_seo_page' );

/**
 * Render the SEO settings page.
 *
 * @return void
 */
function promptatlas_render_seo_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to view this page.', 'promptatlas' ) );
	}

	$token  = get_option( PROMPTATLAS_OPTION_GSC_TOKEN, '' );
	$public = (bool) get_option( 'blog_public' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'SEO and verification', 'promptatlas' ); ?></h1>

		<?php if ( ! $public ) : ?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Search engines are currently discouraged.', 'promptatlas' ); ?></strong>
					<?php esc_html_e( 'Every page is noindex and robots.txt disallows everything. This is correct during development — turn it on at Settings → Reading before launch.', 'promptatlas' ); ?>
				</p>
			</div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'promptatlas_seo' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="promptatlas-gsc"><?php esc_html_e( 'Search Console token', 'promptatlas' ); ?></label>
					</th>
					<td>
						<input
							type="text"
							id="promptatlas-gsc"
							name="<?php echo esc_attr( PROMPTATLAS_OPTION_GSC_TOKEN ); ?>"
							value="<?php echo esc_attr( is_string( $token ) ? $token : '' ); ?>"
							class="regular-text code"
						>
						<p class="description">
							<?php esc_html_e( 'From Google Search Console, the HTML tag verification method. Paste the token or the whole meta tag — the token is extracted either way. The verification tag is only added to the site once this has a value.', 'promptatlas' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>

		<h2><?php esc_html_e( 'What this site already does', 'promptatlas' ); ?></h2>
		<ul class="ul-disc">
			<li><?php esc_html_e( 'Titles, meta descriptions, canonical URLs, Open Graph and Twitter cards on every page.', 'promptatlas' ); ?></li>
			<li>
				<?php
				printf(
					/* translators: %s: sitemap URL. */
					esc_html__( 'XML sitemap at %s, excluding anything marked noindex.', 'promptatlas' ),
					'<a href="' . esc_url( home_url( '/wp-sitemap.xml' ) ) . '">' . esc_html( home_url( '/wp-sitemap.xml' ) ) . '</a>'
				);
				?>
			</li>
			<li>
				<?php
				printf(
					/* translators: %s: llms.txt URL. */
					esc_html__( 'AI crawler policy in robots.txt and %s.', 'promptatlas' ),
					'<a href="' . esc_url( home_url( '/llms.txt' ) ) . '">' . esc_html( home_url( '/llms.txt' ) ) . '</a>'
				);
				?>
			</li>
			<li><?php esc_html_e( 'Breadcrumb, Article and ItemList structured data. No ratings or review markup, because there is no rating data.', 'promptatlas' ); ?></li>
		</ul>
	</div>
	<?php
}
