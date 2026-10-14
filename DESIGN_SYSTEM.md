# PROMPTATLAS — DESIGN SYSTEM

**Phase 07 deliverable.** Completed 2026-10-14.

Live reference: **`/style-reference/`** — every token and component on one page, in
both themes. Not indexed. Use it to review a change before and after.

---

## 1. Brand voice

**Write like a capable colleague explaining something they actually use.** PromptAtlas exists
because most prompt sites hand you a wall of text and no understanding. The explanation *is* the
product, so the writing carries the value — not the design, and certainly not adjectives.

Plain, direct, specific. Reading level: a competent non-native English speaker should follow it
without effort, because a large share of the audience is exactly that (decision 2). Short
sentences. Concrete nouns. Say what a prompt does and what it does *not* do. Where something is
uncertain or a prompt has a weakness, say so — admitting that a prompt struggles with long inputs
builds more trust than claiming it is perfect, and it is the one thing competitors will not do.

A prompt description should read like the answer to "what is this for, and will it work for me?"
It should never read like a product page.

### Do

| Write | Not |
|---|---|
| "Rewrites your résumé to match one job description." | "Supercharge your job hunt with AI!" |
| "Works best with job adverts under 800 words." | "Works with any job description." |
| "If the output invents experience, reply: …" | "Get perfect results every time." |
| "This prompt is weak at technical roles." | *(silence about the weakness)* |
| "Replace `[JOB_TITLE]` with the exact title from the advert." | "Customise as needed." |

### Avoid

- **Hype**: *supercharge, unlock, revolutionary, game-changing, 10x, effortlessly, insane*.
- **Fake authority**: *experts agree, studies show* — unless you cite the study.
- **Invented numbers**: no "used 10,000 times", no "95% success rate", no ratings. Ever. This is
  the content-integrity rule, and it outranks every stylistic preference here.
- **Padding**: "In today's fast-paced world…". Delete it and start at the point.
- **Second-person imperatives stacked for energy**: "Copy it. Paste it. Win."
- **Em-dash-heavy, listicle-shaped AI prose.** If a paragraph could have been produced by asking a
  model to "write an engaging intro", rewrite it.

### Interface copy

Buttons say what happens: *Copy prompt*, *Browse all prompts*, not *Click here* or *Go*. Empty
states state the fact and offer the next step — "No prompts here yet" is honest; "Oops! Nothing to
see here 😢" is not. Error messages say what went wrong and what to do about it.

---

## 2. Token system

All tokens live in `assets/css/tokens.css`. **The one rule: no colour literal may appear outside
that file.** Dark mode only works because every component reads a semantic token; a hex value in a
component rule is a bug, because it will be wrong in one of the two themes.

### Layers

| Layer | Example | Who uses it |
|---|---|---|
| 1. Brand scale | `--brand-burgundy`, `--neutral-200` | Only layer 2. Never a component. |
| 2. Semantic | `--color-primary`, `--color-text-muted` | **Components use only these** |
| 3. Non-colour | `--space-md`, `--text-lg`, `--radius-md` | Everything |

The dark theme redefines **layer 2 only**. No component rule changes between themes.

### Theme switching mechanism (established Phase 06)

| State | Result |
|---|---|
| No `data-theme` attribute | Follows the OS via `prefers-color-scheme` |
| `data-theme="dark"` | Forced dark |
| `data-theme="light"` | Forced light |

The media query is written `@media (prefers-color-scheme: dark) { :root:not([data-theme="light"]) }`.
The `:not()` guard is what lets an explicit light choice beat an OS set to dark — without it, a
visitor who picks light on a dark-mode machine gets ignored.

A small inline script in `<head>` applies the saved choice **before first paint**. Deferring it
would flash the light theme on every page load for every dark-mode visitor. Every `localStorage`
access is wrapped in `try`/`catch`, because it throws with blocked site data.

### Token groups

