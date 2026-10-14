# PROMPTATLAS — PROGRESS LOG

This file records completed work, outstanding work, test results, known issues, and the
exact next step. Update it after every phase.

---

## Status summary

| Phase | Status | Date | Notes |
|---|---|---|---|
| 01 — Project decisions | **Complete** | 2026-10-14 | `DECISIONS.md` + `PROJECT_PLAN.md`. Decisions 2, 3, 4, 6, 7, 8, 11 decided under owner delegation; 1, 5, 9, 10 pending with recommendations. |
| 02 — Environment and local WordPress | **Complete** | 2026-10-14 | WordPress 7.1.3 installed and verified on XAMPP. |
| 03 — Tooling, docs and version control | **Complete** | 2026-10-14 | Git boundary set, full doc set, PHPCS + WPCS installed and passing. |
| 04 — Information architecture | **Complete** | 2026-10-14 | `CONTENT_STRATEGY.md`; taxonomy + URLs frozen as decisions 12 and 13. |
| 05 — Content model | **Complete** | 2026-10-14 | `promptatlas-core` plugin: 2 post types, 3 taxonomies, 8 meta fields. All checks pass. |
| 06 — Theme foundation | **Complete** | 2026-10-14 | Custom theme active. Dark mode mechanism established. |
| 07 — Design system | **Complete** | 2026-10-14 | Tokens, components, brand assets, measured contrast. `/style-reference/`. |
| 08 — Frontend templates | **Complete** | 2026-10-14 | All public templates rendering real content. 9 edge cases tested. |
| 09 — Search and discovery | Not started | — | **Next.** |
| 10–25 | Not started | — | — |

> **Note on ordering.** Phase 02 ran before Phase 01 at the owner's instruction. Phase 01 was
> then completed retroactively on 2026-10-14, and its decisions 3 and 6 confirmed the provisional
> settings Phase 02 had applied. The "Phase 01 debt" section below is resolved and kept for the
> record.

---

## Phase 02 — Environment audit and local WordPress setup

Completed 2026-10-14.

### Environment facts

Later phases depend on these values. Re-verify them rather than assuming they still hold.

| Fact | Value |
|---|---|
| Operating system | Windows 11 Pro 10.0.26100 |
| Local environment | XAMPP (LocalWP is not installed on this machine) |
| Web server | Apache 2.4.58 (Win64, Apache Lounge VS17) |
| PHP version | 8.2.12 (ZTS, Visual C++ 2019, x64) |
| **PHP executable path** | `C:\xampp\php\php.exe` |
| `php.ini` in use | `C:\xampp\php\php.ini` |
| WordPress version | 7.1.3 |
| Database engine | MariaDB 10.4.32 |
| MySQL client binaries | `C:\xampp\mysql\bin\` (not on the system `PATH`) |
| **Local site URL** | `http://localhost/promptmarket` |
| Admin URL | `http://localhost/promptmarket/wp-admin` |
| **WordPress root directory** | `C:\xampp\htdocs\promptmarket` |
| **Theme directory** | `C:\xampp\htdocs\promptmarket\wp-content\themes\promptatlas` |
| VS Code workspace path | `C:\xampp\htdocs\promptmarket` |
| Active theme (temporary) | `twentytwentyfive` 1.5 |
| Database name | `promptatlas` |
| Database user | `promptatlas_usr` (dedicated, not `root`) |
| Table prefix | `wp_` |
| Table count / engine | 12 tables, all InnoDB, all `OK` |
| WP-CLI available | **Yes** — WP-CLI 2.12.0 at `C:\xampp\wp-cli.phar` |
| WordPress admin username | `promptatlas_ops` (not `admin`) |
| Credentials file | `C:\Users\Syed Safwan\promptatlas-local-credentials.txt` — outside the repository, git-ignored by pattern |

### WordPress configuration applied

| Setting | Value | Why |
|---|---|---|
| Site title | PromptAtlas | Phase 02 requirement |
| Tagline | Discover, search, copy and save useful AI prompts. | Placeholder; Phase 04 owns real copy |
| Permalink structure | `/%postname%/` | Phase 02 requirement |
| Search engine visibility | **Discouraged** (`blog_public = 0`) | Development site. See `LAUNCH_CHECKLIST.md` |
| Default comment status | `closed` | Provisional — see Phase 01 debt |
| Default ping status | `closed` | Provisional — see Phase 01 debt |
| Timezone | `Asia/Karachi` | Provisional — see Phase 01 debt |
| Locale | `en_US` | Provisional — see Phase 01 debt |

`wp-config.php` additions for local development:

