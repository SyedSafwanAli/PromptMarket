# PHASE 21 — INDEPENDENT CODE REVIEW

**Prerequisites:** Phase 20 complete.
**Produces:** a severity-ranked findings report, fixes for confirmed high-priority issues, and the go or no-go recommendation.
**Method:** a fresh-eyes audit of the entire codebase, assuming all previous work may contain mistakes.

**This is the final gate before deployment.** It runs before Phase 22, not after. A review conducted after launch is a post-mortem, not a review.

Perform a fresh, independent code review of the entire PromptAtlas project. Do not trust the previous phase reports, including your own. A phase that claimed success may have verified it poorly.

## Scope

Inspect:
- The Git history and the full current state of the code
- The theme architecture and whether the separation of concerns held up
- Correct use of WordPress APIs
- Every PHP template and template part
- The site-specific plugin, if one was created
- Security controls
- Accessibility implementation
- Frontend performance
- Search behavior
- SEO output
- The content model and its data integrity
- Maintainability and duplication

## Priority order

1. Critical security issues
2. Data loss or broken content behavior
3. PHP errors and broken templates
4. Accessibility failures
5. Performance regressions
6. SEO conflicts, especially duplicated metadata
7. Duplicated or unnecessary code

## Specific things to verify independently

These are the places where this project is most likely to have gone wrong. Check each one yourself rather than trusting the phase report that covered it.

- Does the content model survive a theme switch, as Phase 05 was supposed to ensure?
- Does any URL, canonical, or sitemap entry contain a hardcoded local domain that will break in Phase 22?
- Does SEO metadata appear exactly once per page, with no theme-versus-plugin duplication?
- Does every state-changing handler check both a nonce and a capability?
- Does prompt content from an untrusted paste render safely everywhere it appears?
- Do the Phase 10 per-visitor features behave correctly under the Phase 15 caching?
- Does the Phase 17 privacy policy still match what the code actually does?
- Is any secret, credential, or placeholder account ID present anywhere in the repository?
- Do the Phase 15 performance budgets still hold after all later changes?
- Is there any feature that is visually present but non-functional, contrary to the master instructions?

## Method

Do not rewrite the entire project. List findings by severity, explain the evidence for each with the specific file, line, and failure scenario, and fix confirmed high-priority issues in small, reviewable changes.

Run the relevant tests after each fix. Where a finding is plausible but you could not confirm it, label it as unconfirmed rather than asserting it.

Where a finding would require a large change, do not start it. Report it with a recommendation and let me decide.

## Deliverables

1. The findings report, severity-ranked, each with evidence and a concrete failure scenario.
2. The fixes applied, with the verification output that proves each one.
3. The findings deliberately not fixed, with the reasoning.
4. All unresolved risks.
5. Updated documentation and `PROGRESS.md`.
6. An explicit deployment recommendation: go, or no-go with the specific blockers listed.

Never fabricate test results. If this project is not ready to deploy, say so plainly — that is the most valuable output this phase can produce.
