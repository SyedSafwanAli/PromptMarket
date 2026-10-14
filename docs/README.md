# PromptAtlas — Project Prompt Library

This folder contains the complete, ordered instruction set for building **PromptAtlas**, an AI prompt discovery platform on WordPress.

Nothing here is code. Each file is one prompt you paste into a fresh Claude Code session, in order.

---

## How to use this library

1. **Always load `00-MASTER-INSTRUCTIONS.md` first** in any new session. It holds the standing rules that every phase depends on.
2. Then paste the phase file you are working on.
3. At the start of every phase, Claude must read `00-MASTER-INSTRUCTIONS.md`, `PROGRESS.md`, and `DECISIONS.md` from the project repository before touching files.
4. Do not skip ahead. Each phase lists its prerequisites and most will produce incorrect work if run out of order.
5. After each phase, confirm `PROGRESS.md` was updated before moving on.

---

## Phase order

| # | File | Phase | Prerequisite |
|---|---|---|---|
| — | `00-MASTER-INSTRUCTIONS.md` | Standing rules (not a phase) | — |
| 01 | `01-project-decisions.md` | Project decisions and open questions | — |
| 02 | `02-environment-and-local-wordpress.md` | Environment audit and local WordPress | 01 |
| 03 | `03-tooling-docs-and-git.md` | Tooling, documentation and version control | 02 |
| 04 | `04-information-architecture.md` | Information architecture and content strategy | 01, 03 |
| 05 | `05-content-model.md` | Prompt post type and taxonomies | 04 |
| 06 | `06-theme-foundation.md` | Theme foundation | 02, 03 |
| 07 | `07-design-system.md` | Design system and brand assets | 06 |
| 08 | `08-frontend-templates.md` | Frontend templates | 05, 07 |
| 09 | `09-search-and-discovery.md` | Search and discovery | 05, 08 |
| 10 | `10-copy-recent-and-saved.md` | Copy, Recently Copied, Saved | 08 |
| 11 | `11-collections-and-hub-pages.md` | Collections and hub pages | 04, 05, 08 |
| 12 | `12-technical-seo.md` | Technical SEO, social and crawler policy | 01, 08, 11 |
| 13 | `13-contact-email-newsletter.md` | Contact form, email delivery, newsletter | 06, 07 |
| 14 | `14-roles-and-content-operations.md` | Roles, permissions and content operations | 05, 08 |
| 15 | `15-performance-engineering.md` | Performance engineering | 08, 09, 10, 11 |
| 16 | `16-security-and-accessibility-review.md` | Security and accessibility review | 08–14 |
| 17 | `17-legal-and-policy-pages.md` | Legal and policy pages | 01, 13 |
| 18 | `18-adsense-and-analytics.md` | AdSense readiness and analytics | 12, 15, 17 |
| 19 | `19-launch-content-batch.md` | Launch content batch | 14, 11 |
| 20 | `20-comprehensive-qa.md` | Comprehensive QA and regression testing | 19 |
| 21 | `21-independent-code-review.md` | Independent code review | 20 |
| 22 | `22-staging-and-deployment.md` | Staging and deployment | 21 |
| 23 | `23-post-launch-operations.md` | Post-launch operations | 22 |
| 24 | `24-performance-regression-process.md` | Performance regression process | 15, 22 |
| 25 | `25-creator-community-specification.md` | Creator and community specification (planning only) | 23 |

---

## Dependency notes

- **Phase 05 must run before Phase 08.** The frontend templates render the `prompt` post type, so the post type has to exist first.
- **Phase 21 must run before Phase 22.** The independent code review is a pre-deployment gate, not a post-mortem.
- **Phase 01 gates several later phases.** Audience geography decides the consent requirement in Phase 18; the plugin stack decision shapes Phase 12; the language decision shapes Phases 06 and 09.

## The three review phases are deliberately different

They overlap in subject but not in method. Do not merge them.

- **Phase 16 — Security and accessibility review.** Deep, targeted inspection of two specific risk domains while the code is still being written.
- **Phase 20 — Comprehensive QA.** Black-box functional testing against the running site, as an independent QA engineer who does not trust the code.
- **Phase 21 — Independent code review.** Fresh-eyes audit of the whole codebase assuming all previous work may contain mistakes. The final gate before deployment.

---

## Status tracker

Update this table as you complete each phase.

| Phase | Status | Date | Notes |
|---|---|---|---|
| 01 | Complete | 2026-10-14 | DECISIONS.md + PROJECT_PLAN.md. Items 1, 5, 9, 10 pending with owner. |
| 02 | Complete | 2026-10-14 | WordPress 7.1.3 on XAMPP, verified. Run before Phase 01 — see PROGRESS.md "Phase 01 debt". |
| 03 | Complete | 2026-10-14 | Git boundary, full doc set, PHPCS + WPCS passing. |
| 04 | Complete | 2026-10-14 | CONTENT_STRATEGY.md. Taxonomy + URLs frozen (decisions 12, 13). |
| 05 | Complete | 2026-10-14 | promptatlas-core plugin. 3 bugs found and fixed; see PROGRESS.md. |
| 06 | Complete | 2026-10-14 | Custom theme active; dark mode mechanism established. |
| 07 | Complete | 2026-10-14 | Tokens, components, brand assets, measured contrast. /style-reference/. |
| 08 | Complete | 2026-10-14 | All public templates on real content. 9 edge cases tested. |
| 09 | Not started | | |
| 10 | Not started | | |
| 11 | Not started | | |
| 12 | Not started | | |
| 13 | Not started | | |
| 14 | Not started | | |
| 15 | Not started | | |
| 16 | Not started | | |
| 17 | Not started | | |
| 18 | Not started | | |
| 19 | Not started | | |
| 20 | Not started | | |
| 21 | Not started | | |
| 22 | Not started | | |
| 23 | Not started | | |
| 24 | Not started | | |
| 25 | Not started | | |

---

## `_archive-original/`

The first draft of this library (`Prompt00.txt` through `Prompt17.txt`). Kept for reference only. Do not run these files — the phase numbering, ordering, and scope have changed. Use the files above.