| Group | Tokens | Notes |
|---|---|---|
| Colour | `--color-bg`, `--color-surface`, `--color-surface-sunken`, `--color-text`, `--color-text-muted`, `--color-text-faint`, `--color-border`, `--color-border-control`, `--color-primary`, `--color-accent`, `--color-accent-text`, `--color-success`, `--color-highlight`, `--color-danger`, `--color-code-bg`, `--color-focus` | Semantic, not raw brand |
| Typography | `--font-sans`, `--font-serif`, `--font-mono`, `--text-xs` … `--text-3xl`, `--leading-*`, `--weight-*`, `--tracking-*` | Fluid via `clamp()` |
| Spacing | `--space-3xs` … `--space-4xl` | 4px-based |
| Layout | `--width-prose`, `--width-content`, `--width-wide`, `--gutter`, `--grid-gap`, `--section-gap` | `--width-prose` is 42rem ≈ 68 characters |
| Borders | `--border-width`, `--radius-sm/md/lg/pill` | Radii stay low on purpose |
| Focus | `--focus-width`, `--focus-offset`, `--focus-color` | 3px, always visible |
| Motion | `--duration-instant/fast/normal`, `--ease-out`, `--ease-in-out` | 80–220ms |
| Elevation | `--shadow-sm`, `--shadow-md` | Warm-tinted, nearly absent in dark |
| Z-index | `--z-base/sticky/header/dropdown/skiplink` | Named, so no magic numbers |

### Typography scale

System fonts only at launch: no network request, no layout shift from a late webfont, native
rendering everywhere. Sizes use `clamp()` so there is a readable floor at 320px and a sane ceiling
on wide displays, with no breakpoint juggling.

---

## 3. Contrast — measured, not assumed

Computed with the WCAG 2.2 relative-luminance formula. **These are real numbers from a script, not
an assurance.** Targets: 4.5:1 normal text, 3:1 large text and UI boundaries.

### Light theme

| Pairing | Ratio | Needs | |
|---|---|---|---|
| Body text on page background | **16.42** | 4.5 | PASS |
| Body text on surface | **17.12** | 4.5 | PASS |
| Muted text on page background | **6.96** | 4.5 | PASS |
| Faint text (large only) | **4.42** | 3.0 | PASS |
| Burgundy link on page background | **8.38** | 4.5 | PASS |
| Accent-as-text (deep orange) | **7.35** | 4.5 | PASS |
| Olive success text | **8.58** | 4.5 | PASS |
| White on primary button | **8.74** | 4.5 | PASS |
| **Dark text on orange button** | **5.46** | 4.5 | PASS |
| Charcoal on butter highlight | **13.42** | 4.5 | PASS |
| Prompt text on prompt block | **15.06** | 4.5 | PASS |
| Control boundary | **3.64** | 3.0 | PASS |
| Focus ring | **4.33** | 3.0 | PASS |
| Danger text | **7.16** | 4.5 | PASS |

### Dark theme

| Pairing | Ratio | Needs | |
|---|---|---|---|
| Body text on page background | **15.64** | 4.5 | PASS |
| Muted text on page background | **8.38** | 4.5 | PASS |
| Faint text (large only) | **5.42** | 3.0 | PASS |
| Primary (burgundy light) | **8.20** | 4.5 | PASS |
| Link | **10.47** | 4.5 | PASS |
| Success (olive light) | **8.88** | 4.5 | PASS |
| Accent (orange light) | **8.67** | 4.5 | PASS |
| Highlight (butter light) | **14.05** | 4.5 | PASS |
| Near-black on primary button | **8.20** | 4.5 | PASS |
| Prompt text on prompt block | **14.95** | 4.5 | PASS |
| Control boundary | **3.96** | 3.0 | PASS |
| Focus ring | **8.67** | 3.0 | PASS |

### Three decisions measurement forced

These were not design preferences. The numbers changed the design:

1. **The orange button carries dark text, not white.** White on `#ef6905` measures **3.14:1** and
   fails AA. Near-black on the same orange is **5.46:1**. Raw orange as *text* also fails, so text
   use routes through `--color-accent-text` (`#8f3e02`, 7.35:1).
