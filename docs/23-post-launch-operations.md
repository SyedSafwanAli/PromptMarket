# PHASE 23 — POST-LAUNCH OPERATIONS

**Prerequisites:** Phase 22 complete and the site live.
**Produces:** the maintenance plan, the monitoring setup, the verified backup and recovery procedure, and the incident runbook.
**Do not:** treat launch as the end of the project.

## Why this phase exists

The original plan mentioned a maintenance plan, backups, and rollback instructions only as line items in a deployment report. Nothing ever built them. A live WordPress site without a patching routine, a tested restore, and a way to find out it is down is not finished — it is just unsupervised.

## Step 1: The maintenance routine

Define a concrete, scheduled routine covering:
- WordPress core updates, and the policy for minor versus major versions
- Plugin and theme updates, with a staging-first rule for anything non-trivial
- PHP version upgrades, and how compatibility is checked beforehand
- Security advisories for the specific plugins in use, and where to watch for them
- Database maintenance and cleanup
- Reviewing the Phase 09 zero-result search log for content opportunities
- Reviewing Search Console for coverage errors and manual actions
- Broken link checks
- Reviewing the Phase 13 contact submissions and the DMCA inbox

For each item, state the frequency, who does it, and roughly how long it takes. A routine nobody can realistically follow will not be followed.

## Step 2: Monitoring

Set up, within the Phase 01 budget:
- Uptime monitoring with an alert that reaches me
- SSL certificate expiry monitoring
- PHP error logging and a routine for actually reading it
- Core Web Vitals field data from Search Console
- Search Console coverage and indexation alerts
- Disk and database size awareness

Prefer free or low-cost tooling. State clearly what is monitored, what is not, and how I would find out about each category of failure.

## Step 3: Backups and recovery

Backups that have never been restored are not backups.

Implement and document:
- What is backed up — files, database, uploads — and what is excluded
- The schedule and the retention period
- Where backups are stored, off the production server
- How a restore is performed, step by step
- The recovery point and recovery time this gives in practice

Then actually perform a test restore, preferably onto the Phase 22 staging site, and report the result. If a test restore is not possible, say so and record it as an open risk.

## Step 4: The incident runbook

Write a short runbook for the failures most likely to happen here:
- The site is down
- The site is slow or unresponsive
- A white screen or PHP fatal error after an update
- A compromise is suspected
- A bad deployment needs rolling back
- The database is corrupted
- A DMCA notice arrives
- AdSense is disabled or a policy violation is reported
- A traffic drop appears in Search Console

For each: the first diagnostic step, the containment action, the fix path, and when to roll back instead of fixing forward. Keep it short enough to be usable under pressure.

## Step 5: Post-launch SEO monitoring

Define what to watch in the first 90 days and what each signal means:
- Indexation progress against the Phase 12 policy
- Which pages Search Console actually indexed, versus which were excluded and why
- Early query data, and how it compares to the Phase 04 assumptions
- Core Web Vitals field data versus the Phase 15 lab figures
- When to revisit the Phase 04 content strategy based on real data

Do not set traffic expectations you cannot support. State what would count as the strategy working and what would count as it needing revision.

## Step 6: Handover

Write everything above into `DEPLOYMENT.md` and a new operations document. Make it usable by someone who did not build the site.

## Completion report

Report the maintenance schedule, the monitoring configured and what it does not cover, the backup configuration and the real result of the test restore, the runbook, the SEO monitoring plan, and any operational risk that remains unmitigated.
