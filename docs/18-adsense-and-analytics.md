# PHASE 18 — ADSENSE READINESS AND ANALYTICS

**Prerequisites:** Phase 12, Phase 15 and Phase 17 complete.
**Produces:** ad placements that are disabled by default, the analytics integration points, and the event tracking specification.
**Do not:** assume an AdSense account exists or that Google has approved the site. Do not invent a publisher ID, a verification token, or a measurement ID.

## Advertising placements

Create designated ad locations that:
- Are clearly distinguishable from editorial content and explicitly labeled
- Do not resemble prompt cards, collection entries, or navigation
- Are not placed near the Phase 10 copy buttons, where an accidental click is likely
- Do not obstruct the main content
- Reserve their space so they do not cause layout shift, measured against the Phase 15 CLS budget
- Remain responsive on mobile
- Are disabled by default and controlled by a single documented switch

Use the ad slot container component from Phase 07. Decide and document which templates carry ads and which do not, and keep the density low enough that the page still reads as content first.

Do not insert live advertising scripts until I have the account configuration and am ready to test. Verify the site renders correctly with ads disabled, because that is how it will ship.

## Event tracking specification

The original plan prepared analytics integration points but never defined what to measure, which leaves the site with no feedback loop. Specify the events that would actually change a decision:

- Prompt copy events, as an aggregate measure of which prompts are useful
- Search queries performed
- Zero-result searches, joined to the Phase 09 logging
- Filter and category usage
- Collection page engagement
- Newsletter signup completion
- Outbound and internal navigation patterns worth knowing

For each event, state what decision it informs. Drop any event that informs nothing.

Keep the implementation privacy-respecting: aggregate counts, no personally identifying data, no cross-site identifiers, and nothing that contradicts the Phase 17 privacy policy. If an event cannot be collected without identifying visitors, do not collect it.

Note explicitly that copy events here are an editorial signal only. They are not an earnings mechanism, and Phase 25 owns that question.

## ads.txt and verification

Support the correct `ads.txt` location and document how the real publisher ID is added at launch. Do not invent a publisher ID or copy an example identifier into a live configuration.

Do not promise AdSense approval or revenue. Identify the content-quality and policy requirements that remain my responsibility, honestly, including the number of published prompts and the originality standard from Phase 14.

## Analytics integration

Prepare the integration points for Google Search Console and Google Analytics, or a privacy-respecting alternative evaluated against the Phase 01 budget and the Phase 15 performance budget.

Avoid duplicate tags, unnecessary tracking, and any exposure of secrets. If Phase 17 determined Consent Mode is required, wire the integration to respect it rather than retrofitting that later.

State exactly which account-owned IDs I need to supply and where each one goes. Add every one to `LAUNCH_CHECKLIST.md`.

## Verification

Verify and report:
- Every template renders correctly with ads disabled
- The ad placements reserve space and produce no measurable layout shift
- No third-party advertising or analytics script loads while the switches are off
- The Phase 15 performance budgets still hold
- Mobile layout with placements active
- Privacy and policy links are reachable from every page carrying an ad slot
- The Phase 12 indexation policy is unaffected
- No placeholder ID is present in a live code path

## Completion report

Report which placements exist and on which templates, the event tracking specification with the decision each event informs, which integrations are ready and which are placeholders, the exact IDs and credentials still required from me, and the measured performance and CLS impact.

State plainly that AdSense approval depends on content quality and Google's review, not on this implementation.