2. **Control borders needed their own token.** The decorative hairline is **1.55:1** — correct for
   a separator, which is exempt, but not for anything whose boundary identifies a control under
   WCAG 2.2 SC 1.4.11. `--color-border-control` exists for inputs, buttons and selects.
3. **The focus ring was darkened.** Raw orange scraped **3.01:1** against the page background —
   technically a pass, too close to rely on. `--brand-orange-focus` is **4.33:1**.

### Why burgundy and olive are replaced in dark mode

Measured on the dark background: burgundy `#8b2626` is **2.09:1** and olive `#486c2f` is
**3.01:1**. Both fail badly and read as muddy. Each is replaced by a light step that keeps the hue
recognisable while actually being readable. **Dark mode is not the light palette inverted.**

> Re-run the checker after any palette change. The script is in the Phase 07 scratchpad; it takes
> hex pairs and prints computed ratios.

---

## 4. Components

In `assets/css/components.css`. Rendered together at `/style-reference/`.

| Component | Class | Notes |
|---|---|---|
| Buttons | `.button--primary/accent/secondary/quiet` | 44px min touch target; disabled state included |
| Form controls | element selectors | Use `--color-border-control`; placeholder opacity forced to 1 for Firefox |
| Search | `.search-form` | Input + icon button; label is screen-reader-only |
| Theme toggle | `.theme-toggle` | **Hidden without JavaScript** — a toggle that cannot toggle is worse than none |
| Cards | `.card`, `.card-grid` | 1 / 2 / 3 columns; `:focus-within` so keyboard users get the same affordance as hover |
| Terms | `.term-pill`, `.category-link` | |
| Category list | `.category-list` | Editorial rule-separated list, not boxes |
| Breadcrumbs | `.breadcrumbs` | Separator via `::before`, so it is not read aloud |
| **Prompt block** | `.prompt-block` | The key surface. Darker than the page in both themes. `white-space: pre-wrap` so long prompts wrap instead of forcing horizontal scroll |
| Placeholder tokens | `.prompt-token` | Highlighted so it is obvious what must be replaced |
| Copy control | `.copy-button` | Styled contract for Phase 10; hidden without JavaScript |
| Variable checklist | `.variable-list` | |
| Empty states | `.empty-state` | Honest, with a next step |
| Notices | `.notice-message--success/error` | |
| Pagination | `.pagination` | 40px targets |
| **Ad slots** | `.ad-slot` | Reserved min-height to protect CLS; **label is mandatory**; extra margin after a prompt block so accidental proximity to the copy button is visually obvious |

### Design intent

Editorial, not SaaS. Hairline rules, whitespace and typographic hierarchy do the work. Explicitly
avoided, per the brief: heavy shadows, gradients, glassmorphism, neon, cards rounded into pills,
oversized heroes, and decorative animation. Radii top out at 8px and most surfaces use a 1px rule.

---

## 5. Motion

Short and purposeful: 80–220ms, on `opacity`, `color`, `border-color` and `transform` only — never
on layout-affecting properties.

`prefers-reduced-motion: reduce` is honoured **globally** in `theme.css` rather than per component,
so nothing can be missed. Durations go to `0.01ms` rather than `0` because some browsers skip
`transitionend` entirely at exactly zero.

No scroll-jacking, no parallax, no autoplay loops, no entrance animations. Nothing depends on hover
alone: every hover affordance has a `:focus-visible` or `:focus-within` equivalent.

---

## 6. Brand assets

In `wp-content/themes/promptatlas/assets/img/`.

| File | Size | Purpose |
|---|---|---|
| `mark.svg` | 0.8 KB | The mark, fixed colours |
| `favicon.svg` | 0.8 KB | Icon variant: warm background, heavier strokes for 16px |
| `favicon-32.png` | 254 B | Legacy favicon |
| `icon-192.png` | 655 B | Android / PWA |
| `icon-512.png` | 1.8 KB | Large icon |
| `apple-touch-icon.png` | 639 B | iOS home screen, 180×180 |
| `og-default.png` | 38 KB | Default share image, 1200×630 |

