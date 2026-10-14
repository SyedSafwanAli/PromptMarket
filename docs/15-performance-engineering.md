# PHASE 15 — PERFORMANCE ENGINEERING AND SPEED AUDIT

**Prerequisites:** Phases 08 through 14 complete, so there is a real site to measure.
**Produces:** a recorded baseline, justified optimizations, and the measured outcome.
**Do not:** disable security, accessibility, or core WordPress features to improve a score.

Read all project documentation. Measure before making major optimizations.

## Goal

Optimize PromptAtlas for fast rendering, responsiveness, and stable layouts without sacrificing accessibility, maintainability, or useful functionality.

Target Core Web Vitals:
- LCP at or below 2.5 seconds
- INP at or below 200 milliseconds
- CLS at or below 0.1

Aim for approximately two-second loading under defined test conditions, but do not guarantee it across all devices and networks.

## Step 1: Establish the baseline first

Record the baseline before changing anything, along with the exact test conditions: the URL, the device profile, the network throttling, the tool and its version, whether a cache was warm, and whether you were logged in.

Capture the baseline for each distinct template: homepage, prompt archive, single prompt, collection, category archive, and search results. A single homepage number is not a baseline for a content site.

Carry forward the CSS size baseline from Phase 07 and the per-template query counts from Phases 08 and 11.

## Step 2: Audit

Inspect:
- PHP template behavior
- Database queries, and repeated or duplicated queries
- Asset sizes
- CSS and JavaScript payloads
- Render-blocking resources
- Image dimensions and formats
- Font loading
- Third-party requests
- Cache headers
- Layout shifts and their causes
- Unnecessary DOM complexity
- Plugin overhead, measured rather than assumed

Identify which specific thing is the LCP element on each template. Optimizing without knowing that is guesswork.

## Step 3: Optimize

Implement only justified changes:
- Remove unused code and unnecessary dependencies
- Load assets only on the templates that need them
- Defer or delay non-critical JavaScript when safe
- Use efficient CSS
- Add explicit image dimensions
- Use responsive image sources
- Lazy-load below-the-fold images
- Do not lazy-load the primary LCP image if that worsens rendering
- Avoid unnecessary font files and weights
- Minimize layout shifts, including from the Phase 13 newsletter form and the Phase 18 ad slots
- Avoid expensive animations
- Cache only where it is safe and compatible
- Avoid database queries inside repeated loops
- Use WordPress caching APIs or persistent caching only when appropriate
- Preserve admin and personalized content behavior when implementing caching

Pay specific attention to the per-visitor features from Phase 10. Recently Copied and Saved must not break under page caching, and must not force the whole page to become uncacheable.

For each change, state the expected effect and then the measured effect. A change with no measured improvement should be reverted or justified on other grounds.

## Step 4: Animation quality

Use transform and opacity for most transitions. Avoid animating width, height, top, left, or other layout-affecting properties without a compelling reason. Respect reduced-motion preferences.

## Step 5: Set the budgets

Define the performance budgets Phase 24 will enforce, based on this real baseline rather than aspiration:
- Theme CSS size
- Theme JavaScript size
- Number of requests per template
- Number of database queries per template
- Allowed third-party requests, which should be close to zero before Phase 18

## Test conditions

Separate local-development results from production-like results, and label every number accordingly. A local LCP figure does not predict production.

If Lighthouse, browser automation, or PageSpeed Insights are unavailable, state the limitation plainly, provide the next verification step, and add production measurement to `LAUNCH_CHECKLIST.md` for Phase 22.

## Completion report

Report:
- The baseline per template, with the exact test conditions
- Each change made and why
- The measured outcome per template
- Regressions checked, including accessibility and functionality
- Remaining bottlenecks and what they would cost to fix
- The budgets set for Phase 24
- Whether the target is met under the tested conditions, stated honestly

Report estimates separately from measurements, and never present an estimate as a measurement.
