# CLAUDE.md — PromptAtlas repository conventions

**Read this first in every session.** Then read `PROGRESS.md` (where the work actually stands),
`DECISIONS.md` (what is already locked in) and the phase file you are about to run from
`Prompts/`.

---

## 1. What this project is

**PromptAtlas** — an AI prompt discovery platform on WordPress. Visitors find, search, copy and
save useful AI prompts. Only the owner and team publish at launch.

The build follows a fixed 25-phase sequence in [`Prompts/`](Prompts/). Phases have prerequisites
and produce wrong work when run out of order. Finish the current phase, report, and stop — do not
roll into the next phase because it seems convenient.

---

## 2. Environment facts

Verify these rather than trusting them if something looks wrong. Full detail in `DEPLOYMENT.md`.

| Fact | Value |
|---|---|
| Local site URL | `http://localhost/promptmarket` |
| Admin URL | `http://localhost/promptmarket/wp-admin` |
| WordPress root | `C:\xampp\htdocs\promptmarket` |
| **Theme path** | `wp-content/themes/promptatlas/` |
| **Plugin path** | `wp-content/plugins/promptatlas-core/` |
| WordPress | 7.1.3 |
| PHP | 8.2.12 — `C:\xampp\php\php.exe` |
| Database | MariaDB 10.4.32, database `promptatlas`, prefix `wp_` |
| Web server | Apache 2.4.58 (XAMPP) |
| WP-CLI | 2.12.0 — `C:\xampp\wp-cli.phar` |
| OS | Windows 11 |

Credentials live in `C:\Users\Syed Safwan\promptatlas-local-credentials.txt`, **outside this
repository**. Never read them into a report, a commit, or a log.

### Three environment traps that have already caused bugs

1. **Git Bash mangles WP-CLI arguments.** Any argument starting with `/` or containing a URL path
   gets rewritten into a Windows path. This silently corrupted `siteurl` and the permalink
   structure in Phase 02. Either use PowerShell, or:
   ```bash
   export MSYS_NO_PATHCONV=1 MSYS2_ARG_CONV_EXCL='*'
   C:/xampp/php/php.exe C:/xampp/wp-cli.phar rewrite structure '/%postname%/'
   ```
2. **Never run `wp core download --force`.** The WordPress 7.1.3 tarball contains a filename
   ending in a dot, which Windows cannot create. Use the admin updater or the `.zip` package.
3. **`wp db *` subcommands fail** — `mysql.exe` and `mysqlcheck.exe` are not on `PATH`. Call them
   from `C:\xampp\mysql\bin\` directly.

---

## 3. Architecture

WordPress with a **custom classic theme** plus a **site-specific plugin**. PHP templates,
WordPress hooks and APIs, CSS, minimal vanilla JavaScript.

**Not used, and not to be introduced without a measured requirement and the owner's approval:**
React, Next.js, a headless CMS, an external search service, a bundler or build step, a page
builder, or a third-party API.

### What goes where

| Concern | Location | Why |
|---|---|---|
| Templates, markup, styling, presentation | `promptatlas` theme | Presentation belongs to the theme |
| Post types, taxonomies, meta registration | `promptatlas-core` plugin | Content must survive a theme change |
| SEO meta output, Open Graph, structured data | `promptatlas-core` | Data about content, not presentation |
| Contact form handling, newsletter capture | `promptatlas-core` | Business logic, must survive a theme change |
| Copy / Recently Copied / Saved UI | theme (JS + CSS), with any endpoint in the plugin | UI in theme, server contract in plugin |

The test: **if deactivating the theme would lose data or break a URL, it belongs in the plugin.**

---

## 4. Coding rules

These are not stylistic preferences. A pull request violating section 4.1 is wrong, not untidy.

### 4.1 Security — non-negotiable

- **Escape on output, always.** `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`.
  There is no "this value is safe" exception; the escaping sniffs are set to **error**.
- **Verify nonces** on every state-changing request: `wp_verify_nonce()` / `check_admin_referer()`.
- **Check capabilities server-side** with `current_user_can()`. A hidden button is not a permission.
- **Sanitise and validate every input** before use and before storage. Never trust `$_GET`,
  `$_POST`, `$_REQUEST`, `$_SERVER` or a cookie.
- **Prepared queries only.** `$wpdb->prepare()` for every variable. Prefer `WP_Query` and the
  WordPress APIs over custom SQL — if you are writing SQL, justify it in a comment.
- **Never commit a secret.** Not a password, API key, publisher ID, token or database credential.
- **Never modify WordPress core.**

### 4.2 WordPress conventions

- Follow the WordPress Coding Standards; `phpcs` enforces them.
- Use the template hierarchy correctly. Do not route around it.
- Enqueue assets through `wp_enqueue_script` / `wp_enqueue_style` on the proper hook. Never
  hardcode a `<script>` or `<link>` tag in a template.
- **Load assets only where they are needed.** A stylesheet for the prompt page must not load on
  the homepage.
- Prefix every global symbol: functions `promptatlas_`, classes `PromptAtlas_`, constants
  `PROMPTATLAS_`, options and meta keys `promptatlas_` or `_promptatlas_` for protected meta.
- Wrap every user-facing string for translation with the text domain **`promptatlas`**
  (decision 3). `__()`, `esc_html__()`, `esc_attr__()`, `_n()`, and `printf` with `%s`
  placeholders — never concatenate a translated string.
- Register meta with `register_post_meta()` including `sanitize_callback` and `auth_callback`.
- Comments are **off everywhere** (decision 6). Do not build, style or enqueue anything for them.

### 4.3 Frontend

- **Every colour is a CSS custom property.** No colour literal in a component rule — dark mode is
  in scope (decision 8) and depends on this absolutely.
- Mobile-first CSS. The layout must work at 320px with no horizontal scroll.
- Explicit `width` and `height` on every image; lazy-load below the fold. CLS budget is 0.1.
- JavaScript is progressive enhancement. Core content and navigation must work without it.
- No framework, no jQuery, no bundler. Vanilla JS in small, documented files.
- Semantic HTML and real landmarks. Keyboard-operable, visible focus states, and contrast that
  passes in **both** light and dark themes.
- Wrap every `localStorage` read and write in `try`/`catch` — it throws in private mode and with
  blocked site data, and the page must still render.

### 4.4 Content integrity — outranks every feature

- **Never fabricate** prompt records, ratings, review counts, user counts, copy totals, trending
  statistics, testimonials or social proof.
- Where real data does not exist yet, build an **honest empty state**.
- Every published prompt must be original or properly attributed.
- Never disguise an ad as content, and never place an ad where it could be mistaken for the copy
  button.

---

## 5. Brand palette

Defined as tokens in the theme. Phase 07 derives the dark-theme variants — the mid-dark colours
below will not survive unchanged on a dark background.

| Token | Hex | Use |
|---|---|---|
| Burgundy | `#8B2626` | Primary brand, headings, primary action |
| Orange | `#EF6905` | Accent, highlights, focus |
| Pale butter yellow | `#F1E5A1` | Soft surfaces, highlight backgrounds |
| Olive green | `#486C2F` | Secondary accent, success |
| Warm off-white | `#FCFAF6` | Page background (light theme) |
| White / charcoal / muted gray | — | Supporting text and surfaces |

