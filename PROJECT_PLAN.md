# PROMPTATLAS — PROJECT PLAN

**What this is:** an AI prompt discovery platform on WordPress. Visitors find, search, copy and
save useful AI prompts. Only the owner and team publish at launch.

**Architecture:** WordPress with a custom theme (`promptatlas`) plus a site-specific plugin
(`promptatlas-core`). PHP templates, WordPress hooks and APIs, CSS, minimal JavaScript. No React,
no headless, no build framework, no external search service.

Phase source files live in [`Prompts/`](Prompts/). Decisions live in [`DECISIONS.md`](DECISIONS.md).
Running status lives in [`PROGRESS.md`](PROGRESS.md).

---

## Roadmap and status

| # | Phase | Prereq | Status |
|---|---|---|---|
| 01 | Project decisions | — | **Complete** 2026-10-14 |
| 02 | Environment and local WordPress | 01 | **Complete** 2026-10-14 |
| 03 | Tooling, documentation, version control | 02 | **In progress** |
| 04 | Information architecture and content strategy | 01, 03 | Not started |
| 05 | Content model — `prompt` post type and taxonomies | 04 | Not started |
| 06 | Theme foundation | 02, 03 | Not started |
| 07 | Design system and brand assets | 06 | Not started |
| 08 | Frontend templates | 05, 07 | Not started |
| 09 | Search and discovery | 05, 08 | Not started |
| 10 | Copy, Recently Copied, Saved | 08 | Not started |
| 11 | Collections and hub pages | 04, 05, 08 | Not started |
| 12 | Technical SEO, social, crawler policy | 01, 08, 11 | Not started |
| 13 | Contact form, email, newsletter | 06, 07 | Not started |
| 14 | Roles, permissions, content operations | 05, 08 | Not started |
| 15 | Performance engineering | 08–11 | Not started |
| 16 | Security and accessibility review | 08–14 | Not started |
| 17 | Legal and policy pages | 01, 13 | Not started |
| 18 | AdSense readiness and analytics | 12, 15, 17 | Not started |
| 19 | Launch content batch | 11, 14 | Not started |
| 20 | Comprehensive QA | 19 | Not started |
| 21 | Independent code review | 20 | Not started |
| 22 | Staging and deployment | 21 | **Blocked** — needs a domain and a host |
| 23 | Post-launch operations | 22 | Not started |
| 24 | Performance regression process | 15, 22 | Not started |
| 25 | Creator and community specification (planning only) | 23 | Not started |

---

## What is actually blocked, and by what

| Blocker | Blocks | Who resolves it |
|---|---|---|
| **Domain name** (decision 1) | Phase 22 fully; Phase 12 canonical/sitemap verification and Phase 13 email sending can be built but not finalised | **Owner** |
| **Hosting account** | Phase 22 | **Owner** |
| **Content licence confirmation** (decision 5) | Phase 17 Terms wording | **Owner**, ideally with a lawyer |
| **AdSense publisher ID, GA4 ID, Search Console access** | Phase 18 | **Owner** — must never be invented |
| **Real prompt content** | Phase 19, and therefore 20 and 21 | **Owner and team** write it; the platform is built regardless |

Everything from Phase 03 through Phase 21 can be built without any of the above. Phases 12, 13
and 18 are built to read their identifiers from WordPress options rather than hardcoding them,
so supplying a domain or an analytics ID later is configuration, not a code change.

---

## Critical path

```
01 ─ 02 ─ 03 ─ 04 ─ 05 ─┬─ 08 ─┬─ 09 ─┐
                        │      ├─ 10 ─┤
         06 ─ 07 ───────┘      ├─ 11 ─┤
                               └──────┴─ 12 ─ 15 ─ 16 ─ 17 ─ 18 ─ 19 ─ 20 ─ 21 ─ 22 ─ 23/24/25
                               13 ─ 14 ─┘
```

Phase 08 is the convergence point: it needs both the content model (05) and the design system
(07). Phases 06 and 07 can proceed in parallel with 04 and 05 because they do not depend on the
content model — this is the one place in the plan with real slack.

## Dependency notes that are easy to get wrong

- **05 before 08.** Templates render the `prompt` post type; the post type must exist first.
- **07 before 08.** Templates consume design tokens. Writing templates first produces colour
  literals scattered through markup, which breaks dark mode (decision 8).
- **21 before 22.** The code review is a pre-deployment gate, not a post-mortem.
- **12 depends on 11.** Collection and hub pages need to exist before their canonical URLs,
  sitemap entries and structured data can be correct.
- **The three review phases are not interchangeable.** 16 is targeted inspection during
  development, 20 is black-box functional QA against the running site, 21 is a fresh-eyes audit
  of the whole codebase. Do not merge them.
- **18 after 17.** AdSense review requires the policy pages to already be live.

---

## Decisions that shape the build

From [`DECISIONS.md`](DECISIONS.md), the ones with the widest reach:

| Decision | Consequence |
|---|---|
| Global English audience, EU/US traffic expected (2) | GDPR/CCPA consent is **required** in Phase 18 |
| English-only, translation-ready, `promptatlas` text domain (3) | Every output string wrapped from Phase 06 onward |
| Near-zero plugins; `promptatlas-core` owns site functionality (4) | Phases 12, 13 and 16 write code instead of configuring plugins |
| Comments off everywhere (6) | No phase builds or styles comment templates |
| Selective AI crawler policy + `llms.txt` (7) | Phase 12 deliverable |
| Dark mode in scope (8) | Phase 07 must tokenise every colour; Phase 16 checks both themes |
| Repo at WordPress root, core ignored (11) | Phase 03 `.gitignore`; Phase 22 deploys a narrower set |

---

## Non-goals for the initial release

Not built, and not partially built:

- Public creator accounts or creator dashboards
- Community submissions or moderation queues
- Payments, payouts, earnings ledger
- Semantic or vector search
- A headless or JavaScript-framework frontend
- A mobile app
- Any feature existing only as a non-functional visual placeholder

Phase 25 writes the creator-system specification. It does not implement it.

---

## Definition of launched

The Phase 06–11 site is live on the real domain over HTTPS, indexing is enabled, 40+ original
prompts are published, the Phase 17 policy pages exist, the contact form delivers mail, and
Phases 20 and 21 have both signed off. See decision 10.
