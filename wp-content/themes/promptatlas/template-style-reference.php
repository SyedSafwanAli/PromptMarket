<?php
/**
 * Template Name: Style reference (internal)
 *
 * Renders every design token and component in one place so the system can be
 * reviewed and regression-checked after a change.
 *
 * STATUS: this template STAYS in the theme. It is the only practical way to
 * regression-check the design system, and Phase 24 needs it. It is kept out of
 * search engines by emitting `noindex, nofollow` below — not by obscurity.
 *
 * To use it: create a page and assign the "Style reference (internal)"
 * template. Delete the page to remove it from the site; the template file is
 * harmless on its own because WordPress only renders it for an assigned page.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Keep the style reference out of search engines and out of the sitemap.
 *
 * @return void
 */
function promptatlas_style_reference_noindex() {
	echo '<meta name="robots" content="noindex, nofollow">' . "\n";
}
add_action( 'wp_head', 'promptatlas_style_reference_noindex', 1 );

/**
 * Render a labelled swatch for a colour token.
 *
 * @param string $token    CSS custom property name, without `var()`.
 * @param string $label    Human label.
 * @param string $measured Measured contrast note, or an empty string.
 * @return void
 */
function promptatlas_swatch( $token, $label, $measured = '' ) {
	?>
	<div class="sr-swatch">
		<div class="sr-swatch__chip" style="background: var(<?php echo esc_attr( $token ); ?>);"></div>
		<div class="sr-swatch__meta">
			<code class="sr-swatch__token"><?php echo esc_html( $token ); ?></code>
			<span class="sr-swatch__label"><?php echo esc_html( $label ); ?></span>
			<?php if ( '' !== $measured ) : ?>
				<span class="sr-swatch__ratio"><?php echo esc_html( $measured ); ?></span>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

get_header();
?>

<main id="primary" class="site-main container" role="main">

	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Style reference', 'promptatlas' ); ?></h1>
		<p class="page-header__description">
			<?php esc_html_e( 'Internal reference for the PromptAtlas design system. Not indexed. Use the theme toggle in the header to check every component in both themes.', 'promptatlas' ); ?>
		</p>
	</header>

	<section class="sr-section">
		<h2><?php esc_html_e( 'Colour tokens', 'promptatlas' ); ?></h2>
		<p><?php esc_html_e( 'Semantic tokens only. Components never use the raw brand scale. Contrast figures are computed, not estimated — see DESIGN_SYSTEM.md.', 'promptatlas' ); ?></p>

		<h3><?php esc_html_e( 'Surfaces', 'promptatlas' ); ?></h3>
		<div class="sr-swatches">
			<?php
			promptatlas_swatch( '--color-bg', 'Page background' );
			promptatlas_swatch( '--color-surface', 'Raised surface' );
			promptatlas_swatch( '--color-surface-sunken', 'Sunken surface' );
			promptatlas_swatch( '--color-code-bg', 'Prompt block' );
			?>
		</div>

		<h3><?php esc_html_e( 'Text', 'promptatlas' ); ?></h3>
		<div class="sr-swatches">
			<?php
			promptatlas_swatch( '--color-text', 'Body text', '16.42 : 1 light / 15.64 : 1 dark' );
			promptatlas_swatch( '--color-text-muted', 'Muted text', '6.96 : 1 light / 8.38 : 1 dark' );
			promptatlas_swatch( '--color-text-faint', 'Faint (large text only)', '4.42 : 1 light / 5.42 : 1 dark' );
			?>
		</div>

		<h3><?php esc_html_e( 'Brand and action', 'promptatlas' ); ?></h3>
		<div class="sr-swatches">
			<?php
			promptatlas_swatch( '--color-primary', 'Primary (burgundy)', '8.38 : 1 light / 8.20 : 1 dark' );
			promptatlas_swatch( '--color-accent', 'Accent (orange) — background only', 'dark text on it: 5.46 : 1' );
			promptatlas_swatch( '--color-accent-text', 'Accent as text', '7.35 : 1 light' );
			promptatlas_swatch( '--color-success', 'Success (olive)' );
			promptatlas_swatch( '--color-highlight', 'Highlight (butter)' );
			promptatlas_swatch( '--color-danger', 'Danger' );
			?>
		</div>

		<h3><?php esc_html_e( 'Lines', 'promptatlas' ); ?></h3>
		<div class="sr-swatches">
			<?php
			promptatlas_swatch( '--color-border', 'Decorative hairline — never on a control' );
			promptatlas_swatch( '--color-border-control', 'Control boundary', '3.64 : 1 light / 3.96 : 1 dark' );
			promptatlas_swatch( '--color-focus', 'Focus ring', '4.33 : 1 light / 8.67 : 1 dark' );
			?>
		</div>
	</section>

	<section class="sr-section">
		<h2><?php esc_html_e( 'Typography', 'promptatlas' ); ?></h2>
		<p style="font-size: var(--text-3xl); line-height: var(--leading-tight);">--text-3xl — Page titles</p>
		<p style="font-size: var(--text-2xl); line-height: var(--leading-tight);">--text-2xl — Section headings</p>
		<p style="font-size: var(--text-xl);">--text-xl — Subheadings</p>
		<p style="font-size: var(--text-lg);">--text-lg — Lead paragraphs</p>
		<p style="font-size: var(--text-md);">--text-md — Body copy, the default</p>
		<p style="font-size: var(--text-sm);">--text-sm — Secondary text and metadata</p>
		<p style="font-size: var(--text-xs);">--text-xs — Eyebrows, pills, counts</p>
		<p style="font-family: var(--font-mono);">--font-mono — Prompt text and placeholder names</p>
	</section>

	<section class="sr-section">
		<h2><?php esc_html_e( 'Buttons', 'promptatlas' ); ?></h2>
		<p class="sr-row">
			<a href="#0" class="button button--primary">Primary action</a>
			<a href="#0" class="button button--accent">Accent action</a>
			<a href="#0" class="button button--secondary">Secondary</a>
			<a href="#0" class="button button--quiet">Quiet</a>
			<button type="button" class="button button--primary" disabled>Disabled</button>
		</p>
		<p><?php esc_html_e( 'The accent button carries dark text. White on raw orange measures 3.14 : 1 and fails AA.', 'promptatlas' ); ?></p>
	</section>

	<section class="sr-section">
		<h2><?php esc_html_e( 'Form controls', 'promptatlas' ); ?></h2>
		<p>
			<label for="sr-text">Text input</label>
			<input type="text" id="sr-text" placeholder="Placeholder text">
			<span class="form-hint">Hint text explaining the field.</span>
		</p>
		<p>
			<label for="sr-textarea">Textarea</label>
			<textarea id="sr-textarea" placeholder="Longer input…"></textarea>
		</p>
		<p><?php esc_html_e( 'Search form:', 'promptatlas' ); ?></p>
		<?php get_search_form(); ?>
	</section>

	<section class="sr-section">
		<h2><?php esc_html_e( 'Prompt presentation', 'promptatlas' ); ?></h2>
		<div class="prompt-block">
			<div class="prompt-block__header">
				<span class="prompt-block__label"><?php esc_html_e( 'Prompt', 'promptatlas' ); ?></span>
				<button type="button" class="copy-button"><?php esc_html_e( 'Copy', 'promptatlas' ); ?></button>
			</div>
			<pre class="prompt-block__text">You are an experienced technical recruiter who screens résumés for <span class="prompt-token">[JOB_TITLE]</span> roles.

Rewrite my résumé so that it speaks directly to this job description. Keep it to <span class="prompt-token">[PAGE_COUNT]</span> page(s).</pre>
		</div>

		<h3><?php esc_html_e( 'Placeholder variables', 'promptatlas' ); ?></h3>
		<ul class="variable-list">
			<li class="variable-list__item">
				<span class="variable-list__name">[JOB_TITLE]</span>
				<p class="variable-list__description">The exact job title from the advert.</p>
			</li>
			<li class="variable-list__item">
				<span class="variable-list__name">[PAGE_COUNT]</span>
				<p class="variable-list__description">Usually 1, or 2 for ten or more years of experience.</p>
			</li>
		</ul>
	</section>

	<section class="sr-section">
		<h2><?php esc_html_e( 'Terms and navigation', 'promptatlas' ); ?></h2>
		<p class="sr-row">
			<a href="#0" class="category-link">Career &amp; Job Search</a>
		</p>
		<ul class="term-list">
			<li><a href="#0" class="term-pill">resume</a></li>
			<li><a href="#0" class="term-pill">cold-email</a></li>
			<li><span class="term-pill term-pill--tool">ChatGPT</span></li>
			<li><span class="term-pill term-pill--tool">Claude</span></li>
		</ul>

		<nav class="breadcrumbs" aria-label="Breadcrumb example">
			<ol class="breadcrumbs__list">
				<li class="breadcrumbs__item"><a href="#0">Home</a></li>
				<li class="breadcrumbs__item"><a href="#0">Prompts</a></li>
				<li class="breadcrumbs__item breadcrumbs__item--current" aria-current="page">Career &amp; Job Search</li>
			</ol>
		</nav>
	</section>

	<section class="sr-section">
		<h2><?php esc_html_e( 'Messages', 'promptatlas' ); ?></h2>
		<p class="notice-message notice-message--success">Your message has been sent.</p>
		<p class="notice-message notice-message--error">That email address does not look right.</p>
	</section>

	<section class="sr-section">
		<h2><?php esc_html_e( 'Ad slot', 'promptatlas' ); ?></h2>
		<p><?php esc_html_e( 'Reserved and labelled, with a min-height so a loading unit cannot shift the layout. Never placed adjacent to the copy control.', 'promptatlas' ); ?></p>
		<div class="ad-slot ad-slot--leaderboard">
			<span class="ad-slot__label"><?php esc_html_e( 'Advertisement', 'promptatlas' ); ?></span>
		</div>
	</section>

	<section class="sr-section">
		<h2><?php esc_html_e( 'Empty state', 'promptatlas' ); ?></h2>
		<?php
		promptatlas_empty_state(
			__( 'No prompts here yet', 'promptatlas' ),
			__( 'Nothing has been published in this section so far.', 'promptatlas' )
		);
		?>
	</section>

	<section class="sr-section">
		<h2><?php esc_html_e( 'Brand assets', 'promptatlas' ); ?></h2>
		<p class="sr-row" style="align-items: center;">
			<?php promptatlas_mark( 24 ); ?>
			<?php promptatlas_mark( 48 ); ?>
			<?php promptatlas_mark( 96 ); ?>
		</p>
		<p>
			<img
				src="<?php echo esc_url( promptatlas_default_share_image() ); ?>"
				alt="<?php esc_attr_e( 'Default Open Graph share image', 'promptatlas' ); ?>"
				width="600"
				height="315"
				loading="lazy"
			>
		</p>
	</section>

</main>

<style>
/* Style-reference-only layout. Scoped here rather than shipped in
	components.css, because none of it is part of the product. */
.sr-section {
	max-width: var(--width-content);
	margin-block: var(--space-2xl);
	padding-block-start: var(--space-lg);
	border-block-start: var(--border-width) solid var(--color-border);
}
.sr-row {
	display: flex;
	flex-wrap: wrap;
	gap: var(--space-sm);
	align-items: baseline;
}
.sr-swatches {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr));
	gap: var(--space-md);
	margin-block: var(--space-md);
}
.sr-swatch {
	display: flex;
	gap: var(--space-sm);
	align-items: flex-start;
}
.sr-swatch__chip {
	width: 2.75rem;
	height: 2.75rem;
	flex: 0 0 auto;
	border: var(--border-width) solid var(--color-border-control);
	border-radius: var(--radius-md);
}
.sr-swatch__meta {
	display: grid;
	gap: 2px;
	font-size: var(--text-xs);
}
.sr-swatch__token {
	font-size: var(--text-xs);
	color: var(--color-text);
}
.sr-swatch__label { color: var(--color-text-muted); }
.sr-swatch__ratio { color: var(--color-success-text); }
</style>

<?php
get_footer();
