# PROMPTATLAS — DECISION RECORD

Every decision that later phases depend on. One entry per decision: what was decided, the date,
the reasoning, the alternatives rejected, and who confirmed it.

**Read this before starting any phase.** If a decision here is wrong, say so now — changing a
decision is cheap, undoing the code built on it is not.

## How to read the status column

| Status | Meaning |
|---|---|
| **Owner-confirmed** | The project owner stated this explicitly. Do not change it without them. |
| **Delegated** | The owner delegated the decision ("sab karo complete", 2026-10-14). Decided here with reasoning, and the owner may veto any of them. Treat as binding until they do. |
| **Pending** | Genuinely unresolved. Do not fill the gap with an assumption. |

---

## 1. Domain name — **PENDING**

**Not decided. The owner supplies this one.**

No domain has been given, and the standing rules forbid inventing one. Nothing in the codebase
hardcodes a domain; the site runs on `http://localhost/promptmarket` and reads its URL from the
WordPress `siteurl` option, so switching to a real domain later is an options change plus a
search-replace, not a code change.

Candidates worth checking, in preference order. **Availability has not been verified** — check at
a registrar before committing:

| Candidate | Note |
|---|---|
| `promptatlas.com` | First choice. Matches the brand exactly. |
| `promptatlas.io` / `.dev` | Common for developer-facing tools; `.io` renews expensively. |
| `promptatlas.co` | Acceptable fallback. |
| `theopromptatlas.com`, `promptatlashq.com` | Only if the above are gone. |

Avoid anything that reads as a knock-off or a misspelling of an existing prompt site — it damages
trust and is a recognised AdSense review risk.

Expect roughly $10–15/year for `.com`, $35–50/year for `.io`.

**Blocked work:** Phase 12 (canonical URLs, sitemap), Phase 13 (email `From` domain), Phase 17
(policy pages), Phase 22 (deployment). None of them are blocked *yet*.

---

## 2. Primary audience and geography — **DELEGATED** · 2026-10-14

**Decision: global English-speaking audience, with South Asia (Pakistan, India) and South-East
Asia expected to be a large traffic share. Build for EU and US visitors from day one.**

Reasoning:

- Organic search demand for AI prompts is overwhelmingly in English and is not
  geographically containable. You cannot choose to not have EU visitors.
- The owner is in Pakistan, which affects content tone and publishing hours, not the audience.
- Consequence that actually matters: **GDPR, UK GDPR and CCPA consent handling is required**, not
  optional. Phase 18 must implement consent before any advertising or analytics cookie is set.
  Designing this in is cheap; retrofitting consent after AdSense is live is not.
- AdSense RPM expectation: a global-English mix skewed toward South Asian traffic earns at the
  **low end** of the range. Do not plan on US-tier RPM. No revenue figure is projected here —
  see decision 10.
- Hosting region: **United States**, with a CDN in front. US is a reasonable midpoint for a
  global audience and is where most of the higher-value traffic sits. Confirm at Phase 22.

**Alternatives rejected:**

- *Pakistan/India-only targeting* — shrinks the addressable search market enormously for no
  compliance saving, since EU visitors would still arrive.
- *EU hosting* — would favour the smaller share of traffic and raise latency for the larger share.
- *Assuming a global English audience by default without deciding* — Phase 01 explicitly forbids
  this, and it would have silently removed the consent requirement from Phase 18.

---

## 3. Content language and localization — **DELEGATED** · 2026-10-14

**Decision: English-only content, in translation-ready code with the text domain `promptatlas`.
Roman Urdu is supported as *search input only*, never as a second content language.**

Reasoning:

- Every string the theme and plugin output goes through `__()`, `esc_html__()` etc. with the
  `promptatlas` text domain. This costs almost nothing while writing the code and keeps a future
  translation possible.
- No `.po`/`.mo` files, no language switcher, no multilingual plugin, no hreflang. Those are only
  justified once there is real demand.
- **This resolves the conflict flagged in Phase 01.** Phase 09 may map Roman Urdu query terms to
  English content as search synonyms — that is a small, contained lookup that makes the site
  findable for Urdu-speaking searchers. It does **not** mean translating prompts.

**Alternatives rejected:**

- *English only, no scaffolding* — saves a trivial amount of work now and makes translation a
  full rewrite later.
- *Genuinely multilingual content* — doubles every editorial task forever and produces the
  half-translated outcome Phase 01 warns about. Also a duplicate-content risk if done lazily.

---

