# PHASE 25 — CREATOR AND COMMUNITY SPECIFICATION

**Prerequisites:** Phase 23 complete and the site operating stably with real traffic.
**Produces:** a phased technical specification.
**Do not:** implement any of it. This phase produces a document and then stops.

## Goal

Plan a future creator and community system for PromptAtlas without implementing payments or any other part of it yet.

Study the current architecture and propose a staged design. For each stage, state what it delivers, what it depends on, and what it costs to build and to operate.

## Design areas

- User registration and roles, built on the Phase 14 roles rather than beside them
- Creator prompt submissions
- Moderation queue and reporting
- Ownership and editing permissions after submission
- Copy analytics, built on the Phase 18 event specification
- Abuse prevention
- Privacy-conscious visitor recognition
- Eligible-copy rules
- An auditable earnings ledger
- Withdrawal requests and administrative review

## Constraints to respect

- Build on the existing content model. Identify every data model change, and treat any change to the frozen Phase 04 URLs as requiring a redirect plan.
- Public submissions change the legal posture of the site. The Phase 17 DMCA policy, Terms, and licence position all need revision before submissions open, and the specification must say so.
- Moderation is an operational cost, not just a feature. Estimate the human time per submission and who does it.
- Spam and low-quality submissions will arrive immediately. Design for that from the first stage.
- The Phase 15 performance budgets still apply.

## The earnings concept

Assume the eventual concept may be PKR 5 per 50 eligible copies.

Design around these constraints:
- Repeated copies by the same recognized visitor must not count as new eligible copies.
- IP address alone must not permanently disqualify a visitor. Shared and mobile-carrier IP addresses are common, especially in the Phase 01 target geography.
- Perfect lifetime visitor identification is not possible. Do not claim otherwise, and design a system that is honest about its error rate in both directions.
- The ledger must be auditable, append-only, and reconcilable. A creator must be able to see how a figure was reached.
- Define what happens to accrued earnings when a prompt is removed, a creator is banned, or fraud is found after payment.

Address fraud directly: self-copying, automated copying, coordinated groups, and why a copy-based metric is inherently easier to game than most. If you conclude the metric is not viable at this payout level, say so and propose alternatives. That is a more useful answer than a specification for something that will be abused.

## Also specify

- Any regulatory or tax implications of paying individuals, flagged as needing professional advice rather than answered
- The payment rails realistically available for small PKR payouts, and their fees and minimums
- The operating cost of the system at several usage levels
- What could be learned cheaply first, before building the full system
- Migration concerns for existing content and existing URLs
- What should deliberately never be built

## Deliverable

A phased technical specification with the data model changes, the security risks, the migration concerns, the expected costs, and the operational requirements for each stage.

End with a clear recommendation on whether to build this at all, and if so, which stage is worth doing first.

Then wait for my approval. Do not begin implementation.