```php
WP_ENVIRONMENT_TYPE = 'local'
WP_DEBUG            = true
WP_DEBUG_LOG        = true
WP_DEBUG_DISPLAY    = false   // errors go to wp-content/debug.log, never to the screen
SCRIPT_DEBUG        = true
DISALLOW_FILE_EDIT  = true    // theme/plugin editing happens in VS Code, not in wp-admin
WP_POST_REVISIONS   = 10
EMPTY_TRASH_DAYS    = 30
```

### Commands actually run, and their results

| Command | Result |
|---|---|
| `wp core download --locale=en_US` | Success — WordPress 7.1.3, md5 hash verified |
| `mysql` — create database, create dedicated user, grant | Success (after the repair below) |
| `wp config create` | Success — `wp-config.php` generated |
| `wp core install` | Success |
| `wp rewrite structure '/%postname%/'` | Success |
| `wp option update blog_public 0` | Success |
| `wp theme install twentytwentyfive --activate` | Success — v1.5 |
| `wp core verify-checksums` | **Success — core verifies against official checksums** |
| `mysqlcheck -u root promptatlas` | All 12 tables `OK` |
| `mysql CHECK TABLE mysql.*` | All grant tables `OK` after repair |

### Verification results (all performed, none assumed)

| Check | Result |
|---|---|
| Frontend `GET /` | **200** — `<title>PromptAtlas – Discover, search, copy and save useful AI prompts.</title>` |
| Login page `GET /wp-login.php` | **200**, login form present |
| `GET /wp-admin/` while logged out | **302** to login (correct) |
| Admin login `POST /wp-login.php` | **302**, `wordpress_logged_in` cookie issued |
| Dashboard `GET /wp-admin/` while logged in | **200**, 9 dashboard markers matched |
| Post permalink `GET /hello-world/` | **200** — pretty permalinks work |
| Page permalink `GET /sample-page/` | **200** |
| REST API `GET /wp-json/` | **200** |
| Search `GET /?s=prompt` | **200** |
| `noindex` meta present on frontend | **Yes** — `<meta name='robots' content='noindex, nofollow' />` |
| Database connection | Working — WordPress reads and writes |
| Theme directory identified | `wp-content/themes/promptatlas` created (empty; Phase 06 fills it) |
| PHP error log | `wp-content/debug.log` does not exist — no PHP errors logged |
| Apache error log | No WordPress-related errors |
| Unrelated project files overwritten | **None.** `docs/` is untouched; other `htdocs` sites untouched |

---

## Issues found and resolved during Phase 02

### 1. Corrupted MariaDB grant tables (pre-existing, unrelated to this project)

`FLUSH PRIVILEGES` failed with `ERROR 1030 ... Got error 176 "Read page with wrong checksum" from storage engine Aria`.

`CHECK TABLE` showed eight `mysql` system tables reporting *"Table is from another system and
must be zerofilled or repaired to be usable on this system"*: `db`, `global_priv`,
`tables_priv`, `columns_priv`, `procs_priv`, `roles_mapping`, `servers`, `func`.

This is the known Aria condition that occurs when data files are copied in from a different
MariaDB installation — consistent with the `_backup_db_20260721_132146` database present on
this server. It was **not** data loss.

Resolution:
1. Backed up all 92 files of `C:\xampp\mysql\data\mysql\` (13 MB) to
   `C:\xampp\backups\mysql-systables-20261014\`, including a `users-before.txt` snapshot.
2. Tested `REPAIR TABLE` on the unused `mysql.func` first — `OK`.
3. Repaired the remaining seven tables — all `OK`.
4. Verified: `FLUSH PRIVILEGES` succeeds; all 7 user accounts and all 3 `mysql.db` grant rows
   intact; all grant tables `CHECK TABLE` `OK`.

**Why this mattered beyond PromptAtlas:** left unrepaired, these tables could have failed to
load on a future MariaDB restart, which can lock every user out of every local site.

### 2. `wp core download --skip-content=false` skipped bundled content

WP-CLI treats `--skip-content` as a boolean flag, so passing `=false` still enabled it. No
default themes or plugins were installed, leaving WordPress with no theme to render.

Re-running `wp core download --force` failed: the WordPress 7.1.3 tarball contains
`wp-includes/php-ai-client/src/.../WithRequestAuthenticationInterface.` — a filename ending in
a dot, which **Windows cannot create** — and WP-CLI's `tar` fallback is unavailable. Resolved
by installing the current default theme from wordpress.org instead
(`wp theme install twentytwentyfive`). Core itself was already complete and verifies against
checksums.

**Note for later phases:** do not run `wp core download --force` or a tarball-based core
update on this machine. Use the WordPress admin updater or the `.zip` package.

### 3. Git Bash mangled two WP-CLI arguments

Running WP-CLI through Git Bash silently rewrote POSIX-looking arguments into Windows paths:

- `--url=http://localhost/promptmarket` → `siteurl`/`home` stored as `http://localhost/htdocs/promptmarket`
- `'/%postname%/'` → `/C:/Program%20Files/Git/%postname%/`

