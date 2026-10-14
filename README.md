# PromptAtlas

An AI prompt discovery platform built on WordPress. Visitors find, search, copy and save useful
AI prompts.

**Status: in development.** Not deployed. There is no public site yet.

---

## What it does

PromptAtlas is a content site, not an app. The value is a well-organised, genuinely useful,
original collection of AI prompts that people can find through search and copy in one click.

Planned for the first release:

- A polished homepage
- Prompt listing and prompt detail pages
- Curated collections and category hub pages
- Prompts, categories and tags managed in WordPress
- Search with useful filters
- Copy-to-clipboard, Recently Copied, and Saved prompts
- Responsive navigation and SEO-friendly templates
- A working contact path

**Deliberately not in the first release:** creator accounts, community submissions, moderation
queues, payments or payouts, semantic/vector search, a JavaScript-framework frontend, or a mobile
app. See `PROJECT_PLAN.md`.

---

## Architecture

WordPress with a custom classic theme and one site-specific plugin:

```
wp-content/themes/promptatlas/         Templates, styling, presentation
wp-content/plugins/promptatlas-core/   Post types, taxonomies, meta, SEO output, forms
```

PHP templates, WordPress hooks and APIs, CSS, and minimal vanilla JavaScript. No React, no
headless CMS, no bundler, no build step, no page builder. The split exists so that content and
URLs survive a theme change — anything whose loss would break a URL or lose data lives in the
plugin.

---

## Running it locally

### Requirements

Already installed and configured on the development machine:

- XAMPP — Apache 2.4.58, PHP 8.2.12, MariaDB 10.4.32
- WordPress 7.1.3
- WP-CLI 2.12.0 (`C:\xampp\wp-cli.phar`)
- Composer 2.9.5 (development tooling only)

### Steps

1. Start **Apache** and **MySQL** from the XAMPP Control Panel (`C:\xampp\xampp-control.exe`).
2. Open **http://localhost/promptmarket**
3. Admin: **http://localhost/promptmarket/wp-admin**

Credentials are in `C:\Users\Syed Safwan\promptatlas-local-credentials.txt`, intentionally kept
outside this repository. They are local-only and must never be reused anywhere else.

### Development tooling

```bash
composer install     # PHP_CodeSniffer + WordPress Coding Standards (dev only)
composer lint        # PHP syntax check across the theme and plugin
composer cs          # WordPress Coding Standards
composer check       # both
```

The site itself has **no Composer runtime dependencies** — `vendor/` is tooling and is not
deployed. See `TESTING.md`.

### Setting it up from scratch

The database and WordPress install are not in this repository (code is tracked; content and
credentials are not). To rebuild from nothing you would create a `promptatlas` database and a
dedicated database user, install WordPress, then symlink or copy the theme and plugin into
`wp-content/`. `DEPLOYMENT.md` records the exact environment facts and the commands used.

---

## Repository layout

```
├── CLAUDE.md                Conventions and standing context — read first
├── PROGRESS.md              Where the work stands, test results, next step
├── DECISIONS.md             Locked decisions with reasoning
├── PROJECT_PLAN.md          Phase roadmap and dependencies
├── CONTENT_STRATEGY.md      Audience, taxonomy, editorial standards
├── TESTING.md               Every check and how to run it
├── DEPLOYMENT.md            Environment facts and deployment
├── LAUNCH_CHECKLIST.md      Everything deferred to launch
├── docs/                 The 25 phase instruction files (reference, not code)
├── tools/                   Development scripts
├── phpcs.xml.dist           Coding standards ruleset
└── wp-content/
    ├── themes/promptatlas/
    └── plugins/promptatlas-core/
```

WordPress core is **not** tracked. It is installed by WP-CLI and verified with
`wp core verify-checksums`. Reasoning in `DECISIONS.md` decision 11.

---

## Contributing

Single-developer project at present. Before committing:

1. `composer check` must pass.
2. `PROGRESS.md` must reflect reality — no phase is complete until it does.
3. No secrets in the diff. This repository has a GitHub remote.

Read `CLAUDE.md` section 4 first. The security rules there are requirements, not style
preferences.

---

## Licence

Proprietary. All rights reserved. The content licence for published prompts is a separate
question and is still being settled — see `DECISIONS.md` decision 5.
