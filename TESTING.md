# PROMPTATLAS — TESTING

Every check available on this project, how to run it, and what its output means.

**Rule: never record a check as passed unless it was actually run and its output was read.**
If a tool is unavailable, the manual alternative is given below — use it and say which you used.

---

## 1. Quick reference

Run from the repository root, `C:\xampp\htdocs\promptmarket`:

| Check | Command | Exit 0 means |
|---|---|---|
| PHP syntax | `composer lint` | Every project PHP file parses |
| Coding standards | `composer cs` | No WPCS violations |
| Both | `composer check` | Both of the above |
| Auto-fix style | `composer cs:fix` | — (rewrites files) |

Direct equivalents, if Composer's script runner is not wanted:

```bash
C:/xampp/php/php.exe tools/lint-php.php
./vendor/bin/phpcs
./vendor/bin/phpcbf
```

---

## 2. PHP syntax check

```bash
composer lint
```

Runs `php -l` over every `.php` file in `wp-content/themes/promptatlas/`,
`wp-content/plugins/promptatlas-core/` and `tools/`. WordPress core, third-party code and
`vendor/` are never checked.

Written in PHP rather than as a shell script deliberately: it behaves identically in Git Bash,
PowerShell and CMD, and it uses the PHP binary running it, so there is no hardcoded interpreter
path to go stale.

**Expected output:**

```
PromptAtlas PHP syntax check
PHP 8.2.12 — C:\xampp\php\php.exe
------------------------------------------------------------
  OK    tools/lint-php.php
------------------------------------------------------------
PASS — 1 file, no syntax errors.
```

Exit `0` = all valid. Exit `1` = at least one file failed, and the error with its line number is
printed for each.

A directory that does not exist yet is reported as `skipped`, not as a failure — expected until
the theme and plugin have files.

**Manual alternative:** `C:/xampp/php/php.exe -l path/to/file.php` per file.

### Last run — 2026-10-14 (Phase 03)

```
PASS — 1 file, no syntax errors.
```

Only `tools/lint-php.php` existed. `promptatlas-core` was reported as skipped. **This result does
not mean the theme or plugin are verified — they had no PHP files yet.** Re-run after Phase 05
and Phase 06.

---

## 3. WordPress Coding Standards (PHPCS)

```bash
composer cs
```

| Tool | Version |
|---|---|
| PHP_CodeSniffer | 3.13.6 |
| WordPress Coding Standards (WPCS) | 3.4.1 |
| PHPCompatibilityWP | 2.1.8 |
| PHPCSUtils / PHPCSExtra | 1.2.3 / 1.5.1 |

Configured by `phpcs.xml.dist`. Scans only the theme, the plugin and `tools/`.

`composer.lock` **is tracked**, so `composer install` reproduces these exact tool versions. A
linter that silently changes version between machines produces different results for the same
code, which defeats the point of having one. `vendor/` itself is ignored and is never deployed.

### Exit codes

| Code | Meaning |
|---|---|
| `0` | Clean |
| `1` | Warnings only |
| `2` | **Errors** — must be fixed |

### What is set to error on this project

Promoted from warning to error, because a security regression must fail the check rather than
scroll past in a wall of style notices:

- `WordPress.Security.EscapeOutput` — output not escaped
- `WordPress.Security.NonceVerification` — state change without a nonce
- `WordPress.Security.ValidatedSanitizedInput` — unvalidated input
- `WordPress.DB.PreparedSQL` and `.PreparedSQLPlaceholders` — unprepared queries

### Configured exclusions, and why

| Exclusion | Scope | Reason |
|---|---|---|
| `WordPress.Files.FileName.InvalidClassFileName` | project-wide | The theme uses WordPress template-hierarchy filenames (`single-prompt.php`), which WPCS's `class-*.php` rule would break |
| `EscapeOutput`, `PrefixAllGlobals`, `GlobalVariablesOverride`, `DiscouragedPHPFunctions`, `AlternativeFunctions`, `DevelopmentFunctions` | **`tools/` only** | Standalone CLI scripts that never load WordPress. They write to stdout, not a browser, so `esc_html()` does not apply, and their top-level variables are script locals, not WordPress globals |

**The theme and the plugin have no exclusions.** Everything that serves an HTTP request is held
to the full standard. If a future phase wants to exclude a sniff for theme or plugin code, that
needs a recorded justification — not a quiet edit to `phpcs.xml.dist`.

### Other configuration

- Text domain enforced: **`promptatlas`** (decision 3)
- Required global prefixes: `promptatlas`, `PromptAtlas`, `PROMPTATLAS`
- `minimum_wp_version` = `7.1` — flags functions newer than the installed WordPress, and
  deprecated ones
- `testVersion` = `8.0-` — the production host's PHP version is not chosen yet (Phase 22), so the
  code must not silently depend on 8.2-only syntax. **Narrow this once the host is known.**