The symptom was a 404 homepage and posts redirecting to
`http://localhost/htdocs/promptmarket/C:/Program%20Files/Git/hello-world/`.

Resolved by re-running with `MSYS_NO_PATHCONV=1` and `MSYS2_ARG_CONV_EXCL='*'`, and
referencing the phar as a Windows path (`C:/xampp/wp-cli.phar`). Verified correct afterwards.

**Standing rule for later phases:** when a WP-CLI argument begins with `/` or contains a URL
path, either run WP-CLI from PowerShell or export `MSYS_NO_PATHCONV=1` first. This is recorded
in `DEPLOYMENT.md`.

### 4. `.htaccess` was not generated

`wp rewrite structure --hard` warned that regenerating `.htaccess` needs extra configuration.
Written manually with the standard WordPress subdirectory rules and `RewriteBase /promptmarket/`.
Verified: `mod_rewrite` is loaded and `AllowOverride All` applies to `htdocs`. Pretty permalinks
return 200.

---

## Known issues and open items

### Phase 01 debt — must be settled before Phase 06

**Phase 02 was run before Phase 01.** Phase 01 requires decisions 2, 3, 4 and 8 to be resolved
first, and its deliverables (`DECISIONS.md`, `PROJECT_PLAN.md`) do not exist.

To install WordPress at all, four values had to be set. Each one below is **provisional**, was
chosen to be cheap to reverse, and is **not** a recorded decision:

| Setting | Provisional value | Reversible? |
|---|---|---|
| Locale | `en_US` | Yes — `wp language core install` / `WPLANG` |
| Comments default | `closed` | Yes — one option update |
| Timezone | `Asia/Karachi` | Yes — one option update |
| Site tagline | Placeholder sentence | Yes |

Still blocked on the project owner — these are Phase 01 items 2, 3, 4 and 8:

1. **Primary audience and geography.** Decides whether GDPR / UK GDPR / CCPA consent handling
   is needed in Phase 18, realistic AdSense RPM, content tone, and hosting region.
2. **Content language and localization.** English only with no scaffolding, English with a
   translation-ready text domain, or genuinely multilingual. This also has to resolve the
   Roman-Urdu-search-with-English-content question in Phase 09.
3. **Plugin stack.** Per slot: SEO metadata, caching, backup, security hardening, contact form,
   newsletter — plugin, theme-owned, native WordPress, or deliberately empty.
4. **Dark mode.** In scope or out. Retrofitting it into a finished design system is expensive,
   so Phase 07 cannot start without this.

Also still pending, and safe to defer: domain name (item 1), content licence (5), AI/LLM
crawler policy (7), budget ceiling (9), success metrics (10).

### Other open items

- **Optional PHP extensions not enabled.** `intl` and `sodium` are commented out in
  `C:\xampp\php\php.ini` (lines 934 and 958). WordPress Site Health recommends both. Enabling
  them requires an Apache restart, which would briefly interrupt the other sites in `htdocs`,
  so this was left for the owner to decide. Not required for Phase 02.
- **`wp db` subcommands do not work.** `wp db check` and `wp db query` need `mysql.exe` /
  `mysqlcheck.exe` on the `PATH`; `C:\xampp\mysql\bin` is not on it. Use the full binary paths,
  or add that directory to `PATH`. Verification above used the full paths.
- **Default WordPress sample content still present.** "Hello world!", "Sample Page", and the
  draft Privacy Policy page were deliberately left in place rather than deleted. Phase 19 owns
  launch content; Phase 17 owns the real policy pages.
- **Version control is not set up yet.** Phase 03 owns it. WordPress core is currently untracked
  inside the existing repository (21 untracked entries), and the repository boundary has not been
  decided. A **minimal, secrets-only `.gitignore`** was added during Phase 02 so that
  `wp-config.php` cannot be committed by accident; it is marked in-file as temporary and Phase 03
  should replace it with the deliberate policy.
- **No theme yet.** `twentytwentyfive` is a temporary placeholder so the frontend could be
  verified. Phase 06 builds the real `promptatlas` theme in the empty directory created here.

---

---

## Phase 01 — Project decisions

Completed 2026-10-14, retroactively, after the owner delegated the blocking decisions
("sab karo complete", 2026-10-14).

