# PHASE 08 — FRONTEND TEMPLATES

**Prerequisites:** Phase 05 complete (the `prompt` post type exists) and Phase 07 complete (the design system exists).
**Produces:** every public-facing template, rendering real WordPress content.
**Do not:** implement search logic (Phase 09), copy behavior (Phase 10), or collections (Phase 11). Build their interfaces only where this phase requires them, and document anything visually present but not yet functional.

Inspect the design system, the content model, and the current theme before making changes.

Build the production-quality visual frontend for PromptAtlas using the existing theme and the Phase 07 tokens. The website must look intentional and distinctive on desktop and mobile.

## Homepage

Create:
1. Responsive site header with the Phase 07 wordmark or logo
2. Accessible primary navigation reflecting the Phase 04 architecture
3. A clear editorial hero with a strong headline and short explanation, written in the Phase 07 brand voice
4. A prominent search form posting to the real WordPress search endpoint
5. Useful discovery categories, driven by the real Phase 05 taxonomy terms
6. A featured prompts section driven by WordPress content
7. A recent prompts section driven by WordPress content
8. Helpful explanatory content for first-time visitors
9. A clear footer with About, Contact, Privacy Policy, Terms, and DMCA links

Phase 17 creates those policy pages. Build the footer so the links appear only when the pages exist, rather than rendering dead links now.

Do not fabricate prompt records, ratings, user counts, copy totals, reviews, or trending statistics. If no prompts exist, create a polished empty state that directs the site owner to publish content.

## Templates

Implement or refine:
- Homepage
- Prompt archive and listing
- Single prompt detail
- Category archive
- Tag archive
- AI tool or model archive, if Phase 04 defined that taxonomy
- Search results
- Standard content page
- 404 page
- Pagination

Use the WordPress template hierarchy and reusable template parts. Do not duplicate entire layouts across templates. The prompt card is a single template part used everywhere a prompt is listed.

## Prompt presentation

Prompt listings should communicate the prompt title, a short real excerpt, relevant categories and tags, and clear actions where those actions actually work.

Prompt detail pages should support a readable title, a useful description, the full prompt content, relevant metadata, a last-updated indicator if Phase 04 called for one, and related content when available. Implement the related-prompts relationship exactly as Phase 04 defined it, with an honest fallback when there are no related prompts.

Long prompts must wrap properly and be easy to read and copy. Preserve whitespace and line breaks in prompt content, since prompts are often multi-line and formatting is meaningful. Avoid tiny text and excessive nested containers.

## Search interface

Create the visual search interface and its accessible states, posting to the existing WordPress search endpoint rather than displaying fake results.

Provide:
- Empty query state
- Results state
- No-results state, with a genuinely useful next action
- Keyboard focus state
- Loading feedback only when an actual asynchronous operation is happening
- Clear button labels and error messages

## Query correctness

Use WordPress queries correctly. Avoid N+1 patterns. Paginate every listing and never load all prompts on one page. Do not run queries inside repeated loops. Note the query count per template so Phase 15 has a baseline.

Ensure missing metadata, missing featured images, and missing taxonomy terms never break a template. Use honest, meaningful fallbacks rather than fabricated content.

## Quality requirements

- Mobile-first responsive design
- Semantic HTML
- Accessible contrast and focus
- Reduced-motion support
- No decorative gradients, glassmorphism, neon effects, or excessive shadows
- No unnecessary third-party dependencies
- No fake loading delays or simulated features
- Explicit width and height on every image

## Verification

Test every template against real WordPress content, and then against the hard cases:
- No published prompts at all
- An empty category
- A very long title
- A very long multi-line prompt body
- Special characters and non-Latin text
- A prompt with no featured image, no excerpt, and no tags
- A search with no results
- Page two of a paginated archive
- A 404 URL

Run the Phase 03 syntax and standards checks. Load every template with debugging enabled and report every notice.

## Completion report

Report the templates created, the template parts shared, the per-template query counts, the edge cases tested with their real results, and a clear list of anything visually present but not yet functional.
