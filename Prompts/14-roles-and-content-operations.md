# PHASE 14 — ROLES, PERMISSIONS AND CONTENT OPERATIONS

**Prerequisites:** Phase 05, Phase 08 and Phase 11 complete.
**Produces:** the user roles, the editorial admin experience, the publishing SOP, and the content calendar.
**Do not:** implement public creator submissions, creator accounts, or payments.

Build a practical editorial workflow for the owner and a small team that initially publishes prompts themselves.

## Goal

Make it realistic to publish the target number of high-quality prompts per day, as set in Phase 01, without compromising originality, usefulness, or SEO quality.

## Step 1: Roles and permissions

The original plan said the editor should not need administrator permissions but never defined the role that makes that true.

Define and implement:
- The roles this team actually needs, and what each one can and cannot do
- The specific capabilities for creating, editing, and publishing prompts and collections
- Whether an editor may manage taxonomy terms, and the reasoning
- Whether an editor may install plugins, edit theme files, or manage users — the answer should be no
- Who may publish versus who may only submit for review

Use the WordPress roles and capabilities API. Map the post type capabilities deliberately rather than relying on the default post capabilities.

Verify by actually logging in as each role and confirming both what works and what is correctly refused. A capability check that was never tested from a non-administrator account has not been verified.

## Step 2: The editing experience

Review the Phase 05 prompt editor and the Phase 11 collection editor. Add only the fields and inline editorial guidance that genuinely improve publishing.

For each prompt, the team should be able to enter:
- A descriptive, unique title
- The full prompt text, with formatting preserved
- An original explanation of what it does
- Appropriate categories and tags
- Relevant AI tool or model metadata, if Phase 04 defined that taxonomy
- Usage notes or customization guidance where helpful
- A featured image only when useful and legally permitted
- Draft, review, schedule, and publish status

Put the guidance where the work happens — inline help in the editor — rather than only in a document nobody opens.

## Step 3: Originality verification

Scraped or near-duplicate prompt content is the most common cause of AdSense rejection and the clearest legal risk in this project. The original plan reduced this to a single checklist line.

Define a concrete, repeatable process:
- How a prompt is checked against the rest of the site for near-duplication
- How a prompt is checked against external sources before publishing
- What the team does when a prompt is a common idea with no single owner
- How attribution is handled when a prompt is adapted rather than original
- Who is accountable for the check

Implement whatever part of this can reasonably be automated, such as an in-admin similarity warning against existing prompts. Be honest about what cannot be automated and must stay a human step.

## Step 4: The publishing SOP

Produce a documented checklist the team follows for every prompt:
1. Confirm the prompt is useful and meaningfully distinct.
2. Run the originality check from step 3.
3. Add a descriptive, unique title.
4. Explain the use case in original language, in the Phase 07 brand voice.
5. Assign accurate categories and tags.
6. Add image alt text if a featured image is used.
7. Verify the prompt content displays and copies correctly, with formatting intact.
8. Preview desktop and mobile layouts.
9. Confirm it belongs in an existing collection, or note that a new collection is needed.
10. Schedule or publish when ready.

Do not create hundreds of thin pages to target keyword variations. Do not fabricate prompt performance metrics or copy counts.

## Step 5: Operational documents

Create:
- A content calendar template, aligned to the Phase 04 topic clusters and priority order
- The publishing SOP as a standalone document a non-technical editor can follow
- A short onboarding note for a new team member
- A documented path for the zero-result search data from Phase 09 to feed the calendar

## Verification

Verify by doing, not by reading the code:
- Publish a real prompt end to end as a non-administrator editor
- Confirm the editor cannot reach administrator-only areas
- Confirm scheduling works and a scheduled prompt appears at the right time
- Confirm the review-and-approve path works if you implemented one
- Confirm the originality warning behaves correctly on a deliberately near-duplicate draft
- Run the Phase 03 syntax and standards checks

## Completion report

Report the roles created with their exact capabilities, the admin changes made, the originality process and what part of it is automated, the documents produced, the verification performed from each role, and any remaining manual steps.