**Produced:** [`DECISIONS.md`](DECISIONS.md), [`PROJECT_PLAN.md`](PROJECT_PLAN.md).

| Decision | Outcome |
|---|---|
| 1. Domain | **Pending** — owner supplies. Candidates listed; nothing in code hardcodes a domain |
| 2. Audience and geography | Global English, large South Asian share. **GDPR/UK GDPR/CCPA consent required** in Phase 18. US hosting + CDN |
| 3. Language | English only, translation-ready, text domain `promptatlas`. Roman Urdu as *search input* only — resolves the Phase 09 conflict |
| 4. Plugin stack | Near-zero plugins. `promptatlas-core` owns SEO output, forms, capture. Caching/backup/security slots deliberately empty |
| 5. Content licence | **Pending** — position drafted; needs owner confirmation and a lawyer for Terms wording |
| 6. Comments | **Off everywhere.** Confirms the Phase 02 provisional setting |
| 7. AI crawler policy | Selective — allow citing retrieval crawlers, block training-only. Publish `llms.txt` |
| 8. Dark mode | **In scope.** Phase 07 must tokenise every colour |
| 9. Budget | **Pending** — estimate supplied ($5–12/mo shared, $20–35/mo managed) |
| 10. Metrics | **Pending** — targets proposed. No traffic projection given, because none would be honest |
| 11. Repository boundary | WordPress root, project code only, core ignored *(added for Phase 03)* |

Decisions 3 and 6 retroactively confirm the locale and comment settings Phase 02 applied
provisionally. The placeholder tagline is still Phase 04's to replace.

---

## Phase 03 — Tooling, documentation and version control

Completed 2026-10-14.

### Repository boundary

**Git root: `C:\xampp\htdocs\promptmarket`** (the WordPress root), tracking only
project-authored code. Reasoning in `DECISIONS.md` decision 11.

**Important discovery:** this repository already has a GitHub remote —
`https://github.com/SyedSafwanAli/PromptMarket.git`. `wp-config.php` contains the database
password and the WordPress salts, so before Phase 03 a single `git add -A && git push` would have
published them. The `.gitignore` now excludes it, verified with `git check-ignore`.

Tracked: the documentation set, `docs/`, `tools/`, `composer.json`, `composer.lock`,
`phpcs.xml.dist`, `.editorconfig`, `.gitignore`, and (once they have files)
`wp-content/themes/promptatlas/` and `wp-content/plugins/promptatlas-core/`.

Ignored: all WordPress core (verified: `wp-admin/`, `wp-includes/`, `/index.php`, `/wp-login.php`
all report `IGNORED`), `wp-config.php`, `vendor/`, uploads, logs, caches, SQL dumps, editor noise,
and `.htaccess` (it hardcodes `RewriteBase /promptmarket/`, which is wrong anywhere else).

### Tooling installed and verified

| Tool | Version | Verified by |
|---|---|---|
| PHP_CodeSniffer | 3.13.6 | `phpcs --version`, `phpcs -i` |
| WordPress Coding Standards | 3.4.1 | listed in `phpcs -i` |
| PHPCompatibilityWP | 2.1.8 | listed in `phpcs -i` |
| PHPCSUtils / PHPCSExtra | 1.2.3 / 1.5.1 | dependencies |
| Composer | 2.9.5 | `composer --version` |

Created: `phpcs.xml.dist` (ruleset), `tools/lint-php.php` (syntax check), `.editorconfig`,
`composer.json` with `lint` / `cs` / `cs:fix` / `check` scripts.

The syntax checker is written in PHP, not as a shell script, so it behaves identically in Git
Bash, PowerShell and CMD — a direct response to the Git Bash path-mangling problem from Phase 02.

### Test results — actually run

```
$ composer check
PromptAtlas PHP syntax check
PHP 8.2.12 — C:\xampp\php\php.exe
  OK    tools/lint-php.php
PASS — 1 file, no syntax errors.

. 1 / 1 (100%)
Time: 435ms; Memory: 14MB          # phpcs, exit 0
```

PHPCS initially reported 11 errors and 2 warnings, all in `tools/lint-php.php`. The errors were
false positives for a CLI script — `EscapeOutput` on `echo` to stdout, and `PrefixAllGlobals` /
`GlobalVariablesOverride` on top-level script variables. Resolved by scoping those six
WordPress-runtime sniffs to exclude `tools/` **only**; the theme and plugin keep the full standard
with no exclusions. The 2 real alignment warnings were fixed by `phpcbf`.

**Honest limitation:** these checks are verified to *work*, but at Phase 03 the only PHP file in
the project was the linter itself. They have not yet validated any site code. Re-run after
Phase 05 and Phase 06.

