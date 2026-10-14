# PHASE 20 — COMPREHENSIVE QA AND REGRESSION TESTING

**Prerequisites:** Phase 19 complete, so the site is being tested with real content.
**Produces:** a QA report, fixes for confirmed bugs, and the launch-blocker list.
**Method:** black-box functional testing against the running site, as an independent QA engineer.

Act as an independent QA engineer. Do not assume features work because their code exists. Where the code and the behavior disagree, the behavior is the truth.

This is the second of three review phases. Phase 16 already audited security and accessibility in depth; re-test them here only as functional behavior. Phase 21 will audit the code. Your job is the running site.

Read the project documentation and build a test plan covering what was actually implemented.

## Functional tests

Test:
- Homepage rendering
- Prompt archive and detail pages
- Collection pages and the collection index
- Categories and tags
- Search and filters
- Zero-result search logging
- Pagination
- Copy button
- Recently Copied
- Saved prompts
- Contact form
- Newsletter signup
- Navigation and the footer policy links
- 404 page
- Empty states
- Admin prompt and collection publishing, from each Phase 14 role
- SEO metadata, canonical URLs, and Open Graph tags
- Ad slots with ads disabled
- Responsive behavior
- Keyboard navigation
- Reduced-motion behavior

## Edge cases

Test:
- No published prompts, by temporarily unpublishing or on a clean state
- Empty categories
- Very long titles
- Very long prompt content
- Special characters and non-English text
- Missing metadata and missing images
- No search results
- Invalid and hostile query parameters
- Clipboard permission failures
- Browser storage unavailable and corrupted
- Stale saved and recently-copied references
- Mobile overflow at 320 CSS pixels
- Broken links across the whole site
- Unauthorized state-changing requests
- A prompt containing script tags or event handler attributes
- JavaScript disabled
- Page two and beyond on every paginated listing

## Technical checks

Run what is available and report real output:
- The Phase 03 PHP syntax check
- PHPCS with the WordPress standards
- WordPress debugging enabled across every template
- Automated tests, if any exist
- Browser tests, if tooling exists
- A link check across the site
- Lighthouse or an equivalent performance audit, against the Phase 15 budgets

Do not install a large test framework unless the project justifies it. Never say tests passed when they were skipped — list them as not run, with the reason.

## Cross-browser and device

Test what you genuinely can, and state what you could not. At minimum establish whether the site was tested on:
- A Chromium browser
- Firefox
- Safari, including iOS Safari, where clipboard behavior most often differs
- A real mobile device or an emulated one, stating which

Do not claim mobile Safari was tested if no such browser was available.

## Fix and retest

Fix confirmed bugs caused by the implementation. After each meaningful fix, re-run the relevant tests and check for regressions. Avoid unrelated rewrites.

Where a bug reveals a design or architecture problem rather than a defect, say so rather than patching the symptom.

## Deliverables

Produce a concise QA report:
- Tests passed
- Tests failed, each with the exact reproduction steps
- Tests not run, and why
- Bugs fixed, with the verification that proves it
- Remaining defects, with severity
- Security concerns
- Performance observations against the Phase 15 budgets
- Launch blockers, listed explicitly

Update `TESTING.md` with the test plan so it can be repeated after future changes.

Do not claim the site is production-ready if critical checks remain incomplete. If there are launch blockers, say so clearly at the top of the report.
