# PHASE 06 — THEME FOUNDATION

**Prerequisites:** Phase 02 and Phase 03 complete. Phase 05 recommended, so the templates have a real post type to reference.
**Produces:** a working, activated custom theme with the document skeleton, navigation, and accessibility baseline.
**Do not:** build the design system here (Phase 07) or the full page layouts (Phase 08).

Read the master instructions and the current project documentation. Inspect the real WordPress installation before modifying files.

## Goal

Create a working, maintainable custom classic WordPress theme named PromptAtlas.

## Requirements

Use PHP templates, WordPress hooks, CSS, and minimal vanilla JavaScript. Do not add React, Next.js, a page builder, or a frontend build system unless a measured requirement justifies it and I approve it.

Create a clean theme structure with:
- `style.css` with valid WordPress theme metadata
- `functions.php`
- `index.php`
- `header.php`
- `footer.php`
- `front-page.php`
- `page.php`
- `single.php`
- `archive.php`
- `search.php`
- `404.php`
- `assets/css/`
- `assets/js/` only if needed
- `template-parts/` for reusable UI fragments
- `languages/` only if the Phase 01 language decision requires it

Do not create empty or duplicate templates without purpose. Follow the WordPress template hierarchy and ensure there is a functional fallback template.

## Theme setup

Implement appropriate support for:
- `title-tag`
- `post-thumbnails` where relevant
- `html5`
- `custom-logo` if the design needs it
- `menus`
- WordPress-generated body classes
- Translation-ready strings with a consistent text domain, per the Phase 01 language decision

Register menus through WordPress APIs. Enqueue CSS and JavaScript through WordPress hooks. Use file modification versions for local theme assets where appropriate without adding expensive work to every request.

Use `wp_head()`, `wp_footer()`, `wp_body_open()`, `language_attributes()`, `body_class()`, and semantic document structure correctly.

If the Phase 01 decision put dark mode in scope, establish the mechanism now — the attribute or class that switches themes, and the no-JavaScript default — so Phase 07 can define both token sets against it. Do not bolt this on later.

## Accessibility

Implement:
- A skip-to-content link
- Semantic header, navigation, main, and footer landmarks
- Logical heading order
- Keyboard-visible focus styles
- Accessible menu controls
- Accessible names for icon-only controls
- Sufficient color contrast
- `prefers-reduced-motion` support
- No interaction that depends on hover alone

## Security

Use correct escaping functions for output. Sanitize and validate any values accepted from requests. Do not create state-changing actions without appropriate permission checks and nonces. Never edit WordPress core files.

## Performance

Use a system font stack initially. Avoid remote font dependencies, third-party scripts, large images, and unnecessary JavaScript. Keep the theme assets small. Do not enqueue an asset the theme does not use.

## Initial appearance

Create a restrained but polished starter layout using the PromptAtlas palette:
- Burgundy `#8B2626`
- Orange `#EF6905`
- Pale butter yellow `#F1E5A1`
- Olive green `#486C2F`
- Warm off-white `#FCFAF6`
- Charcoal and neutral supporting colors

Build only the reusable foundation: the site header, the main container, basic content presentation, and the footer. Do not build the homepage sections.

## Verification

Activate the theme in the local WordPress dashboard if your tools allow it. Otherwise explain the exact action needed.

Run the Phase 03 PHP syntax check across every theme PHP file and report its real output. Run PHPCS if it was installed in Phase 03.

With WordPress debugging enabled, load the homepage, a page, a single post, an archive, a search result, and a 404, and report any notice or warning.

Test the keyboard path: tab from the top of the page, confirm the skip link works and focus is always visible.

Test desktop and mobile layouts. Do not claim browser tests were run if no browser testing tool was available.

## Completion report

Report the files created, the verification commands and their actual output, the accessibility checks performed, known limitations, and confirmation that Phase 07 is next.