### Deliberately not installed

No JavaScript bundler, no PHPUnit, no Playwright, no ESLint/Stylelint — per Phase 03's rule that a
build step is a decision needing justification, not a default. Node 22.18.0 and npm 10.9.3 are
available if a later phase justifies one. Recorded in `TESTING.md` section 7.

### Documentation created

`CLAUDE.md`, `README.md`, `TESTING.md`, `CONTENT_STRATEGY.md` (stub for Phase 04),
plus `DECISIONS.md` and `PROJECT_PLAN.md` from Phase 01. `DEPLOYMENT.md`, `PROGRESS.md` and
`LAUNCH_CHECKLIST.md` already existed from Phase 02 and were updated.

---

---

## Phase 04 — Information architecture and content strategy

Completed 2026-10-14. **Produced:** [`CONTENT_STRATEGY.md`](CONTENT_STRATEGY.md), plus decisions
12 and 13 in `DECISIONS.md`, both **frozen**.

### Research honesty

Two web searches were run. **Every usable source was a vendor with a commercial interest, and no
independently validated prompt-search volume data was found.** Consequently
`CONTENT_STRATEGY.md` contains **no search volume figures at all** — the query-pattern analysis is
labelled as general-knowledge pattern analysis, not data. Section 1.2 is to be rewritten from real
Search Console data once Phase 18 provides 4–6 weeks of it.

### Decided

- **8 flat categories** — `writing-editing`, `marketing-content`, `business-strategy`,
  `coding-development`, `career-job-search`, `education-study`, `image-video`,
  `productivity-personal`. Eight divides 40–60 launch prompts into 5–8 each, so no category page
  ships thin. Flat, because a hierarchy at this volume produces two-prompt leaf pages.
- **3 taxonomies** — category (required, exactly one), tool (optional, indexable), tag (optional,
  max 5, **noindex**).
- **Collections as a post type**, not a taxonomy — they are the highest-traffic pages and need
  editorial ordering plus 150+ words of original framing, neither of which a term description
  gives. Cost: a mirrored reverse-index meta, implemented in Phase 05.
- **Prefixed URLs** — `/prompts/<slug>/`, not category-nested, because recategorising a prompt
  must not change its URL.
- **Content model field list** — specified precisely enough that Phase 05 made no naming decisions.
- **No ratings, review counts, copy counts, view counts or "trending" fields.** Each would have to
  be fabricated at launch, which the content-integrity rule forbids outright.

---

## Phase 05 — Content model

Completed 2026-10-14. **Produced:** the `promptatlas-core` plugin — 9 PHP files.

### Where the code lives, and why

**In a plugin, not the theme.** If the content model lived in the theme, switching themes would
unregister the post types: the prompts would still be rows in the database but would become
unreachable, their URLs would 404, and the admin menus would vanish. That is a data-integrity
problem, not an inconvenience. Recorded as `DECISIONS.md` decision 11's practical consequence and
in `CLAUDE.md` section 3, with the rule: *if deactivating the theme would lose data or break a
URL, it belongs in the plugin.*

### Files created

| File | Purpose |
|---|---|
| `promptatlas-core.php` | Bootstrap, constants, activation/deactivation, one-time rewrite flush |
| `includes/taxonomies.php` | The 3 taxonomies — registered at priority **5** |
| `includes/post-types.php` | `prompt` and `collection` — registered at priority **6** |
| `includes/meta.php` | 8 meta fields, sanitisers, accessors, token matching |
| `includes/admin-meta-boxes.php` | Editor panels and the save handlers |
| `includes/terms-seed.php` | Seeds the 8 categories and 8 tools on activation |
| `includes/reserved-slugs.php` | Two-layer guard against route-shadowing slugs |
| `includes/collection-sync.php` | Maintains the collection reverse index |
| `includes/queries.php` | Related prompts, collection membership |

### Three bugs found and fixed during verification

**1. Taxonomy archives all returned 404.** The `prompt` post type generates an attachment
catch-all rewrite rule, `prompts/[^/]+/([^/]+)/?$`, which matches *any* two segments under
`/prompts/` — including `/prompts/category/career-job-search/`. Because the post type was
registered first, that greedy rule sorted above the taxonomy rules, which sat unreachable below
it. Confirmed by matching the URL against `rewrite_rules` in order and seeing it resolve to
`index.php?attachment=$matches[1]`.

Fixed by registering the taxonomies at priority 5, **before** the post types at 6, so their
specific rules sort first. The ordering is load-bearing and is commented as such in the source, so
a later phase does not "tidy" it. All three archives now return 200 with the correct titles.

