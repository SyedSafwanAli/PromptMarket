# PHASE 11 — COLLECTIONS AND HUB PAGES

**Prerequisites:** Phase 04 (the collection strategy), Phase 05, and Phase 08 complete.
**Produces:** the collection content type or mechanism, its templates, and the internal linking structure.
**Do not:** publish the actual collection content here. Phase 19 writes the content; this phase builds the machinery and one real example.

## Why this phase exists

On a prompt site, list-format pages are usually the highest-traffic pages and the primary internal-linking mechanism. A site made only of individual prompt pages and automatic taxonomy archives has no editorial surface, no way to rank for list-intent queries, and no structure to distribute authority through.

This was missing from the original plan and it matters more than most of the features that were in it.

## Step 1: Implement the mechanism Phase 04 chose

Phase 04 decided whether a collection is a post type, a taxonomy, or a curated page. Implement that decision. Do not revisit it here unless you find a concrete technical blocker, in which case stop and report rather than quietly choosing differently.

Whatever the mechanism, the editor must be able to:
- Give the collection a title, an original introduction, and a description
- Choose which prompts belong in it, in a deliberate order
- Reorder the prompts without re-creating the collection
- Add commentary for individual entries where that adds value
- Save as draft, preview, schedule, and publish

Prompt ordering must be editorial, not just chronological. The ordering is the editorial value.

## Step 2: Build the collection templates

Create:
- A single collection template
- A collection index or archive listing every published collection
- Collection entry points on the homepage and in the navigation, per Phase 04

A collection page must present real substance: an original introduction that justifies the grouping, each prompt with enough context to be useful, and working copy controls consistent with Phase 10.

Do not render a collection as a bare list of links. A thin list page with no original writing is exactly the kind of page that fails both ranking and AdSense review.

## Step 3: Build the internal linking structure

Define and implement the actual links, not the intention to have them:
- From a collection to each prompt it contains
- From a prompt back to the collections that include it
- From a prompt to its related prompts, per the Phase 04 relationship
- From category archives to the relevant collections
- From collections to related collections

Avoid circular link traps, orphan pages, and automatically generated link blocks that add no value. Every link should be useful to a reader, not just to a crawler.

Report which pages, if any, would still be orphaned with no internal links pointing at them.

## Step 4: Handle the empty and partial cases

A collection must behave correctly when:
- It contains no prompts yet
- A prompt inside it is unpublished, scheduled, or deleted
- It contains a single prompt
- It contains a large number of prompts and needs pagination or sensible length limits
- A prompt appears in several collections

A deleted prompt must drop out of the collection cleanly, without a broken row, a dead link, or a PHP notice.

## Step 5: Create one real example

Build one genuine collection, using the real draft prompts available, with a real original introduction written in the Phase 07 brand voice. This proves the mechanism works and gives Phase 19 a template to follow.

One real example is worth more than five placeholders. Do not create fake collections to fill the index.

## Verification

Verify and report the real outcome for:
- Creating, ordering, reordering, and publishing a collection
- The single collection template and the collection index rendering
- Prompt-to-collection and collection-to-prompt links resolving
- All of the empty and partial cases above
- Permalinks matching the Phase 04 frozen URL architecture
- Query counts on a collection page, so Phase 15 has a baseline
- Escaping and sanitization of all collection fields
- No PHP notices with debugging enabled
- The Phase 03 syntax and standards checks

## Completion report

Report the mechanism implemented, the templates created, the internal linking map, the real example collection, the query cost, and any orphan pages that remain. Update `CONTENT_STRATEGY.md` with how the team should build collections.
