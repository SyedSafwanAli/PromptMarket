# PHASE 01 — PROJECT DECISIONS AND OPEN QUESTIONS

**Prerequisites:** none. This is the first phase.
**Produces:** `DECISIONS.md`, `PROJECT_PLAN.md`, and a list of questions only I can answer.
**Do not:** install software, create a WordPress site, or write any code in this phase.

## Goal

Several later phases are ambiguous until a small set of project-level decisions are locked in. Resolve them now, in writing, so that no phase has to guess.

For each item below: research it where research helps, give me a clear recommendation with your reasoning, state the consequences of each option, and mark whether the decision is yours to recommend or mine to supply.

## Decisions required before Phase 02

### 1. Domain name
Check availability for sensible candidates. Recommend one. Note the registrar cost. If `promptatlas.com` is unavailable, propose alternatives that do not look like a knock-off or a misspelling. I supply the final choice.

### 2. Primary audience and geography
Who is this for, and where are they? This single decision determines:
- Whether GDPR, UK GDPR, or CCPA consent handling is required in Phase 18
- Realistic AdSense RPM expectations
- Content language and tone
- Hosting region

Do not assume a global English audience by default. Ask me directly if you cannot infer it.

### 3. Content language and localization
Options to evaluate:
- English only, no localization scaffolding
- English only, but translation-ready code with a text domain
- Genuinely multilingual content

Note the conflict in the original plan: the search phase wants Roman Urdu query handling while the content is English. Resolve this explicitly. Roman Urdu *search input* with English content is a coherent and cheap choice; partially localized *content* is the expensive, half-finished outcome to avoid.

### 4. Plugin stack
Choose and justify, or explicitly choose none, for each slot:
- SEO metadata: a plugin, or theme-owned output, or WordPress native features
- Caching
- Backup
- Security hardening
- Contact form
- Newsletter or email capture

For each: licence cost, performance cost, maintenance burden, and whether a native WordPress capability already covers it. Prefer fewer plugins. Record the slots you deliberately leave empty.

### 5. Prompt content licence and ownership
Decide and record:
- Who owns the prompts published on PromptAtlas
- Whether visitors may use copied prompts commercially
- What attribution, if any, is requested
- How third-party or user-submitted prompts will be handled when Phase 25 arrives

This text feeds the Terms of Use in Phase 17. It is a real legal position, so flag anything that needs a lawyer rather than inventing legal language.

### 6. WordPress comments
On or off for prompts, collections, and pages. If on, state the spam mitigation. If off, state it so no later phase styles a comment template nobody will use.

### 7. AI and LLM crawler policy
Decide whether to allow, block, or selectively allow AI training and retrieval crawlers, and whether to publish an `llms.txt`. Explain the trade-off between AI-assistant referral traffic and having the content ingested without attribution. This is a genuine strategic question for a prompt site.

### 8. Dark mode
In scope or out of scope. Decide now. Retrofitting a theme system into a finished design system is significantly more expensive than designing for it from the start.

### 9. Budget ceiling
Monthly operating budget for hosting, domain, plugins, and tools. Produce a realistic cost estimate for a small WordPress content site and tell me what it buys.

### 10. Success metrics and timeline
Define measurable targets so later phases have something to aim at:
- Number of published prompts at launch
- Number of published prompts at three and six months
- When AdSense application becomes realistic, and what has to be true first
- What "launched" means for this project

Do not invent traffic projections. Give ranges with the assumptions stated, or say that a reliable projection is not possible.

## Deliverables

1. `DECISIONS.md` — one entry per decision above, each with: the decision, the date, the reasoning, the alternatives rejected, and whether it is confirmed by me or still pending.
2. `PROJECT_PLAN.md` — the phase roadmap, the dependency notes, and the current status.
3. A short list, at the end of your report, of the decisions still blocked on me, with the exact question for each.

## Completion criteria

Do not proceed to Phase 02 until items 2, 3, 4, and 8 are resolved, because Phases 06, 07, 09, 12, and 18 depend on them. Items 1, 5, 9, and 10 may remain pending if you record them clearly as pending.

Mark every unresolved item as unresolved. Do not fill a gap with a plausible assumption and continue.
