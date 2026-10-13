# PHASE 16 — SECURITY AND ACCESSIBILITY REVIEW

**Prerequisites:** Phases 08 through 15 complete.
**Produces:** a prioritized findings report and fixes for confirmed issues.
**Method:** a deep, targeted audit of two specific risk domains while the code is still being written.

This is the first of three review phases and they are deliberately different. Phase 20 is black-box functional QA. Phase 21 is a fresh-eyes whole-codebase audit and the final pre-deployment gate. Do not do their work here, and do not assume they will catch what you skip.

## Security review

Inspect:
- Output escaping on every template and template part
- Input validation and sanitization
- Nonce verification on every state-changing request
- Capability checks, verified from a non-administrator account
- Authentication and authorization
- Custom REST endpoints and what they expose
- AJAX handlers
- Database query safety and prepared statements
- The search parameter handling from Phase 09
- The contact form and newsletter handlers from Phase 13
- The saved-prompts handlers from Phase 10
- File uploads, if any
- URL handling and redirects, including open redirect risks
- Cross-site scripting risks, including stored XSS through prompt content and taxonomy terms
- CSRF risks
- Exposed secrets in the theme, the repository, and client-side JavaScript
- Debug output and error disclosure
- Error handling
- Spam and abuse surfaces
- Plugin and dependency risks, including whether each plugin is maintained

Use WordPress APIs and established security patterns. Do not invent custom cryptography. Do not store passwords, API keys, or production secrets in the theme, in Git, or in browser JavaScript.

Do not expose private information through public REST responses. Do not assume a nonce replaces an authorization check — verify both independently.

Specific to this project: prompt content is user-facing text that editors paste from elsewhere. Confirm that a prompt containing a script tag, an event handler attribute, or a data URI renders safely on the archive, the single page, the collection page, the search results, and in the copy payload.

## Accessibility review

Check:
- Keyboard navigation across every template
- Focus order and visible focus
- Screen-reader labels and accessible names
- Heading hierarchy
- Landmark structure
- Form labels, error association, and error announcement
- Contrast, with the actual computed ratios reported
- Zoom to 200% and responsive reflow at 320 CSS pixels
- Reduced motion
- Dialogs and menus, including focus trapping and escape handling
- Clipboard success and error announcements from Phase 10
- Touch target size and spacing
- Text spacing and content reflow without loss of function

Aim for WCAG 2.2 AA where practical. Do not claim formal compliance without adequate testing, and state exactly which criteria you were able to test and which you could not.

## Fix policy

Fix confirmed problems with the smallest maintainable changes. Do not rewrite unrelated code. Do not add a heavy plugin without a clear reason.

Where a fix would conflict with a Phase 15 optimization, say so and recommend which should win rather than silently reverting performance work.

## Verification

Run, and report the real output of:
- The Phase 03 PHP syntax check
- PHPCS, including any security sniffs available
- WordPress debugging enabled across every template
- Any browser accessibility checker available
- Manual keyboard traversal of every template
- Capability checks exercised from each Phase 14 role, including a logged-out visitor

If a tool is missing, report that fact and recommend the simplest suitable next step rather than skipping the check silently.

## Completion report

Produce a prioritized report. For each finding:
- Severity
- The affected file and line
- The concrete failure scenario, with the input and the resulting behavior
- The fix applied, or why it was deferred
- The verification that the fix works

Then list the remaining risks, the checks you could not perform and why, and anything that must be re-verified on production in Phase 22.

Do not report a clean result for an area you did not actually inspect.