**2. The `Prompts/` folder shadowed the frozen `/prompts/` URL.** The phase-instruction library sat
in the web root as `Prompts/`. Windows filesystems are case-insensitive, so Apache resolved
`/prompts/` to that real directory and served a **directory listing** instead of passing the
request to WordPress — `.htaccess`'s `RewriteCond %{REQUEST_FILENAME} !-d` deliberately skips
rewriting when a directory exists.

Fixed by renaming `Prompts/` → `docs/` with `git mv` (history preserved) and updating all 15
references across 5 documents. Also added `Options -Indexes`, so no folder is ever browsable again.

> This would have been invisible on a Linux production host, where `Prompts` and `prompts` are
> different paths. It would then have appeared only on the next developer's Windows machine.

**3. The reserved-slug guard did not work on drafts.** `wp_unique_post_slug()` returns early for
draft, pending and auto-draft posts **before** applying its filter, so the guard never ran. A draft
could hold the slug `category` and show a misleading permalink until publication. Added a second
layer on `wp_insert_post_data`, which runs for every status. Verified: `category`, `prompts` and
`about` are all suffixed correctly as both draft and published.

### Also found: internal documentation was publicly readable

Not a Phase 05 bug, but discovered while fixing #2 and worth recording. Because the Git repository
root is the web root (decision 11), every tracked file was web-reachable: `/CLAUDE.md`,
`/DECISIONS.md`, `/composer.json` and the whole phase library all returned **200**. On production
that publishes the content strategy, the decision record and the tooling configuration.

`.htaccess` now denies the documentation set, the tooling config, `docs/`, `tools/`, `vendor/` and
`debug.log`. Verified: all return **403**. **`.htaccess` is not tracked in Git**, so these blocks
must be carried into the production config — added to `LAUNCH_CHECKLIST.md`.

### Verification — actually run

A 11-section verification script was executed via `wp eval-file`. **38 of 39 assertions passed on
the first run**; the one failure was bug #3 above, which was then fixed and re-verified.

| Area | Result |
|---|---|
| Angle brackets survive sanitisation | **PASS** — `<strong>`, `List<String>` and `<your name here>` all preserved |
| Proof the custom sanitiser is necessary | **PASS** — `sanitize_textarea_field()` destroys all three, producing `Use List for the type.` |
| Sanitiser hygiene | **PASS** — null bytes stripped, CRLF→LF, blank lines collapsed, trimmed, non-scalar → `''` |
| Variable normalisation | **PASS** — `job title` → `JOB_TITLE`, `weird!!name##` → `WEIRD_NAME`, nameless rows dropped, capped at 12 |
| Token/variable mismatch detection | **PASS** — no undeclared or unused on either real prompt |
| Related-ID validation | **PASS** — rejects non-existent IDs, a page ID, duplicates; caps at 3; parses comma strings |
| **Subscriber cannot write prompt meta** | **PASS** — `auth_callback` returns false |
| **Administrator can** | **PASS** |
| **Save handler ignores POST with no nonce** | **PASS** — meta unchanged |
| **Save handler ignores POST with an invalid nonce** | **PASS** — meta unchanged |
| Programmatic save does not wipe meta | **PASS** — `wp_update_post` with empty `$_POST` leaves meta intact |
| Reserved slug guard, draft and published | **PASS** (after fix) |
| Collection reverse index | **PASS** — adds, **clears stale rows on removal**, preserves editorial order `[7,6]`, clears on delete |
| Post types and taxonomies registered | **PASS** |
| Related-prompts query | **PASS** — returns an array, never includes itself |

URL resolution, by HTTP request:

| URL | Code | Title |
|---|---|---|
| `/prompts/` | 200 | `Prompts – PromptAtlas` |
| `/prompts/<slug>/` ×2 | 200 | correct, UTF-8 title intact (`résumé`) |
| `/prompts/category/career-job-search/` | 200 | `Career & Job Search – PromptAtlas` |
| `/prompts/tool/chatgpt/` | 200 | `ChatGPT – PromptAtlas` |
| `/prompts/tag/resume/` | 200 | `resume – PromptAtlas` |
| `/collections/` | 200 | `Collections – PromptAtlas` |

The prompt archive genuinely lists both prompts (slug matches found in the HTML).

**`wp-content/debug.log` does not exist** — with `WP_DEBUG` on, no notice, warning or error was
emitted throughout. `composer check` passes: 10 files, 0 syntax errors, 0 PHPCS errors, 0 warnings.

### The two draft prompts

