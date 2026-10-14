# PHASE 04 — INFORMATION ARCHITECTURE AND CONTENT STRATEGY

**Prerequisites:** Phase 01 and Phase 03 complete.
**Produces:** `CONTENT_STRATEGY.md`, the taxonomy definition, and the URL architecture.
**Do not:** write any code in this phase. This is a planning phase whose output drives Phases 05, 08, 11, 12 and 19.

## Why this phase exists

PromptAtlas is an organic-search business. Everything downstream — the post type, the taxonomies, the URL structure, the navigation, the templates, the internal linking, and the editorial calendar — depends on knowing what topics the site covers and how they relate. Building the content model before deciding the content strategy produces a taxonomy that has to be migrated later, and taxonomy migrations break URLs.

## Step 1: Understand the landscape

Research how people actually search for AI prompts. Identify:
- The dominant query patterns and their rough intent
- Which query types are served by a single prompt page and which are served by a curated list
- What the established prompt sites rank for, and where the gaps are
- How query behaviour differs for the audience and geography chosen in Phase 01

Use the search and research tools available to you. Where you are relying on general knowledge rather than a verified source, say so. Do not present estimated search volumes as measured data, and do not invent numbers.

## Step 2: Define the taxonomy

This is the core deliverable. Define the actual, specific taxonomy — not the instruction to create one.

Produce:
- The complete list of top-level prompt categories, each with a one-line definition of what belongs in it and what does not
- Whether the category taxonomy is flat or hierarchical, and how deep
- The tag strategy: what tags are for, who creates them, and how tag sprawl is prevented
- Whether a separate AI tool or model taxonomy is justified, and if so its initial terms
- Which taxonomy terms will have indexable archive pages and which will not

Keep the initial category count small enough that each category can realistically be filled with useful content. A category with two prompts in it is a thin page, and thin pages are an AdSense and ranking liability.

## Step 3: Define the URL architecture

Decide and document the final URL shape for:
- Single prompts
- Prompt archive
- Category archives
- Tag archives
- Collections and hub pages
- Paginated archives
- Search results
- Standard pages

Choose deliberately between a prefixed structure and a flat one. Check for collisions between the post type slug, taxonomy slugs, and existing page slugs. These URLs are expensive to change after launch, so state explicitly that they are now frozen and that any later change requires a redirect plan.

## Step 4: Define the content model requirements

For the Phase 05 implementation, specify what each prompt actually needs:
- Which fields are required and which are optional
- What the title convention is
- What the description must contain and how long it should be
- Whether prompt placeholder variables are part of the model
- Whether a last-updated date is displayed, and why that matters for freshness
- What the related-prompts relationship is based on

## Step 5: Define the collection and hub strategy

List-format pages are typically the highest-traffic pages on a prompt site, and they are also the main internal-linking mechanism. Specify:
- What a collection is, and how it differs from a category archive
- Whether collections are a post type, a taxonomy, or curated pages
- The first set of collections worth building
- How collections link to prompts and how prompts link back

Phase 11 implements this. This phase decides it.

## Step 6: Define the editorial plan

Specify:
- The publishing rate target and where it comes from
- The topic clusters to work through, in priority order
- The first batch of prompts to publish in Phase 19, by topic
- How originality is verified before publishing
- How zero-result searches will feed back into the plan once Phase 18 analytics exist

Do not propose mass-producing thin pages for keyword variations.

## Deliverables

Write all of the above into `CONTENT_STRATEGY.md`. Record the taxonomy and URL decisions in `DECISIONS.md` as frozen decisions. Update `PROGRESS.md`.

## Completion criteria

The deliverable is specific enough that Phase 05 can register the post type and taxonomies without making a single naming decision of its own. If it is not that specific, it is not finished.
