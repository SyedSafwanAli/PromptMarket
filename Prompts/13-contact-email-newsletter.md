# PHASE 13 — CONTACT FORM, EMAIL DELIVERY AND NEWSLETTER

**Prerequisites:** Phase 06 and Phase 07 complete. Phase 01 plugin stack decision resolved.
**Produces:** a working contact path, verified email delivery, and an email capture mechanism.
**Do not:** add a marketing automation suite, a popup that blocks content, or any third-party script that is not justified against the Phase 01 budget and the Phase 15 performance budget.

## Why this phase exists

The original plan linked a Contact page in the footer and listed "email delivery if needed" in a deployment checklist, but nothing ever built a contact path. A site applying for AdSense with a non-functional contact page has a real problem, and a site owner who cannot receive a DMCA notice or a correction has a worse one.

## Step 1: Contact form

Implement a contact form using the mechanism chosen in Phase 01 — a plugin, or a small custom implementation if that is genuinely lighter.

Requirements:
- Accessible labels, not placeholder-only fields
- Clear, specific validation errors associated with their fields
- Server-side validation and sanitization of every field
- Nonce verification on submission
- Honest success and failure states, with a fallback contact method shown if sending fails
- Spam protection that does not depend on a third-party script loaded on every page
- Rate limiting on submissions
- No silent failures

Decide and document whether submissions are stored in the database, emailed only, or both. If stored, state the retention period and reflect it in the Phase 17 privacy policy.

Avoid CAPTCHA if a lighter approach works. If one is needed, choose an accessible option and account for its performance cost.

## Step 2: Email delivery

WordPress `wp_mail()` through a shared host's default mail transport is unreliable and frequently lands in spam. Address this now rather than discovering it after launch.

Evaluate and recommend:
- The default PHP mail transport
- SMTP through a transactional email provider
- The provider options available within the Phase 01 budget

Record the decision. Set up what can be set up locally and list exactly what requires credentials I must supply.

Never put SMTP credentials in the theme, in Git, or in client-side JavaScript. Document where they belong and how they are injected.

## Step 3: Verify delivery honestly

Local WordPress environments usually cannot send real email. Determine what this environment can actually do and say so plainly.

- If a local mail catcher is available, use it and report the captured message.
- If real sending is not possible locally, say that delivery is unverified, implement everything that can be implemented, and add an explicit delivery test to `LAUNCH_CHECKLIST.md` for Phase 22.

Do not report that email works because the form submitted without an error.

## Step 4: Newsletter and email capture

An email list is the one audience asset that does not depend on Google. It belongs in the initial build, not a later phase.

Decide and implement a minimal, honest approach:
- Where the signup appears, and where it does not
- What the subscriber is promised, in specific terms
- Double opt-in handling
- Where subscribers are stored, and whether that is a provider or the local database
- Unsubscribe handling
- What happens if the provider is unavailable

Requirements:
- No interstitial or content-blocking popup
- No exit-intent modal
- No pre-checked consent boxes
- No fabricated subscriber counts or social proof
- The form must reserve its space so it does not shift layout
- Accessible labels, states, and error messages

If no provider is chosen yet, build the interface against a documented internal storage mechanism and keep the provider integration behind a single, clearly marked seam. Do not ship a signup form that silently discards addresses.

## Step 5: Privacy

Write down precisely what each form collects, where it goes, who processes it, and how long it is kept. Phase 17 uses this text, and it must match the implementation.

If the Phase 01 audience decision brings GDPR or UK GDPR into scope, note the lawful basis and the consent requirements for the newsletter specifically, and flag anything needing legal review rather than drafting legal conclusions.

## Verification

Test and report real outcomes for:
- A valid submission
- Every validation failure path
- A submission with a missing or invalid nonce
- Script and HTML injection attempts in every field
- An extremely long submission
- Repeated rapid submissions against the rate limit
- Keyboard-only completion of both forms
- Screen reader announcement of errors and success
- Both forms with JavaScript disabled
- Mobile layout and input types
- Newsletter signup success, failure, and duplicate address

## Completion report

Report what was built, the email transport decision, whether delivery was actually verified or not and why, the newsletter approach and where subscribers are stored, the exact privacy behavior, the credentials still required from me, and the items added to `LAUNCH_CHECKLIST.md`.
