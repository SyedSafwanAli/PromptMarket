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
| 04 — Information architecture | **In progress** | — | — |
| 05–25 | Not started | — | — |

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
| Unrelated project files overwritten | **None.** `Prompts/` is untouched; other `htdocs` sites untouched |

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

Tracked: the documentation set, `Prompts/`, `tools/`, `composer.json`, `composer.lock`,
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

## Exact next step

**Phase 04 — Information architecture and content strategy**
(`Prompts/04-information-architecture.md`).

Phase 04 fills `CONTENT_STRATEGY.md` and must settle the URL structure, taxonomy, and the prompt
page field list — because **Phase 05 registers exactly that field list as post meta** and cannot
start without it.
