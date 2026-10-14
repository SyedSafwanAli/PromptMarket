# PHASE 22 — STAGING AND DEPLOYMENT

**Prerequisites:** Phase 21 complete with a go recommendation.
**Produces:** a staging environment, the deployment, and the verified launch.
**Do not:** deploy anything or change production DNS until I have approved the target hosting and domain.

## Step 1: Inspect the current site

Document:
- WordPress version
- PHP compatibility
- Theme dependencies
- Database requirements
- Installed plugins and their versions
- Uploads and media
- URL and permalink configuration
- Required environment settings
- Every local-only configuration, including the Phase 18 ad and analytics switches

Work through `LAUNCH_CHECKLIST.md` in full. Every item deferred by an earlier phase is listed there, and this is where they come due.

## Step 2: Hosting recommendation

Recommend a suitable WordPress hosting environment based on the actual requirements, the expected traffic from Phase 01, the Phase 01 budget, backup needs, caching, hosting region for the Phase 01 audience, and available support.

Do not claim a specific host is required without evidence. Give options at different price points and state the trade-offs.

## Step 3: Build a staging environment

This was referenced in a checklist in the original plan but never built. Build it.

Set up a staging site that matches production in PHP version, WordPress version, and plugin set, and is not publicly indexable. Verify the noindex, because an indexed staging site competing with production is a real and common SEO problem.

Deploy to staging first and complete Step 6 verification there before touching production.

## Step 4: Deployment preparation

Create and work through a checklist for:
- Full files and database backup, verified by testing the restore
- Staging environment
- Production database configuration
- Correct site URL
- HTTPS certificate
- Secure credentials, generated fresh for production
- File permissions
- `wp-config.php` hardening and disabled file editing
- Cache configuration
- Email delivery, including the Phase 13 test that could not be run locally
- Search engine visibility enabled deliberately
- Analytics and Search Console verification, using the IDs I supply
- AdSense configuration and `ads.txt`, only if approved
- Error logging with debug display off
- A backup schedule

Never commit secrets or expose database credentials. Never copy local credentials into production.

## Step 5: Migration safety

Use a reliable WordPress migration process. Account for serialized data when replacing local URLs — a naive global search and replace on the database will corrupt serialized values.

Preserve the permalink structure, redirects, canonical URLs, media references, and SEO metadata. Verify the Phase 04 frozen URLs survived the migration exactly.

Have a tested rollback ready before you start, not after something goes wrong.

## Step 6: Launch verification

After deployment, and only when authorized, verify on the real URL:
- HTTPS works and HTTP redirects to it
- No mixed-content errors
- Every key template loads: homepage, archive, single prompt, collection, category, tag, search, 404, and each policy page
- Admin access works securely
- Search, copy, saved prompts, contact form, and newsletter all work
- Email actually sends and arrives
- Images and assets load
- The sitemap and robots directives are correct, and robots does not still disallow everything
- Search engine indexing is enabled intentionally
- Canonical URLs point at the production domain
- Open Graph tags resolve, tested with a real share preview
- Backups run and a restore has been tested
- No debug output, no secrets, and no placeholder IDs are exposed
- Performance measured on the actual production URL, against the Phase 15 budgets

Production performance is the first real measurement this project has had. Record it as the production baseline for Phase 24.

## Final report

Provide the actual deployment status, the URL only if verified, the real test results, the production performance baseline, outstanding risks, rollback instructions, and the handover to Phase 23.

If credentials, hosting access, or approval are missing, prepare everything that can be prepared and say plainly that the deployment has not happened. Do not describe a deployment you did not perform.
