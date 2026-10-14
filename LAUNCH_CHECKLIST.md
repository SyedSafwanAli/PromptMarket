# PROMPTATLAS — LAUNCH CHECKLIST

Every time a phase defers something to launch, add it here. Nothing is removed from this file —
items get checked off.

Started: Phase 02, 2026-10-14.

---

## Blocking — the site must not go live until these are done

### Indexing and crawling

- [ ] **Enable search engine visibility.** Development set `blog_public = 0`, so every page
      currently serves `<meta name='robots' content='noindex, nofollow' />`. Turn this on at
      Settings → Reading (uncheck "Discourage search engines"), or
      `wp option update blog_public 1`.
      *Deferred by: Phase 02. Verify by requesting the homepage and confirming the `noindex`
      meta tag is gone.*
- [ ] Confirm `robots.txt` matches the AI/LLM crawler policy decided in Phase 01 item 7.
      *Deferred by: Phase 02 → owned by Phase 12.*

### Configuration

- [ ] Set `WP_ENVIRONMENT_TYPE` to `production`.
- [ ] Set `WP_DEBUG` and `SCRIPT_DEBUG` to `false`; confirm `WP_DEBUG_DISPLAY` is `false`.
- [ ] Turn off `WP_DEBUG_LOG`, or move the log outside the web root.
- [ ] Replace every local credential. The local database password and the local WordPress
      administrator password must not exist on the production server.
- [ ] Update `siteurl` and `home` from `http://localhost/promptmarket` to the real domain, and
      verify the permalink structure survived the move.
      *Deferred by: Phase 02.*
- [ ] Serve the site over HTTPS and confirm there is no mixed content.

### Security of the deployed configuration

- [ ] **Carry the `.htaccess` protection blocks into the production config.** `.htaccess` is *not*
      tracked in Git (it hardcodes the local `RewriteBase`), so the protections added in Phase 05
      will not travel with a deploy. Production must deny: the documentation set (`CLAUDE.md`,
      `DECISIONS.md`, `PROGRESS.md`, `PROJECT_PLAN.md`, `CONTENT_STRATEGY.md`, `TESTING.md`,
      `DEPLOYMENT.md`, `LAUNCH_CHECKLIST.md`, `README.md`), the tooling config (`composer.json`,
      `composer.lock`, `phpcs.xml.dist`, `.editorconfig`, `.gitattributes`, `.gitignore`), and the
      `docs/`, `tools/` and `vendor/` directories. Also set `Options -Indexes`.
      *Deferred by: Phase 05, which found all of these publicly readable and returning 200.*
      **Better still:** Phase 22 should not deploy these files at all — `.gitattributes` already
      marks them `export-ignore`.
- [ ] Verify after deploy: requesting `/CLAUDE.md`, `/DECISIONS.md` and `/docs/` returns 403 or
      404, not 200.

### Content

- [ ] **Test-run every published prompt against a real model.** `CONTENT_STRATEGY.md` 6.4 requires
      it, and the two prompts created in Phase 05 (#6 and #7) are published locally **without
      having been run**. Either test them and add real example output, or unpublish them.
      *Deferred by: Phase 05.*
- [ ] Delete the default WordPress sample content: the "Hello world!" post and the "Sample Page"
      page. *Deferred by: Phase 02 — left in place deliberately so nothing was deleted without
      approval.*
- [ ] Replace the draft WordPress Privacy Policy page with the real one from Phase 17.
- [ ] Replace the placeholder tagline ("Discover, search, copy and save useful AI prompts.")
      with the final approved wording. *Deferred by: Phase 02 — Phase 04 owns the real copy.*

---

## Confirm before launch

- [ ] Confirm the final locale. Phase 02 installed `en_US` provisionally, before the Phase 01
      language decision was made.
- [ ] Confirm the site timezone. Phase 02 set `Asia/Karachi` provisionally.
- [ ] Confirm the comment policy. Phase 02 set comments and pingbacks to `closed` provisionally,
      pending Phase 01 item 6.
- [ ] Remove the temporary `twentytwentyfive` theme once the `promptatlas` theme from Phase 06
      is active and verified.
- [ ] Decide whether `intl` and `sodium` should be enabled in PHP on the production host.
      They are disabled locally; WordPress Site Health recommends both.

---

## Phase ownership

Later phases add to this list. The phases that are expected to contribute:

- Phase 03 — version control and the `.gitignore` policy that replaces the temporary one
- Phase 12 — technical SEO, sitemaps, canonical URLs, crawler policy
- Phase 13 — email deliverability for the contact form
- Phase 15 — performance budgets measured on production, not localhost
- Phase 16 — security and accessibility sign-off
- Phase 17 — legal and policy pages
- Phase 18 — AdSense readiness, analytics, consent handling
- Phase 22 — staging, deployment, backups, monitoring