**Design intent: distinctive and editorial, not a generic AI SaaS template.** Avoid unnecessary
shadows, heavy borders, gradients, glassmorphism, neon, excessively rounded cards, oversized
heroes, and decorative animation with no purpose. Favour clear hierarchy, generous spacing and
meaningful typography. System fonts initially.

---

## 6. Performance targets

LCP ≤ 2.5s · INP ≤ 200ms · CLS ≤ 0.1

Minimal JavaScript, lightweight CSS, system fonts, compressed and correctly sized responsive
images, no render-blocking resources without a reason, no autoplay video, no unnecessary
third-party requests. Treat a two-second load as a goal, not a promise. **Distinguish localhost
measurements from real production measurements** — localhost has no network latency and will
flatter every number.

---

## 7. Running the checks

From the repository root:

```bash
# PHP syntax check across the theme and plugin
C:/xampp/php/php.exe tools/lint-php.php      # or: composer lint

# WordPress Coding Standards
./vendor/bin/phpcs                           # or: composer cs
./vendor/bin/phpcbf                          # auto-fix what is fixable

# Both
composer check
```

`phpcs` exit codes: `0` clean, `1` warnings, `2` errors. Full detail and manual alternatives in
`TESTING.md`. Run both before every commit.

---

## 8. Version control

**Repository root:** the WordPress root (`C:\xampp\htdocs\promptmarket`), tracking only
project-authored code. WordPress core, `wp-config.php`, uploads and third-party code are ignored.
Reasoning in `DECISIONS.md` decision 11.

- **The database is never committed.** Content lives in MySQL; the repository holds code.
- **Secrets are never committed.** This repository has a GitHub remote, so a leak is a push away.
- **Branching:** work on `main` for phase work, since this is a single-developer project. Use a
  short-lived `feature/<name>` branch for anything experimental or risky, and merge when the
  checks pass.
- **Commit messages:** imperative mood, a phase reference where one applies, and a body
  explaining *why* when the reason is not obvious from the diff.
  ```
  Phase 05: register prompt post type and taxonomies

  Registered in promptatlas-core rather than the theme so that prompt URLs
  and content survive a theme change.
  ```
- Commit when a phase completes, or at a coherent checkpoint within one. Do not bundle unrelated
  changes.

---

## 9. Documentation map

| File | What it holds |
|---|---|
| `CLAUDE.md` | This file — conventions and the standing context for a new session |
| `PROGRESS.md` | **Where the work actually stands**, test results, known issues, exact next step |
| `DECISIONS.md` | Every locked decision with date, reasoning and rejected alternatives |
| `PROJECT_PLAN.md` | Phase roadmap, dependencies, what is blocked and on whom |
| `README.md` | What PromptAtlas is and how to run it locally |
| `CONTENT_STRATEGY.md` | Audience, taxonomy, prompt page anatomy, editorial standards (Phase 04) |
| `TESTING.md` | Every check available and how to run it |
| `DEPLOYMENT.md` | Environment facts, local setup, deployment (Phase 22) |
| `LAUNCH_CHECKLIST.md` | Everything deferred to launch — **add to it whenever a phase defers something** |
| `Prompts/` | The 25 phase instruction files. Reference only; not code |

**Update `PROGRESS.md` and `DECISIONS.md` at the end of every phase.** A phase is not complete
until they reflect reality.

---

## 10. Standing rules for working on this project

- **Inspect before changing.** Read the actual file and the actual environment; do not assume.
- **Never claim something passed if it was not run.** Report the real command and its real output.
- **Never silently ignore an error.** An unexplained warning is an open issue.
- **Do not fill a decision gap with a plausible assumption.** Mark it unresolved and say so.
- **Do not overwrite unrelated files, delete the owner's work, or reset the database** without
  explaining the impact and getting approval first.
- **Do not add a plugin or dependency** without stating what native WordPress capability fails to
  cover the need, the cost, and the removal path (decision 4).
- Prefer native WordPress capabilities first. Keep presentation separate from business logic.
- Build honest empty states instead of fake data.
- When a phase defers something to launch, **add it to `LAUNCH_CHECKLIST.md` in the same session**.
