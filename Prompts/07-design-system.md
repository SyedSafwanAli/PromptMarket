# PHASE 07 — DESIGN SYSTEM AND BRAND ASSETS

**Prerequisites:** Phase 06 complete.
**Produces:** the documented token system, the component styles, and the actual brand asset files.
**Do not:** build the homepage or page layouts. Phase 08 does that, using what this phase produces.

Read the existing theme and documentation. Build the reusable design system before building the full homepage.

## Brand direction

PromptAtlas is a high-quality editorial discovery platform for AI prompts. It should feel thoughtfully art-directed, welcoming, useful, and fast — not like a generic AI-generated SaaS landing page.

Use burgundy `#8B2626` for key brand moments, orange `#EF6905` for deliberate accents and calls to action, olive `#486C2F` for selected supporting states, and butter yellow `#F1E5A1` as a controlled highlight, over warm off-white `#FCFAF6`. Do not use all four colors at equal intensity everywhere.

## Brand voice

Before building components, write a short brand voice note into the design system documentation: the tone, the reading level, how a prompt description should sound, and what to avoid. Phase 08 writes real interface copy and Phase 19 writes real content, and both need this. Two paragraphs and a handful of do/do-not examples is enough.

## Token system

Create a documented token system using CSS custom properties for:
- Colors, including semantic tokens rather than only raw brand values
- Typography
- Spacing
- Content widths
- Layout gaps
- Borders
- Focus rings
- Motion durations
- Easing
- Z-index layers
- Responsive breakpoints

If the Phase 01 decision put dark mode in scope, define the complete second token set now, against the mechanism Phase 06 established. Verify contrast in both modes.

Use responsive typography with sensible maximum sizes. Choose a readable system font stack initially. If custom fonts are introduced later, use properly licensed local font files, appropriate subsets and weights, and sensible `font-display` settings.

## Components

Build consistent styling for:
- Primary and secondary buttons
- Text links
- Form controls
- Search field
- Navigation
- Breadcrumbs
- Tags
- Category links
- Prompt content presentation
- Copy controls
- Collection and hub page elements
- Ad slot containers, so Phase 18 has a styled, reserved, clearly labeled placement to use
- Empty states
- Error and success messages
- Pagination
- Focus and disabled states

Do not make every component a rounded card. Prefer open editorial layouts, whitespace, typography, subtle separators, and visual grouping.

## Brand assets

Create the actual asset files. Phase 08 references a logo, and that logo has to exist.

Produce:
- A text wordmark treatment, and an SVG logo if you can produce one that is genuinely good rather than generic
- A favicon in the formats modern browsers need
- An apple-touch-icon
- A default Open Graph share image, sized correctly, for Phase 12 to reference
- A documented fallback for prompts with no featured image

Keep every asset small and optimized, with explicit dimensions where they are used in markup. Place them in the theme and document their paths.

If you cannot produce a mark you would genuinely defend, build the wordmark treatment properly and tell me plainly that a designed logo is still outstanding. Do not ship a weak clip-art mark and call it done.

## Motion system

Animations should communicate state or improve orientation.

Use lightweight CSS transforms and opacity transitions when appropriate. Keep movement restrained and short. Avoid animating layout-affecting properties.

Respect `prefers-reduced-motion: reduce`. Avoid scroll-jacking, autoplay animation loops, excessive parallax, and mandatory entrance animations.

Ensure interactive controls respond immediately and remain usable on touch devices.

## Responsive rules

Design mobile-first and verify:
- Small phones around 320–375 CSS pixels
- Typical phones
- Tablets
- Desktop and wide desktop

Avoid horizontal overflow, tiny text, clipped controls, and fixed-width sections that break on mobile.

## Verification

Build a temporary internal style reference page or template that renders every component and token in one place, so the system can be reviewed and regression-checked. Document whether it stays in the theme or is removed before launch, and if it stays, make sure it is not indexable.

Check contrast ratios for every text and interactive color pairing against WCAG 2.2 AA and report the actual computed ratios, not an assurance.

Run the Phase 03 syntax and standards checks. Record the CSS file size as the Phase 15 and Phase 24 baseline.

## Completion criteria

Document the tokens and components. Demonstrate where the tokens are used. Report the contrast results, the asset files created, the asset work still outstanding, and the CSS size baseline.