Icon `<link>` tags are emitted by `promptatlas_icon_links()` — and **skipped entirely if the owner
sets a WordPress Site Icon**, so their choice wins rather than competing with the theme's.

### The mark

Square brackets with a filled block between them. The brackets are the placeholder convention used
throughout the product — every prompt marks what the reader must replace as `[LIKE_THIS]` — and the
block is the thing you fill in. It reads as "a prompt with a slot", which is what the site is for.

Built for 16px: two strokes and one solid shape, nothing that disappears when scaled down. In-page
it is inlined with `currentColor` so it follows the theme; the file versions carry fixed colours
because an icon has nothing to inherit from.

### Share image fallback

`promptatlas_default_share_image()` returns the URL. Phase 12 calls that function rather than
hardcoding a path. For a prompt with no featured image the fallback is deliberate: **the default
share image, and in listings a typographic card with no image frame at all** — not a grey
placeholder box, which looks broken rather than intentional.

### Outstanding — stated plainly

**A designed wordmark is still outstanding, and the current one is a system-font treatment.**

The mark is mine and I would defend it: it is simple, meaningful, scales, and is legible at 16px.
The *wordmark* is not designed — it is "PromptAtlas" set in the system sans at a tight tracking.
That is an honest placeholder, not a finished identity. Custom letterforms, proper optical kerning
and a real lockup need a designer.

I did not ship an SVG wordmark with `<text>` in it, because that renders in whatever font the
viewer happens to have and is therefore not a wordmark at all. Shipping one would have looked like
a finished deliverable while being worse than the CSS treatment.

**Also outstanding:** no `favicon.ico`. Modern browsers use the SVG and PNG icons above. A `.ico`
matters only for very old browsers and some Windows contexts; it needs a tool that writes the ICO
container, which is not available here.

---

## 7. Responsive

Mobile-first. Breakpoints are expressed in `em` so they respond to the user's font size:

| Breakpoint | Change |
|---|---|
| `< 36em` | Single-column cards, stacked header, disclosure menu |
| `≥ 36em` | Two-column cards, two-column category list |
| `≥ 48em` | Horizontal navigation, menu toggle hidden |
| `≥ 62em` | Three-column cards |

The mobile menu is a **checkbox disclosure, not JavaScript**, so navigation works with scripting
unavailable. The checkbox is visually hidden but not `display:none`, which would remove it from the
accessibility tree and make it unreachable by keyboard.

**Verified:** no horizontal overflow at 320px. `.search-form__input` carries `min-width: 0` so the
flex item can shrink below its content width — without it the header overflows on small phones.

---

## 8. Performance baseline

Recorded 2026-10-14 for Phase 15 and Phase 24 to regress against. **Uncompressed, on disk.**

| Asset | Bytes |
|---|---|
| `tokens.css` | 14,156 |
| `theme.css` | 13,010 |
| `components.css` | 18,907 |
| **CSS total** | **46,073** |
| `theme-toggle.js` | 3,406 |
| Images total | 43,951 |

Context: roughly 46 KB of CSS uncompressed, which typically gzips to around 9–10 KB. Much of it is
comments, which do not survive minification — a Phase 15 option, not done now, because readable CSS
is worth more than 4 KB at this stage.

Also done in Phase 06, and counted in this baseline: WordPress's emoji scripts removed, and
`wp-block-library` / `global-styles` dequeued on the frontend. **If a later phase starts using core
blocks that need those styles, the dequeue must be reverted and the cost re-measured.**

---

## 9. Still to verify

Stated so nobody mistakes this for a completed audit:

- **No real browser testing has been done.** Verification was HTTP requests and HTML inspection.
  No browser automation is available in this environment, so rendering, the keyboard path, the
  theme toggle and the mobile menu have **not** been exercised in an actual browser. Phase 16 and
  Phase 20 own that, and `/style-reference/` exists to make it quick.
- Contrast is computed from the token values. It has not been sampled from rendered pixels, which
  would also catch a component accidentally overriding a token.
- No screen reader testing.
- No real-device mobile testing.
