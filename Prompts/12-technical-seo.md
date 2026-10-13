# PHASE 12 — TECHNICAL SEO, SOCIAL METADATA AND CRAWLER POLICY

**Prerequisites:** Phase 01 (plugin stack and AI crawler decisions), Phase 08 and Phase 11 complete.
**Produces:** complete, non-duplicated SEO metadata, sitemaps, robots behavior, social cards, and structured data.
**Do not:** invent verification codes, publisher IDs, or analytics IDs. Phase 18 handles accounts.

Inspect the current WordPress configuration, theme, URLs, and content model before changing SEO behavior.

## Goal

Make PromptAtlas technically sound for organic discovery without producing low-quality or duplicate pages.

## Ownership of metadata

Phase 01 decided whether an SEO plugin, the theme, or WordPress native features own the metadata. Implement that decision and do not produce a second conflicting implementation.

If a plugin owns metadata, the theme must not output its own titles, descriptions, canonicals, or Open Graph tags. Verify by inspecting the generated HTML that each tag appears exactly once.

## Core requirements

Implement or verify:
- Descriptive page titles with a consistent, documented pattern per template
- Unique meta descriptions where appropriate, with an honest fallback
- Canonical URLs
- Correct HTTP status codes
- XML sitemap generation covering prompts, collections, taxonomy archives, and pages
- Robots.txt behavior
- Logical heading hierarchy on every template
- Internal links, per Phase 11
- Breadcrumbs, with matching structured data
- Image alt text support in the admin, and an enforced path for the team to fill it
- Clean permalink structures matching the Phase 04 frozen URLs
- Pagination behavior and its canonical handling
- 404 handling
- A redirect mechanism for changed URLs

Do not hardcode sitemap URLs or canonical URLs in a way that breaks when the domain changes between local, staging, and production. Verify this explicitly, since Phase 22 will move the site.

## Indexation policy

Decide, document, and implement an explicit policy for every page type. State the reasoning for each.

At minimum, rule on:
- Search result pages
- Paginated archive pages beyond page one
- Tag archives, which are the usual source of thin pages
- Empty taxonomy archives
- The Recently Copied and Saved pages from Phase 10, which are per-visitor and must not be indexed
- The Phase 07 style reference page, if it remains in the theme
- Attachment and author pages
- Any filtered or query-parameter URL combination

Do not index arbitrary query combinations. Do not mass-generate empty category pages.

## Social metadata

This was missing from the original plan.

Implement, unless a Phase 01 plugin already owns them:
- Open Graph title, description, URL, type, site name, and image
- Twitter card tags
- Correct image dimensions, with the Phase 07 default share image as the fallback

Verify the rendered tags on a prompt, a collection, a category archive, and the homepage. Confirm each tag appears once and the image URL resolves.

## Structured data

Add structured data only when the visible content genuinely qualifies. Use an appropriate type, valid required properties, and no fabricated ratings, reviews, or aggregate scores.

Breadcrumb structured data is appropriate. Review article or item-list markup only where it honestly matches what is on the page. Validate the output with whatever tooling is available and report the real validator results.

## Crawler policy

Implement the Phase 01 decision on AI and LLM crawlers in robots.txt, and publish an `llms.txt` if that was the decision.

Make the policy easy to reverse, since this is a strategic choice that may change. Document it in `DECISIONS.md` with the reasoning.

## Content quality support

Create editorial templates and admin guidance that help the team write unique, useful prompt descriptions and collection introductions. Phase 14 builds on this.

Do not create thin pages to target keyword variations.

## Search Console and analytics preparation

Prepare the integration points for Google Search Console and privacy-appropriate analytics, with the real IDs supplied later in Phase 18.

Do not invent verification codes or claim an account is connected. State exactly which owner-supplied token or property is still required, and record it in `LAUNCH_CHECKLIST.md`.

## Verification

Inspect the actual generated HTML for the homepage, a single prompt, a collection, a category archive, a tag archive, page two of an archive, a search result page, and a 404. For each, report the title, meta description, canonical, robots directive, and Open Graph tags you actually observed.

Verify the sitemap output, the robots.txt output, and that no URL is both canonicalized and noindexed in a conflicting way.

## Completion report

Report what is configured, which page types are indexable and why, the duplicate-tag check results, the structured data validation output, and what must be completed only after deployment.
