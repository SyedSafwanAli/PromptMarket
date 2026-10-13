# PHASE 17 — LEGAL AND POLICY PAGES

**Prerequisites:** Phase 01 (audience geography and content licence) and Phase 13 complete.
**Produces:** the published policy pages, the consent decision, and the accessibility statement.
**Do not:** fabricate a business address, a legal entity, an email address, a phone number, or a privacy practice. Do not write legal conclusions.

## Why this is its own phase

The original plan bundled the policy pages into the AdSense phase, which buried real legal and compliance work inside an advertising task. These pages are a launch requirement regardless of whether advertising ever happens, and three of them were missing entirely.

## Required pages

Prepare accurate, useful drafts for:

### About
What PromptAtlas is, who runs it, and the editorial standard. Written in the Phase 07 brand voice. Do not invent a team, a founding story, a company size, or credentials.

### Contact
Linked to the working Phase 13 contact form, with the fallback contact method shown.

### Privacy Policy
This must describe what the site actually does, verified against the implementation rather than copied from a template. Cover:
- What the Phase 10 browser storage keeps, where, and for how long
- What the Phase 09 zero-result search logging records, and that it holds no identifying data
- What the Phase 13 contact form and newsletter collect, where it goes, and the retention period
- What the Phase 18 analytics will collect, once configured
- What advertising will collect, once configured
- Cookies actually set, if any
- Visitor rights, scoped to the Phase 01 geography

Every claim in this page must match the code. If the implementation and the policy disagree, the policy is wrong and the mismatch is a real compliance problem.

### Terms of Use
Including the Phase 01 decision on prompt ownership, whether visitors may use copied prompts commercially, and what attribution is requested. Flag anything needing a lawyer rather than drafting around it.

### DMCA and copyright policy
Missing from the original plan and necessary for a site that publishes prompt text, especially before Phase 25 opens public submissions. Cover the notice procedure, the counter-notice path, the designated contact, and the repeat-infringer position. Mark the agent details as owner-supplied.

### Accessibility statement
What standard the site aims for, what Phase 16 actually verified, the known gaps, and how to report a problem. Stating the real position is better than claiming compliance.

## Placeholders

Use clearly marked placeholders for every piece of information only I can supply — legal entity, jurisdiction, address, contact email, DMCA agent, effective dates. Make them impossible to miss, and list every one in `LAUNCH_CHECKLIST.md`.

Do not publish a page with an unfilled placeholder presented as fact.

## Consent management

Phase 01 set the audience geography. Turn that into a concrete decision now, not a question deferred again.

Determine and record:
- Whether GDPR, UK GDPR, CCPA, or another regime applies
- Whether a consent banner is required, and for what specifically
- Whether Google Consent Mode will be needed for Phase 18
- The lawful basis for the newsletter
- The performance and layout-shift cost of any consent tool, measured against the Phase 15 budgets

Implement what is required and nothing more. A consent banner on a site with no tracking is a self-inflicted performance and usability cost. Do not claim legal compliance — state what was implemented and what still needs review.

## Integration

Publish the pages, wire them into the Phase 08 footer so the links go live, add them to the navigation where appropriate, and confirm the Phase 12 indexation policy covers them.

## Verification

Verify and report:
- Every page exists, is published, and renders correctly on desktop and mobile
- Footer and navigation links resolve
- Every privacy claim is traceable to actual code, listed claim by claim
- Every placeholder is marked and listed in `LAUNCH_CHECKLIST.md`
- The pages appear in the sitemap and follow the Phase 12 indexation policy
- The consent mechanism, if implemented, is keyboard accessible and does not block content
- Layout shift from any consent banner, measured

## Completion report

Report the pages created, the privacy-claims-to-code mapping, the consent determination and its reasoning, every owner-supplied placeholder outstanding, and anything you believe needs professional legal review.