Phase 05 requires real content to exercise the model. Two genuine, originally written prompts were
created — *Rewrite a résumé for a specific job description* (#6) and *Explain unfamiliar code line
by line* (#7) — each with the full four-part explanation, real placeholder variables, category,
tools and tags.

**Example output was deliberately left empty on both.** `CONTENT_STRATEGY.md` 6.4 requires a prompt
to be run in a real tool before its output is shown, and I have not run these. An empty field is
honest; an invented example is not.

> **These two prompts are published locally but have NOT been test-run against a live model.** The
> site is `blog_public = 0` on localhost, so nothing is public. Added to `LAUNCH_CHECKLIST.md`:
> every prompt must be test-run before launch.

### Not yet tested

- **Theme-switch survival was verified by construction, not by switching.** Registration is in the
  plugin, and the post types and all 2 published prompts are intact under the active theme — but
  only one theme is installed, so no actual switch has happened. **Phase 06 will perform the real
  switch test** once the `promptatlas` theme exists.
- **The admin editing UI has not been clicked through by hand.** The save handlers were tested
  directly, including both nonce-rejection paths, but nobody has used the meta box in a browser.
  Worth doing when the owner first writes a prompt.

---

---

## Phase 06 — Theme foundation

Completed 2026-10-14. The `promptatlas` theme is built and active.

**Files:** `style.css` (header only), `functions.php`, `inc/` (setup, assets, branding,
template-tags, comments-off), templates (`index`, `front-page`, `page`, `single`, `archive`,
`search`, `404`, `searchform`), `template-parts/` (card, card-prompt, card-collection),
`assets/css/` (tokens, theme, components), `assets/js/theme-toggle.js`.

**Dark mode mechanism**, established now rather than retrofitted: no attribute follows the OS,
`data-theme="dark"`/`"light"` overrides it, and the media query is guarded with
`:root:not([data-theme="light"])` so an explicit light choice beats an OS set to dark. A small
inline head script applies the saved preference before first paint — deferring it would flash the
light theme on every load for dark-mode visitors.

**Deferred Phase 05 test now passed for real:** after switching from `twentytwentyfive` to
`promptatlas`, the post types stayed registered and all published prompts and 8 categories
survived.

**Bug found and fixed:** the homepage rendered **two `<h1>` elements** — the site title became an
h1 on the front page while the intro heading was also one. The site name is now never an h1.
Verified: exactly one h1 on all 8 page types.

**Also fixed:** the comments feed link remained in `<head>`. It is printed by `feed_links()`, not
`feed_links_extra()`, so it needed `feed_links_show_comments_feed` — removing the action would have
taken the useful posts feed with it.

---

## Phase 07 — Design system and brand assets

Completed 2026-10-14. Full documentation in [`DESIGN_SYSTEM.md`](DESIGN_SYSTEM.md).
Live reference at **`/style-reference/`** (noindex).

**Contrast was computed, not assumed**, and the numbers changed three design decisions:

1. **White on the orange button fails AA** at 3.14:1. The accent button now carries near-black
   text at 5.46:1. Orange as *text* routes through a darkened step at 7.35:1.
2. **Control borders needed their own token.** The decorative hairline is 1.55:1 — fine for a
   separator, which is exempt, but not for anything whose boundary identifies a control under
   WCAG 2.2 SC 1.4.11. `--color-border-control` is 3.64:1 light / 3.96:1 dark.
3. **The focus ring was darkened** from raw orange's 3.01:1 — a technical pass, too close to rely
   on — to 4.33:1.

Burgundy and olive measure 2.09:1 and 3.01:1 on the dark background and are replaced by light
steps. Those two failing rows are kept in the contrast table as documentation of why dark mode is
not the light palette inverted.

**Brand assets:** `mark.svg`, `favicon.svg`, `favicon-32.png`, `icon-192.png`, `icon-512.png`,
`apple-touch-icon.png`, `og-default.png` (1200×630). The mark is square brackets with a filled
block between them — the placeholder convention the whole content model uses.

**Stated outstanding:** a designed wordmark. The current one is a system-font treatment, which
`DESIGN_SYSTEM.md` says plainly. An SVG wordmark with `<text>` was deliberately not shipped: it
renders in whatever font the viewer has, so it would have looked finished while being worse. Also
outstanding: `favicon.ico` (no ICO writer available; modern browsers use the SVG and PNGs).

---

## Phase 08 — Frontend templates

Completed 2026-10-14.

### Templates added

| Template | Purpose |
|---|---|
| `single-prompt.php` | Prompt detail — the page the site exists for |
| `archive-prompt.php` | `/prompts/` listing |
| `taxonomy.php` | **One** template for category, tool and tag archives |
| `single-collection.php` | Collection with its prompts in editorial order |
| `template-parts/prompt-block.php` | The prompt text and copy control |
| `assets/js/copy.js` | Working copy-to-clipboard |

`taxonomy.php` serves all three taxonomies rather than three near-identical files; only the framing
sentence differs, and that is chosen by taxonomy inside the template.

### Page order on a prompt detail page

Title → summary → **prompt + copy** → what to replace → explanation → example output → metadata →
related. The explanation sits **below** the prompt deliberately: `CONTENT_STRATEGY.md` 2 is
explicit that visitors come to copy, and nobody reads an essay to reach a copy button.

### Two scope decisions worth flagging

**1. The copy button genuinely works.** Phase 08 says to build interfaces only and leave behaviour
to Phase 10. But the standing rule forbids any feature that exists as a non-functional visual
placeholder, and a copy button that does not copy is exactly that. `copy.js` implements the copy
action with a selection-based fallback for non-secure contexts (plain `http://` staging, where
`navigator.clipboard` is unavailable). **Phase 10 still owns Recently Copied and Saved.**

**2. No "featured prompt" flag was invented.** Phase 08 asks for a featured section, but
`CONTENT_STRATEGY.md` 4.1 froze the field list and does not include one. Adding a field in a
template would make Phase 05's model and the documentation stop describing reality. Collections
already *are* the project's curation mechanism — hand-picked, hand-ordered, with written framing —
so the homepage's curated section is driven by them, and renders nothing when none are published.
If a per-prompt featured flag is wanted, it should be a recorded content-model decision.

### Query counts — Phase 15 baseline

Measured with temporary instrumentation, since removed.

| Template | Queries |
|---|---|
| Homepage | 35 |
| Single prompt | 31 |
| Search results | 26 |
| Prompt archive | 25 |
| Tag archive | 24 |
| Category archive | 24 |
| Tool archive | 23 |
| Page | 20 |
| Style reference | 18 |
| 404 | 17 |
| Collection archive | 14 |

No N+1 pattern. The homepage is highest because it runs three listings; every listing is paginated.

### Edge cases — all 9 tested, with real results

| Case | Result |
|---|---|
| **XSS probe** `<script>alert('xss')</script>` in prompt text | **Escaped.** 0 raw occurrences, 1 escaped. Confirms escape-then-highlight order is correct |
| Non-Latin text — اردو, 中文, Rôle, français, ✅ | All rendered correctly |
| `List<String>` and `a<b` in prompt text | Survived as entities — the custom sanitiser works end to end |
| Very long multi-line prompt (40+ lines) | Whitespace preserved: 4-space indent and a literal tab both intact; both `[TOKEN]`s highlighted; long unbroken word present and wrapped |
| Very long title | Wraps in the card grid |
| Bare prompt — no excerpt, tags, tools, category or image | 200, exactly one h1, related fallback shown, **no empty metadata groups rendered** |
| Empty category | 200, empty state, editor's term description used |
| Page 2 of a paginated archive | 200, current page marked; page 99 correctly 404s |
| **No published prompts at all** | Homepage, archive and category all show honest empty states. Zero fabricated "trending/popular/most copied" content. The "Add a prompt" action correctly does **not** appear for logged-out visitors |
| Search with no results | "No results", reports 0, offers a real next action |

All fixtures were deleted afterwards; the site is back to 2 published prompts and 8 categories.

**Zero PHP notices** throughout with `WP_DEBUG` on. `composer check` passes on **35 files**.

### Visually present but not yet functional

Nothing. The two places this could have happened were handled rather than shipped dead:
the copy button was implemented, and the featured section is driven by real collections.

Deliberately not built here, per the phase boundaries: search ranking and Roman Urdu synonyms
(Phase 09), Recently Copied and Saved (Phase 10), collection curation UI (Phase 11), SEO meta
output (Phase 12), and the ad slots are styled containers only with no ad code (Phase 18).

---

## Exact next step

**Phase 09 — Search and discovery** (`docs/09-search-and-discovery.md`).

Phase 09 must implement the real search behaviour — including the Roman-Urdu-query-to-English-
content synonym mapping settled in decision 3 — and the zero-result logging that
`CONTENT_STRATEGY.md` 6.5 depends on.

### Carried forward, not yet done

- **No browser testing has happened at any point.** Verification has been HTTP requests and HTML
  inspection. No browser automation is available here, so rendering, the keyboard path, the theme
  toggle, the mobile menu and the copy button have **not** been exercised in a real browser.
  `/style-reference/` exists to make that review quick when someone can do it.
- The admin editing UI has not been clicked through by hand.
- The two published prompts have **not been test-run against a live model** — in
  `LAUNCH_CHECKLIST.md`.