## 4. Plugin stack — **DELEGATED** · 2026-10-14

**Decision: ship with as close to zero plugins as possible. Site-specific functionality lives in
a `promptatlas-core` plugin written for this project, not in third-party plugins.**

| Slot | Decision | Reasoning |
|---|---|---|
| **SEO metadata** | **Theme/plugin-owned.** No Yoast, no Rank Math, no All in One SEO. | WordPress core already emits titles, canonicals, robots directives and sitemaps. What core lacks is meta descriptions and Open Graph/Twitter cards — that is roughly 150 lines in `promptatlas-core`. An SEO plugin would add database tables, admin assets on every screen, upsell notices, and a migration cost if removed, to deliver output we can emit exactly. Phase 12 owns the implementation. |
| **Caching** | **Deliberately empty for launch.** | Page caching belongs at the host or CDN layer, not in PHP. A caching plugin on a site with no traffic optimises nothing and makes every other phase harder to debug. Phase 15 measures first; Phase 22 picks a host-level cache. Revisit only with a measurement that justifies it. |
| **Backup** | **Deliberately empty.** Host-level snapshots plus a documented `mysqldump` procedure. | Backup plugins that write into the web root are a common breach vector and routinely time out on shared hosting. Procedure documented in `DEPLOYMENT.md`; Phase 22 confirms host snapshots. |
| **Security hardening** | **Deliberately empty.** | Hardening here is code discipline — escape output, verify nonces, check capabilities, `DISALLOW_FILE_EDIT`, a non-`admin` username, a non-root database user, least-privilege grants. All already in place. A security plugin bolts a firewall onto an application whose own code is the real attack surface, and costs real request time. Phase 16 audits the code instead. |
| **Contact form** | **Custom, in `promptatlas-core`.** No Contact Form 7, no WPForms, no Gravity Forms. | One form with name/email/message, a nonce, capability-free public POST, server-side validation, a honeypot plus timing check, and rate limiting. Phase 13 owns it. A form plugin loads its own CSS and JS on every page to render one form. |
| **Newsletter / email capture** | **Capture now, sending deferred.** Double opt-in capture in `promptatlas-core`; no ESP chosen yet. | Storing addresses is easy and must be done correctly (explicit consent, timestamp, IP, confirmed status). Choosing a sending provider before there is a list to send to is premature; it also interacts with decision 1 (sending domain). Marked for Phase 13 to implement capture and Phase 22 to pick the provider. |
| **Transactional email** | **Pending** — depends on decision 1. | `wp_mail()` via PHP `mail()` is unreliable and lands in spam. Production needs SMTP or an API provider on a real domain. Phase 13 documents it; Phase 22 configures it. |

**Standing rule this sets:** before any later phase adds a plugin, it must state what native
WordPress capability fails to cover the need, the measured cost, and the removal path. "It is
convenient" is not a justification.

**Alternatives rejected:**

- *A conventional stack (Yoast + WP Rocket + Wordfence + UpdraftPlus + CF7)* — five plugins,
  recurring licence costs, significant request overhead, and five upgrade paths that can each
  break the site. Each one replaces code we control with code we do not.
- *A page builder* — directly contradicts the custom-theme architecture.

---

## 5. Prompt content licence and ownership — **PENDING (position drafted)**

**This is a real legal position. The wording below is a starting point for Phase 17, not legal
advice, and the final Terms of Use should be reviewed by a lawyer before launch.**

Drafted position, for the owner to confirm or amend:

- **Ownership.** Prompts published on PromptAtlas are written by PromptAtlas or licensed to it.
  The site's compilation, categorisation, and the written explanation accompanying each prompt
  are PromptAtlas's own work and are protected as such.
- **Visitor use.** Visitors may copy and use published prompts freely, including commercially,
  with no attribution required. Rationale: a prompt the visitor may not use is worthless, and
  restrictive terms on a copy button would be unenforceable theatre. Attribution is *appreciated*
  but not required — stated as a request, not a condition.
- **What is not licensed.** Visitors may not bulk-copy, scrape, re-host, or republish the
  collection itself, or the written explanations, as a competing dataset or site.
- **Third-party and submitted prompts (Phase 25).** Any future submission flow must take an
  explicit, recorded licence grant from the submitter, confirm they have the right to grant it,
  and keep the submitter's attribution. Do not build a submission form without this.
- **Takedown.** A DMCA/takedown contact path is required — Phase 17.

