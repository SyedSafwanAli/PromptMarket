# PHASE 03 — TOOLING, DOCUMENTATION AND VERSION CONTROL

**Prerequisites:** Phase 02 complete.
**Produces:** the Git repository, the project documentation set, and the code-quality tooling.
**Do not:** write theme code in this phase.

## Goal

Set up the scaffolding that every later phase depends on, before there is any code to make a mess of. This phase exists because "run PHPCS if it is installed" is useless if nobody ever installs it.

## Step 1: Decide the repository boundary

This is an architectural decision, so make it deliberately and record it in `DECISIONS.md`.

Evaluate and choose:
- Repository root at the theme directory only
- Repository root at `wp-content/` covering the theme and any site-specific plugin
- Repository root at the WordPress root

State the trade-offs: what gets versioned, what gets deployed, how uploads and the database are handled, and how a future site-specific plugin fits in. Recommend one and explain why.

## Step 2: Initialize version control

Initialize Git at the chosen root if it is not already initialized. Inspect any existing repository before changing its configuration.

Create a `.gitignore` that excludes:
- `wp-config.php` and any file containing credentials
- Local environment files and `.env` style files
- `wp-content/uploads/`
- Backups, database dumps, logs, and caches
- Editor and OS noise
- Generated dependencies

Do not accidentally ignore source files required by the theme. After writing `.gitignore`, verify with `git status` that the theme source files are actually tracked.

Record in `CLAUDE.md`:
- The commit message convention to use
- The branching approach for this project
- That the database is never committed
- That secrets are never committed

Make the initial commit.

## Step 3: Create the documentation set

Create in the repository root, with real initial content rather than empty placeholders:

- `CLAUDE.md` — the single most useful file in this project. It must contain: the project summary, the local site URL, the WordPress and PHP versions, the theme path, the repository boundary, the brand palette, the coding conventions, how to run the syntax and standards checks, the documentation map, and the standing rules a new session needs.
- `README.md` — what PromptAtlas is, how to run it locally, where the theme lives.
- `PROJECT_PLAN.md` — update from Phase 01.
- `PROGRESS.md` — the running log.
- `DECISIONS.md` — update from Phase 01.
- `CONTENT_STRATEGY.md` — a stub to be filled in Phase 04.
- `TESTING.md` — how to run every check available in this project.
- `DEPLOYMENT.md` — update with the Phase 02 environment facts.
- `LAUNCH_CHECKLIST.md` — start it now and add to it every time a later phase defers something to launch. Seed it with the search-engine-visibility reminder from Phase 02.

## Step 4: Set up code quality tooling

Install and verify what is practical on this machine:
- A PHP syntax check script covering every PHP file in the theme, using the PHP executable recorded in Phase 02
- PHP_CodeSniffer with the WordPress Coding Standards ruleset, if Composer is available
- `.editorconfig` with the WordPress conventions for PHP, CSS, and JavaScript

Do not install a JavaScript toolchain, a bundler, or a test framework in this phase. The theme uses minimal vanilla JavaScript, and a build step is a decision that needs justification, not a default.

If a tool cannot be installed, say so plainly, record the limitation in `TESTING.md`, and provide the manual alternative.

## Step 5: Verification

Verify by actually running them:
- `git status` is clean and no secrets are tracked
- `git log` shows the initial commit
- The PHP syntax check script runs and reports success on the current files
- PHPCS runs, if installed, and its output is understood
- Every documentation file exists and contains real content

## Completion report

Report the repository root chosen and why, the files created, the tooling installed with the exact verification commands and their real output, the tooling that could not be installed and why, and the contents of `LAUNCH_CHECKLIST.md` so far.

Do not claim a tool is installed and working unless you ran it and saw its output.
