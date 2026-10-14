<?php
/**
 * Seed the taxonomy terms frozen in Phase 04.
 *
 * Terms only. No placeholder prompt content is created: fabricated content is
 * forbidden by the content-integrity rule, and a seeded "example prompt" would
 * be exactly that.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * The eight prompt categories from CONTENT_STRATEGY.md 3.3.
 *
 * Descriptions are the editor-facing "what belongs here" guidance, so the rule
 * lives where the person assigning the category will actually read it.
 *
 * @return array<string, array{name: string, description: string}> Slug-keyed terms.
 */
function promptatlas_category_terms() {
	return array(
		'writing-editing'       => array(
			'name'        => __( 'Writing & Editing', 'promptatlas' ),
			'description' => __( 'Drafting, rewriting, summarising, tone changes, proofreading and long-form structure. Not marketing copy written to sell, and not code documentation.', 'promptatlas' ),
		),
		'marketing-content'     => array(
			'name'        => __( 'Marketing & Content', 'promptatlas' ),
			'description' => __( 'Ad copy, social posts, email campaigns, SEO briefs, landing-page copy and content calendars. Not general business strategy, and not image generation.', 'promptatlas' ),
		),
		'business-strategy'     => array(
			'name'        => __( 'Business & Strategy', 'promptatlas' ),
			'description' => __( 'Plans, proposals, pricing, competitor and SWOT analysis, meeting and decision documents, and client communication. Not copy written to sell.', 'promptatlas' ),
		),
		'coding-development'    => array(
			'name'        => __( 'Coding & Development', 'promptatlas' ),
			'description' => __( 'Code generation, debugging, refactoring, code review, tests, documentation, SQL, regex and shell. Not non-technical data analysis.', 'promptatlas' ),
		),
		'career-job-search'     => array(
			'name'        => __( 'Career & Job Search', 'promptatlas' ),
			'description' => __( 'CVs and résumés, cover letters, LinkedIn profiles, interview preparation, salary negotiation and outreach. Not running a business, and not studying a subject.', 'promptatlas' ),
		),
		'education-study'       => array(
			'name'        => __( 'Education & Study', 'promptatlas' ),
			'description' => __( 'Explaining concepts, study plans, practice questions, lesson plans, feedback on student work and research summaries. Not career applications.', 'promptatlas' ),
		),
		'image-video'           => array(
			'name'        => __( 'Image & Video', 'promptatlas' ),
			'description' => __( 'Image generation, editing instructions, video and shot prompts, style and lighting direction, and thumbnails. Not written marketing copy.', 'promptatlas' ),
		),
		'productivity-personal' => array(
			'name'        => __( 'Productivity & Personal', 'promptatlas' ),
			'description' => __( 'Planning, prioritising, email and inbox handling, note-taking systems, habits and personal decisions. Not team or business process.', 'promptatlas' ),
		),
	);
}

/**
 * The initial AI tool terms from CONTENT_STRATEGY.md 3.4.
 *
 * Product families only, never version numbers: a version-specific term is
 * guaranteed to go stale and would multiply without end.
 *
 * @return array<string, array{name: string, description: string}> Slug-keyed terms.
 */
function promptatlas_tool_terms() {
	return array(
		'chatgpt'          => array(
			'name'        => __( 'ChatGPT', 'promptatlas' ),
			'description' => __( 'Prompts written for OpenAI ChatGPT.', 'promptatlas' ),
		),
		'claude'           => array(
			'name'        => __( 'Claude', 'promptatlas' ),
			'description' => __( 'Prompts written for Anthropic Claude.', 'promptatlas' ),
		),
		'gemini'           => array(
			'name'        => __( 'Gemini', 'promptatlas' ),
			'description' => __( 'Prompts written for Google Gemini.', 'promptatlas' ),
		),
		'midjourney'       => array(
			'name'        => __( 'Midjourney', 'promptatlas' ),
			'description' => __( 'Image prompts written for Midjourney.', 'promptatlas' ),
		),
		'sora'             => array(
			'name'        => __( 'Sora', 'promptatlas' ),
			'description' => __( 'Video prompts written for OpenAI Sora.', 'promptatlas' ),
		),
		'stable-diffusion' => array(
			'name'        => __( 'Stable Diffusion', 'promptatlas' ),
			'description' => __( 'Image prompts written for Stable Diffusion.', 'promptatlas' ),
		),
		'github-copilot'   => array(
			'name'        => __( 'GitHub Copilot', 'promptatlas' ),
			'description' => __( 'Coding prompts written for GitHub Copilot.', 'promptatlas' ),
		),
		'perplexity'       => array(
			'name'        => __( 'Perplexity', 'promptatlas' ),
			'description' => __( 'Research prompts written for Perplexity.', 'promptatlas' ),
		),
	);
}

/**
 * Create the seed terms if they do not already exist.
 *
 * Idempotent: an existing term is left exactly as it is, including any edit the
 * owner has made to its name or description. Overwriting their wording on every
 * activation would be destructive.
 *
 * @return array{created: int, skipped: int} Counts, for reporting.
 */
function promptatlas_seed_terms() {
	$created = 0;
	$skipped = 0;

	$sets = array(
		'prompt_category' => promptatlas_category_terms(),
		'prompt_tool'     => promptatlas_tool_terms(),
	);

	foreach ( $sets as $taxonomy => $terms ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		foreach ( $terms as $slug => $term ) {
			if ( term_exists( $slug, $taxonomy ) ) {
				++$skipped;
				continue;
			}

			$result = wp_insert_term(
				$term['name'],
				$taxonomy,
				array(
					'slug'        => $slug,
					'description' => $term['description'],
				)
			);

			if ( is_wp_error( $result ) ) {
				++$skipped;
				continue;
			}

			++$created;
		}
	}

	return array(
		'created' => $created,
		'skipped' => $skipped,
	);
}