**Open question for the owner:** is any PromptAtlas content derived from, or adapted from,
somebody else's published prompts? If yes, that must be attributed per prompt. Per the standing
content-integrity rule, scraped or near-duplicate content is both a legal risk and the most
common cause of AdSense rejection.

---

## 6. WordPress comments — **DELEGATED** · 2026-10-14

**Decision: comments and pingbacks OFF everywhere — prompts, collections, pages and posts.**

Applied in Phase 02: `default_comment_status = closed`, `default_ping_status = closed`.
Phase 05 registers the `prompt` post type **without** `comments` support. Phase 14 will
additionally close comments on any existing content and remove the admin menu noise.

Reasoning:

- Comments on a prompt add nothing a visitor came for. The valuable interaction is copy, and
  that is built in Phase 10.
- Spam on an unmoderated prompt/AI-adjacent site is relentless, and there is no moderation
  capacity — publishing is owner-and-team only at launch.
- Unmoderated or spam-filled comments are a documented AdSense policy risk.
- It removes an entire attack surface and a whole class of template work.

**Consequence for later phases:** no phase should style a comment template, enqueue
`comment-reply.js`, or output comment markup. If discussion is ever wanted, Phase 25's creator
specification is the place to design it properly.

**Alternative rejected:** *comments on with Akismet* — adds a paid third-party dependency and a
moderation obligation to deliver a feature nobody asked for.

---

## 7. AI and LLM crawler policy — **DELEGATED** · 2026-10-14

**Decision: selectively allow. Allow crawlers that retrieve and cite with a link; block
training-only crawlers. Publish an `llms.txt`.**

| Crawler class | Policy | Examples |
|---|---|---|
| Search engines | **Allow** | Googlebot, Bingbot |
| AI retrieval/citation (sends referral traffic) | **Allow** | OpenAI `OAI-SearchBot`, `PerplexityBot`, Anthropic `Claude-SearchBot` |
| Training-only (no traffic back) | **Block** | `GPTBot`, `CCBot`, `Google-Extended`, `anthropic-ai` |

Reasoning — this is a genuine trade-off and it cuts differently for a prompt site than for most
content sites:

- Prompt content is exactly what gets ingested, reproduced without attribution, and answered
  inline by an assistant. Allowing training-only crawlers hands over the entire product for
  nothing in return.
- Retrieval crawlers are different: they cite, and an AI assistant recommending a PromptAtlas page
  is a real and growing traffic source. Blocking those would cut off a channel that matters.
- `llms.txt` is cheap to publish and states the policy in the emerging convention, while
  `robots.txt` enforces what is actually enforceable.
- **Honest limitation:** `robots.txt` is voluntary. It stops well-behaved crawlers and nothing
  else. It is not a technical protection measure, and it should not be described as one.

Phase 12 implements `robots.txt` and `llms.txt`. Reviewed at launch (`LAUNCH_CHECKLIST.md`).

**Alternatives rejected:**

- *Allow everything* — maximum ingestion, zero leverage, no attribution.
- *Block everything AI* — forfeits assistant referral traffic, which is one of the few growing
  discovery channels for this exact kind of content.

---

## 8. Dark mode — **DELEGATED** · 2026-10-14

**Decision: IN SCOPE. Designed in from the start in Phase 07, not retrofitted.**

Implementation this commits Phase 07 to:

- Every colour is a CSS custom property on `:root`. No colour literal appears in a component rule.
- Dark values are redefined under `@media (prefers-color-scheme: dark)`, so the OS preference is
  respected with no JavaScript.
- A manual toggle overrides the OS preference via a `data-theme` attribute on the root element,
  persisted in `localStorage`, with every read and write wrapped so that blocked site data cannot
  break the page.
- Both themes must meet the contrast requirements in Phase 16. Dark mode is not "the same design
  inverted" — the brand palette needs genuinely different values at low luminance.

Reasoning:

- The audience is people working with AI tools; dark mode is an expectation, not a flourish.
- Phase 01 is right that retrofitting a theme system into a finished design system is
  significantly more expensive. The cost of deciding yes now is writing colours as tokens, which
  is better practice regardless.
- It interacts with the brand palette: burgundy `#8B2626` and olive `#486C2F` are mid-dark
  colours that will not survive unchanged on a dark background. Phase 07 must derive lighter
  variants deliberately rather than discovering the problem later.

**Alternative rejected:** *out of scope* — the likely outcome is a retrofit request after Phase 08,
at several times the cost.

---

## 9. Monthly operating budget — **PENDING (estimate supplied)**

