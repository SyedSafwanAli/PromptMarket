# PHASE 05 — PROMPT POST TYPE AND TAXONOMIES

**Prerequisites:** Phase 04 complete. Use the taxonomy, URL, and field decisions from `CONTENT_STRATEGY.md` exactly as written.
**Produces:** the registered `prompt` post type, its taxonomies, and the admin editing experience.
**Do not:** build frontend templates here. Phase 08 owns the frontend, and it depends on this phase.

## Goal

Make PromptAtlas content manageable from the WordPress admin without hardcoding prompts into the theme.

## Where this code lives

Decide, and record in `DECISIONS.md`, whether the post type and taxonomy registration belongs in the theme or in a small site-specific plugin.

The content model must survive a theme change. Content that disappears when the theme is switched is a data-integrity problem. Recommend accordingly and explain the trade-off.

## Prompt post type

Register a `prompt` custom post type using WordPress APIs, with the slug and rewrite behavior specified in Phase 04.

Define deliberately, with the reason recorded:
- Public visibility
- Archive behavior
- REST API support, and what that exposes
- Which core features are supported: title, editor, excerpt, featured image, revisions
- Menu position and admin icon
- Capability mapping

Use native WordPress title, editor, excerpt, featured image, and publication status where practical. Do not reimplement something WordPress already does well.

## Taxonomies

Register the taxonomies defined in Phase 04 — prompt categories, prompt tags, and the AI tool or model taxonomy if Phase 04 justified it.

Decide hierarchical versus flat per the Phase 04 decision. Set the rewrite slugs to the frozen Phase 04 URLs. Set `show_in_rest` deliberately.

Seed the initial category terms defined in Phase 04 so the admin is usable immediately. Seed terms only; do not create placeholder prompt content.

## Custom metadata

Add metadata only for the fields Phase 04 identified as genuinely needed. For each field, implement and document:
- Its purpose
- Its registration, including the type and whether it is exposed in REST
- Server-side validation
- Sanitization on save
- Escaping on output
- The capability check and nonce verification on the save handler
- Its fallback when empty

Avoid storing data WordPress already manages. If a field duplicates the excerpt or the content, use the native field instead.

## Editorial workflow

The owner and team must be able to:
- Create and edit a prompt
- Preview before publishing
- Save drafts
- Schedule publication
- Assign categories and tags
- Add a clear description
- Publish and update content safely

Verify each of these by actually performing it in the admin.

Do not add public submissions, creator accounts, or payouts in this phase.

## URL stability and rewrite rules

Use the exact slugs frozen in Phase 04. Check for conflicts between the post type slug, taxonomy slugs, page slugs, and any existing WordPress rewrite rules.

Flush rewrite rules only on registration or activation, never on every page request. Verify that permalinks resolve after flushing.

Preserve any existing WordPress content and URLs.

## Verification

Create at least two real draft prompts yourself to exercise the model, using genuine content rather than lorem ipsum, and verify:
- Prompt creation, saving, and publication
- Draft and scheduled content behave correctly
- Taxonomy assignment works and terms save
- Custom fields save, sanitize, and reload correctly
- An unauthorized user cannot save prompt metadata
- Permalinks resolve for prompts, categories, and tags
- The post type survives a theme switch, if you placed it in a plugin
- No PHP notices, warnings, or fatal errors appear with debugging enabled
- The PHP syntax check from Phase 03 passes

Enable WordPress debugging for this phase and report anything it surfaces.

## Completion report

Document the final content model: every post type argument, taxonomy, and meta field, with its purpose and sanitization. Note any migration implications if the model changes later.

Report the files changed, the verification actually performed with its real output, and anything that could not be tested.
