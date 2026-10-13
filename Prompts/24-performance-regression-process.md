# PHASE 24 — PERFORMANCE REGRESSION PROCESS

**Prerequisites:** Phase 15 (the budgets) and Phase 22 (the production baseline) complete.
**Produces:** a repeatable check I can run myself after any change.
**Do not:** introduce heavy dependencies merely to measure a small theme.

## Goal

Create a lightweight, maintainable performance regression process for PromptAtlas, so that the work done in Phase 15 does not quietly decay as content and plugins accumulate.

## Establish repeatable checks

Build checks for:
- Theme CSS and JavaScript sizes
- The number of HTTP requests per template
- Unnecessary or newly added third-party requests
- Homepage, prompt page, and collection page loading
- Image sizing and layout shifts
- Database query counts per template, where measurable
- Mobile layout
- Core Web Vitals, where reliable measurement is available

Use the budgets set in Phase 15 and the production baseline recorded in Phase 22. Do not invent new targets here.

## Budgets

Set realistic budgets based on the actual measured baseline, not on aspiration. Each budget needs a number, the template it applies to, and what to do when it is exceeded.

Avoid brittle checks tied to exact machine-specific timings. Asset sizes, request counts, and query counts are stable and worth automating. Wall-clock timings on a developer machine are not, and should be reported as indicative only.

## Make it genuinely repeatable

The point of this phase is that I can run the check myself without reconstructing how it worked.

Produce:
- A single documented command or short script that runs every automatable check
- A written procedure for the manual checks that cannot be automated
- A results template so successive runs are comparable
- A clear statement of the test environment each check assumes

Separate the local checks from the production checks, and say which number means what. A local figure is for catching regressions; only a production figure describes the real user experience.

## When to run it

Define the triggers:
- After any theme change
- After adding, updating, or removing a plugin
- After a WordPress or PHP version change
- After a significant content volume increase
- On a regular schedule, tied to the Phase 23 maintenance routine

## Documentation

Document the test environment, the baseline, the budgets, the exact commands, and how I repeat the checks after changes. Report measured results separately from estimates, and never present an estimate as a measurement.

Add the process to `TESTING.md` and reference it from the Phase 23 maintenance routine.

## Completion report

Report the checks implemented, the commands to run them with their real output from this run, the budgets with their source baseline, the checks that could not be automated and why, and the current status against every budget.