**The owner supplies the ceiling.** Realistic cost for a small WordPress content site:

| Item | Monthly | Note |
|---|---|---|
| Domain | ~$1 | $10–15/year, `.com` |
| Shared hosting | $3–10 | Adequate to launch and well beyond |
| Managed WordPress hosting | $15–30 | Buys host-level caching, backups, staging, support |
| CDN | $0 | Cloudflare free tier is genuinely sufficient here |
| Plugins | **$0** | Direct consequence of decision 4 |
| Transactional email | $0–15 | Free tiers cover low volume |
| Analytics | $0 | Google Analytics / Search Console |

**Realistic total: $5–12/month to launch on shared hosting; $20–35/month on managed hosting.**

What the extra buys: staging, automatic backups, host-level page caching, and someone to call.
Given decision 4 deliberately has no caching or backup plugin, managed hosting is where those
capabilities come from — that is a coherent trade, not an upsell.

**Not included:** paid content writing, paid link building, or paid traffic. None are planned.

---

## 10. Success metrics and timeline — **PENDING (targets proposed)**

**The owner confirms these.** Proposed, with assumptions stated:

| Milestone | Target | Assumption |
|---|---|---|
| Published prompts at launch | **40–60** | Enough for real category and collection pages to exist. Below ~30 the site looks thin to both visitors and an AdSense reviewer. |
| At 3 months | **120–180** | Roughly 2–3 original prompts published per working day. |
| At 6 months | **250–350** | Same rate, allowing for breaks. |
| AdSense application | **Not before launch + ~2–3 months** | Requires: 40+ original prompts indexed, all Phase 17 policy pages live, indexing enabled, real navigation, and some organic traffic history. Phase 18. |

**"Launched" means:** the Phase 06–11 site is live on the real domain over HTTPS, indexing is
enabled, 40+ original prompts are published, the Phase 17 policy pages exist, the contact form
delivers mail, and Phases 20 and 21 have both signed off.

**No traffic or revenue projection is given.** A reliable one is not possible for a new site with
no ranking history, no domain authority, and no keyword data. Any number offered here would be
invented. Once Search Console has 4–6 weeks of real impression data, a grounded forecast becomes
possible — not before.

**Explicitly not a success metric:** number of prompts alone. 300 near-duplicate prompts is a
worse outcome than 60 genuinely useful ones, and the content-integrity rule outranks any count.

---

## 11. Repository boundary — **DELEGATED** · 2026-10-14 · *Phase 03*

**Decision: the Git repository stays at `C:\xampp\htdocs\promptmarket` (the WordPress root), but
only project-authored code is tracked. WordPress core is ignored.**

Tracked: `wp-content/themes/promptatlas/`, `wp-content/plugins/promptatlas-core/`, the
documentation set, `Prompts/`, `.gitignore`.

Ignored: all WordPress core files and directories, `wp-config.php`, `wp-content/uploads/`,
third-party themes and plugins, logs, caches, and database dumps.

Reasoning:

- The repository root was already here with `Prompts/` committed, and the WordPress install was
  placed around it in Phase 02. Moving the boundary now would either orphan `Prompts/` or require
  rewriting history.
- A root-level repository with core ignored gives one VS Code workspace containing everything
  that is actually edited, while keeping ~2,000 core files out of every diff.
- Both project-authored components — the theme and the site-specific plugin — are tracked in one
  repository, so a change touching both is one commit. A theme-only boundary could not do this.
- What is *deployed* is a narrower question than what is *tracked*, and Phase 22 answers it.

**Alternatives rejected:**

- *Repository at the theme directory only* — cannot track `promptatlas-core`, and strands the
  existing `Prompts/` library and the documentation set.
- *Repository at `wp-content/`* — cleaner in theory, but `Prompts/` and the docs sit above it, and
  it would mean re-initialising the existing repository for no real gain.
- *Tracking WordPress core* — bloats the repository, buries real changes in core diffs on every
  update, and offers nothing that `wp core verify-checksums` does not already do better.

---

## Change log

| Date | Change |
|---|---|
| 2026-10-14 | File created. Decisions 2, 3, 4, 6, 7, 8 and 11 decided under owner delegation. Decisions 1, 5, 9, 10 recorded as pending with recommendations. Phase 02's provisional settings (locale `en_US`, comments closed, timezone `Asia/Karachi`) are now confirmed by decisions 3 and 6; the placeholder tagline remains Phase 04's to replace. |
