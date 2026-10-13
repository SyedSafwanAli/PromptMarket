# PHASE 10 — COPY, RECENTLY COPIED AND SAVED PROMPTS

**Prerequisites:** Phase 08 complete.
**Produces:** working copy-to-clipboard, a Recently Copied list, and saved prompts.
**Do not:** build copy counters, analytics displays, or anything that resembles the Phase 25 earnings system.

Inspect the current prompt templates and search implementation.

## Goal

Implement reliable prompt copying and a practical way for visitors to return to prompts they used previously.

## Copy button

- Copy the actual prompt content, not its description or title.
- Preserve the prompt's line breaks and whitespace exactly. A multi-line prompt copied as one line is a broken feature.
- Use the Clipboard API when available, with a documented fallback path.
- Handle unsupported clipboard access and permission errors gracefully, with a visible way for the visitor to select the text manually.
- Provide accessible feedback such as "Copied" and an announced status message via a live region.
- Keep the control keyboard accessible with a clear accessible name.
- Prevent duplicate event handlers and accidental double submissions.
- Do not introduce fake loading delays.

If the Phase 04 content model includes placeholder variables in prompts, decide and document whether copy delivers the raw prompt or a filled version, and make the behavior obvious to the visitor.

## Recently Copied

After a successful copy, add the prompt to Recently Copied automatically.

For guests, use a small, versioned browser storage structure. Store prompt IDs, timestamps, and the minimum information needed to render the list — not duplicated prompt bodies.

Include a schema version in the stored payload so a future change can migrate or discard old data instead of breaking.

Handle:
- Storage unavailable or throwing
- Private browsing restrictions
- Corrupted or unparseable data
- Stale IDs pointing at deleted or unpublished prompts
- A configurable history limit
- A visitor-initiated clear action

If the visitor is logged in, evaluate whether server-side persistence is justified. Do not require account creation just to copy a prompt.

## Saved prompts

Allow visitors to save and unsave a prompt if it can be implemented securely and maintainably.

For guest users, local storage is likely sufficient. If logged-in support is implemented, enforce ownership checks on the server, verify nonces on every state-changing request, and validate every prompt ID against real published content. Do not trust user-provided IDs.

Provide clear states for:
- Saved
- Not saved
- Copy succeeded
- Copy failed
- Empty Recently Copied list
- Empty saved list

## Rendering the lists

Decide how Recently Copied and Saved are displayed — a dedicated page, a panel, or both — and make sure those pages are excluded from search indexing in Phase 12, since they are per-visitor and have no value in search results.

Stale entries must degrade gracefully: a deleted prompt should disappear from the list cleanly, not render a broken row or a dead link.

## Progressive enhancement

The prompt content must be readable and manually selectable with JavaScript disabled or broken. Copy, Recently Copied, and Saved are enhancements. Verify that the page is still useful without them, and that controls which cannot work are not shown as if they could.

## Privacy

Do not silently collect unnecessary personal data or build browser fingerprints. Keep everything in this phase client-side unless server persistence was explicitly justified.

Write a plain-language description of exactly what is stored, where, and for how long. Phase 17 needs this for the privacy policy, and it must match the implementation.

## Testing

Test and record the real outcome for:
- Repeated copies of the same prompt
- Copying many different prompts, exceeding the history limit
- Storage unavailable and storage throwing
- Private browsing mode
- Corrupted stored data
- A stored ID for a deleted prompt
- Very long prompt text
- Prompts with special characters, quotes, and non-Latin text
- Multi-line prompt formatting after paste
- Keyboard-only operation of copy, save, and unsave
- Denied clipboard permission
- Screen reader announcement of success and failure
- Mobile browsers, including iOS Safari
- JavaScript disabled

## Completion report

Report what was implemented, the storage schema and its version, the exact privacy behavior, the full test matrix with real results, and any case that could not be tested along with what tool would be needed.