**Manual alternative:** `./vendor/bin/phpcs --standard=WordPress path/to/file.php`

### Last run — 2026-10-14 (Phase 03)

```
. 1 / 1 (100%)
Time: 662ms; Memory: 16MB
```

Exit `0`, clean. Two alignment warnings were auto-fixed by `phpcbf` first. As with the syntax
check, only `tools/lint-php.php` existed at the time.

---

## 4. WordPress install integrity

```bash
C:/xampp/php/php.exe C:/xampp/wp-cli.phar core verify-checksums
```

Compares every core file against the official WordPress checksums. Catches a corrupted download,
a partial update, or a modified core file.

**Last run 2026-10-14:** `Success: WordPress installation verifies against checksums.`

Database table check:

```bash
C:/xampp/mysql/bin/mysqlcheck.exe -u root promptatlas
```

**Last run 2026-10-14:** all 12 tables `OK`.

> `wp db check` does **not** work on this machine — it shells out to `mysqlcheck.exe`, which is
> not on `PATH`. Use the full path above.

---

## 5. Manual HTTP verification

No automated browser or HTTP test suite is set up (see section 7). Until one exists, verify by
request:

```bash
for u in "/" "/wp-login.php" "/wp-json/" "/feed/"; do
  printf "%-16s -> %s\n" "$u" \
    "$(curl -s -o /dev/null -w '%{http_code}' "http://localhost/promptmarket$u")"
done
```

**Last run 2026-10-14** — all `200`. Admin login was separately verified with a real POST to
`wp-login.php`: `302` with a `wordpress_logged_in` cookie, and `/wp-admin/` then returning `200`.

### PHP error log

`WP_DEBUG_LOG` is on and `WP_DEBUG_DISPLAY` is off, so PHP notices go to a file, never to the
screen:

```bash
cat wp-content/debug.log
```

**A clean run leaves no `debug.log` at all.** If the file exists, something emitted a notice —
read it. As of 2026-10-14 it does not exist.

Apache errors: `C:\xampp\apache\logs\error.log`.

---

## 6. Accessibility and performance

No tooling installed yet. **Phase 15** owns performance measurement and **Phase 16** owns the
accessibility audit. Until then:

- **Lighthouse** — Chrome DevTools, available with no install. Treat localhost numbers as
  indicative only; there is no network latency locally, which flatters every metric. Real
  measurement needs production (Phase 15 / Phase 24).
- **Keyboard check** — Tab through every interactive element. Focus must always be visible and
  the order must be logical.
- **Contrast** — must pass in **both** light and dark themes (decision 8). Dark mode is not the
  light palette inverted.
- **axe DevTools / WAVE** — browser extensions, no project install needed.

---

## 7. Deliberately not installed

Per Phase 03, which forbids adding a JavaScript toolchain as a default:

| Tool | Why not |
|---|---|
| A JS bundler (Webpack, Vite, Rollup) | The theme uses minimal vanilla JavaScript. A build step is a decision requiring justification, not a default. It would also mean committing build output or adding a build to every deploy |
| PHPUnit / WP test suite | No business logic exists to unit test yet. Worth revisiting when `promptatlas-core` has real logic — particularly the Phase 13 form handling and the Phase 09 search query building |
| Playwright / Cypress | Phase 20 is explicitly black-box manual QA. Revisit only if regressions start recurring |
| ESLint / Stylelint | Reasonable to add once there is more than a trivial amount of CSS and JS. Not yet |
| A JS test runner | Nothing to test yet |

Node.js 22.18.0 and npm 10.9.3 **are** available on this machine, so any of these could be added
later. Adding one requires a recorded decision, per `CLAUDE.md` section 10.

---

## 8. Known testing gaps

Stated plainly so nobody mistakes a passing check for broader coverage:

1. **No automated functional tests.** Nothing verifies that a prompt page renders correctly, that
   copy-to-clipboard works, or that search returns the right results. All of that is manual until
   Phase 20.
2. **No cross-browser testing.** Verification so far is `curl` and one browser.
3. **No real-device mobile testing.** DevTools emulation is not a phone.
4. **No performance baseline.** Nothing has been measured, so there is nothing to regress against.
   Phase 15 establishes it; Phase 24 owns the regression process.
5. **The syntax and standards checks have only ever run against one CLI file.** They are verified
   to *work*; they have not yet verified any site code, because none existed at Phase 03.

---

## 9. Pre-commit checklist

1. `composer check` passes.
2. `wp-content/debug.log` does not exist, or its contents are understood and resolved.
3. The affected pages were loaded in a browser and actually looked at.
4. `git status` shows no secrets, no `vendor/`, no uploads, no database dump.
5. `PROGRESS.md` reflects reality.
