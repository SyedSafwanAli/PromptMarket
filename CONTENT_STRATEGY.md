# PROMPTATLAS — CONTENT STRATEGY

**Phase 04 deliverable.** Completed 2026-10-14.

This document is the input to Phases 05, 08, 11, 12 and 19. It is specific on purpose: **Phase 05
must be able to register the post types, taxonomies and meta fields without making a single
naming decision of its own.**

The taxonomy and URL decisions here are **frozen** — see section 3.6.

---

## 1. The search landscape

### 1.1 What was actually researched, and how much to trust it

Two web searches were run on 2026-10-14. **The honest summary: almost every available source is a
vendor with a commercial interest in the answer, and no independently validated prompt-search
volume data was found.** Nothing in this section should be treated as measured data.

What the sources support:

- Real prompts people type are often **short and keyword-like**, not polished templates, and
  frequently carry personal context (budget, location, role). Reported by Search Engine Land,
  summarising a survey by Stella Rising — a vendor, and the article discloses that the two
  surveys used different audiences.
- **Platform habits differ.** ChatGPT users tend to write conversationally in first person and
  front-load context; Perplexity users iterate. Reported by Topify, also a vendor.
- **AI tools fan one prompt out into several sub-queries**, which argues for building topic
  clusters rather than single-keyword pages. Topify's figure of ~2.17 fan-out searches per prompt
  is a vendor estimate with no independent validation.
- Established prompt directories organise **by industry, function, use case, or target tool** —
  PromptDen and Snack Prompt by developer topic; God of Prompt by business function; PromptSpace
  by image tool and style.
- Prompt-volume tools (Writesonic Prompt Explorer, Goodie Prompt Research) claim to measure how
  often specific prompts are asked. No independent validation was found.

**Sources:**
[Search Engine Land — how real people prompt AI](https://searchengineland.com/real-people-actually-prompt-ai-geo-479731) ·
[Topify — prompt search and AI query behaviour](https://topify.ai/blog/prompt-search-ai-query-behavior) ·
[Pinggy — best prompt libraries for AI-assisted development](https://pinggy.io/blog/best_prompt_libraries_for_ai_assisted_software_development/) ·
[God of Prompt — AI prompt libraries by use case](https://www.godofprompt.ai/blog/ai-prompt-libraries-every-use-case) ·
[PromptSpace](https://www.promptspace.in/prompts) ·
[arXiv — Generative Engine Optimization](https://arxiv.org/pdf/2509.08919) ·
[Connective — AI queries in Search Console](https://connectivewebdesign.com/blog/ai-queries-search-console)

### 1.2 Query patterns and their intent

**This is general-knowledge pattern analysis, not verified volume data.** No search volumes are
given anywhere in this document, because no reliable source for them was found. Once Search
Console has 4–6 weeks of real impression data (Phase 18), this section should be rewritten from
that data and these assumptions checked.

| Pattern | Example shape | Intent | Served by |
|---|---|---|---|
| **A. Role / task list** | "chatgpt prompts for teachers" | Browse, collect, bookmark | **Collection** page |
| **B. Specific outcome** | "prompt to rewrite my resume" | Get one thing done now | **Single prompt** page |
| **C. Tool-led** | "midjourney prompts", "sora prompts" | Browse by the tool they already use | **Tool archive** or collection |
| **D. Superlative browse** | "best chatgpt prompts" | Survey the field | **Collection** / homepage |
| **E. How-to** | "how to prompt chatgpt to summarise a pdf" | Learn the technique | **Single prompt** page, explanation-led |
| **F. Category browse** | "coding prompts", "marketing prompts" | Orient within a domain | **Category archive** |

**The structural conclusion that drives everything below:** patterns A, C and D — all list intent —
are the highest-value and most linkable pages, and they are *not* served by a single prompt page.
This is why collections are a first-class post type in section 3, not an afterthought. Pattern B
pages are the long tail: individually small, collectively the bulk of the traffic, and the reason
each one needs genuinely original explanatory copy.

### 1.3 Where the gaps are

Judgement, not measured data:

- **Most prompt directories are a wall of prompt text with no explanation.** They answer "what do
  I paste" but not "why this works", "what to change", or "what to do when the output is wrong".
  That explanation is the only defensible, rankable, non-duplicable asset a prompt site has — and
  it is also what keeps the site on the right side of the thin-content problem.
- **Paid walls on obvious content.** Several large libraries charge for prompts of ordinary
  quality. Free plus genuinely useful explanation is a real position.
- **Tool-specific pages are stale.** Model names and capabilities move fast, and most directories
  do not revisit. A visible, honest last-updated date is a real differentiator, which is why
  section 4.6 makes it part of the model.

### 1.4 How the chosen audience changes this

From `DECISIONS.md` decision 2 — global English, large South Asian share:

- **Write in plain international English.** No idiom, no region-locked examples, no assumed
  currency. A prompt example that says "$" without context fails half the audience.
- **Mobile-first is not optional.** South Asian traffic is overwhelmingly mobile on variable
  networks. This reinforces the Phase 15 performance budget and the "minimal JavaScript" rule.
- **Career, study, freelancing and small-business topics are disproportionately relevant** to this
  audience mix, and are reflected in the category priorities in section 7.2.
- **Roman Urdu appears as search input, never as content** (decision 3). Phase 09 maps Roman Urdu
  query terms onto English content as synonyms. No content is translated.

---

## 2. Audience definition

**Primary:** a working adult who already uses an AI assistant and wants a better result for a
specific task, now. Marketers, freelancers, students, job seekers, small-business owners, and
developers.

They are **not** prompt engineers. They are not looking to study prompting theory.

What they do in the first ten seconds on a prompt page:

1. Check the prompt actually matches their task.
2. Copy it.
3. Leave — or, if the page earned it, read what to change and look at one more prompt.

**Design consequences, which bind Phases 07 and 08:**

- The prompt text and the copy button must be visible without scrolling on a phone.
- The explanation sits **below** the prompt, never above it. Nobody reads an essay to reach a
  copy button.
- Related prompts matter more than any other navigation, because step 3 is where a second
  pageview is won or lost.

**Secondary audience:** a browser with no specific task, arriving on a collection or category
page from a list-intent query. For them, the collection page *is* the product.

---

## 3. Taxonomy and URL architecture — FROZEN

### 3.1 Post types

| Post type | Slug | Purpose | Public |
|---|---|---|---|
| `prompt` | `prompts` | One prompt, its explanation, and its metadata | Yes |
| `collection` | `collections` | A curated, editorially ordered list of prompts with original intro copy | Yes |

Both are registered by **`promptatlas-core`**, not the theme (`CLAUDE.md` section 3).

`prompt` supports: title, editor, excerpt, revisions, custom fields, thumbnail, author.
`collection` supports: the same.
**Neither supports `comments`** (decision 6).

### 3.2 Taxonomies

| Taxonomy | Slug | Attached to | Shape | Hierarchical | Archive indexable |
|---|---|---|---|---|---|
| `prompt_category` | `category` | `prompt` | Required, **exactly one** | **Flat** | **Yes** |
| `prompt_tool` | `tool` | `prompt`, `collection` | Optional, multiple | Flat | **Yes** |
| `prompt_tag` | `tag` | `prompt` | Optional, multiple | Flat | **No — `noindex, follow`** |

**Why `prompt_category` is flat, not hierarchical.** The site launches with 40–60 prompts
(decision 10). A two-level hierarchy over 50 items produces leaf categories with two prompts
each, and a category page with two prompts is a thin page — the exact thing that damages both
ranking and an AdSense review. Depth can be added later by converting the taxonomy to
hierarchical, which does not break existing term URLs. Starting hierarchical and flattening
*does* break them.

### 3.3 The category list — complete and final

**Eight categories.** Eight into 40–60 prompts gives every category 5–8 prompts at launch, so no
category page ships thin. New categories require a recorded decision and at least 5 prompts ready
to publish into it.

| # | Category | Slug | Belongs here | Does **not** belong here |
|---|---|---|---|---|
| 1 | **Writing & Editing** | `writing-editing` | Drafting, rewriting, summarising, tone changes, proofreading, long-form structure | Marketing copy written to sell (→ 2); code comments and docs (→ 4) |
| 2 | **Marketing & Content** | `marketing-content` | Ad copy, social posts, email campaigns, SEO briefs, landing-page copy, content calendars | General business strategy (→ 3); image generation for ads (→ 7) |
| 3 | **Business & Strategy** | `business-strategy` | Plans, proposals, pricing, competitor and SWOT analysis, meeting and decision documents, client communication | Copy written to sell (→ 2); spreadsheet formulas and data work (→ 8) |
| 4 | **Coding & Development** | `coding-development` | Code generation, debugging, refactoring, code review, tests, documentation, SQL, regex, shell | Non-technical data analysis (→ 8) |
| 5 | **Career & Job Search** | `career-job-search` | CVs and résumés, cover letters, LinkedIn profiles, interview preparation, salary negotiation, outreach | Running a business (→ 3); studying a subject (→ 6) |
| 6 | **Education & Study** | `education-study` | Explaining concepts, study plans, practice questions, lesson plans, feedback on student work, research summaries | Career applications (→ 5) |
| 7 | **Image & Video** | `image-video` | Image generation, editing instructions, video and shot prompts, style and lighting direction, thumbnails | Written marketing copy (→ 2) |
| 8 | **Productivity & Personal** | `productivity-personal` | Planning, prioritising, email and inbox handling, note-taking systems, habits, personal decisions | Team and business process (→ 3) |

**Overlap rule for editors:** exactly one category per prompt, chosen by **the user's job, not the
tool used**. A prompt that generates a product photo for an ad campaign is Image & Video (7),
because the person's job is making the image. Use tags for the cross-cutting dimension.

### 3.4 The tool taxonomy

Justified, because pattern C ("midjourney prompts") is a real and distinct query shape, and a
prompt written for an image model genuinely does not transfer to a text model.

**Initial terms:** `ChatGPT`, `Claude`, `Gemini`, `Midjourney`, `Sora`, `Stable Diffusion`,
`GitHub Copilot`, `Perplexity`.

Rules:

- A term is created **only when at least 3 published prompts will carry it.** This is the
  anti-thin-page rule and it is not negotiable.
- Terms name a **tool or product family, never a version number.** `ChatGPT`, not `GPT-5.2`.
  Version-specific terms are guaranteed to go stale and would multiply endlessly.
- A prompt may carry several tools when it genuinely works across them. Do not pad.
- Tool archives are indexable and get a real intro paragraph (Phase 11), not a bare list.

### 3.5 The tag strategy, and how sprawl is prevented

Tags are for the **specific artefact or scenario** that cuts across categories: `cold-email`,
`resume`, `landing-page`, `unit-tests`, `lesson-plan`, `thumbnail`.

Sprawl controls — tag sprawl is the single most common way a content site's taxonomy rots:

1. **Only an Editor or Administrator may create a tag** (enforced in Phase 14). Authors may
   assign existing tags only.
2. **A tag must apply to at least 3 prompts** to stay. Fewer at the quarterly review and it is
   merged or deleted.
3. **No tag may duplicate a category or a tool.** No `writing` tag, no `chatgpt` tag.
4. **Maximum 5 tags per prompt.** More than five means the prompt is not actually about anything.
5. Singular, lowercase, hyphenated. `cold-email`, never `Cold Emails`.
6. Tag archives are **`noindex, follow`** — they exist to pass internal link equity and to help a
   human browse, not to rank. This is why rule 2 is tolerable: a thin tag archive costs nothing
   if it is not indexed.

### 3.6 URL architecture — FROZEN

| Content | URL | Indexable |
|---|---|---|
| Single prompt | `/prompts/<slug>/` | Yes |
| Prompt archive | `/prompts/` | Yes |
| Prompt archive, paginated | `/prompts/page/2/` | Yes, self-canonical |
| Category archive | `/prompts/category/<slug>/` | Yes |
| Tool archive | `/prompts/tool/<slug>/` | Yes |
| Tag archive | `/prompts/tag/<slug>/` | **No** — `noindex, follow` |
| Single collection | `/collections/<slug>/` | Yes |
| Collection archive | `/collections/` | Yes |
| Search results | `/?s=<query>` | **No** — `noindex, follow` |
| Standard pages | `/<slug>/` | Yes |
| Home | `/` | Yes |

**Why prefixed (`/prompts/<slug>/`) rather than flat or category-nested:**

- **Flat (`/<slug>/`)** collides with pages. A prompt titled "About Writing Emails" and an About
  page compete for the same namespace, and WordPress resolves it unpredictably.
- **Category-nested (`/<category>/<slug>/`)** means **recategorising a prompt changes its URL**.
  Categories get refined — that is normal editorial work — and it must not cost a redirect and
  lost ranking every time.
- **Prefixed** gives a clean archive at `/prompts/`, an unambiguous namespace, and a URL that
  survives recategorisation. The cost is one extra path segment, which is worth it.

**Reserved slugs.** No page, prompt or collection may use these, or it will shadow a route.
Phase 05 enforces this on save:

```
prompts  collections  category  tag  tool  page  search  feed  wp-admin
wp-content  wp-includes  about  contact  privacy  privacy-policy  terms
dmca  llms  robots  sitemap
```

**Disabled archive types** — they produce duplicate thin pages on a single-author site and are
switched off in Phase 12: author archives, date archives, and the `post` type's own archive if
`post` ends up unused.

> ### These URLs are now FROZEN.
> Any later change requires a written redirect plan: 301s from every old URL, an updated sitemap,
> and a Search Console re-submission. Do not change a slug in this table casually — a URL change
> after launch costs ranking and breaks every inbound link and bookmark.

---

## 4. Content model requirements — the exact spec for Phase 05

Meta keys are prefixed `_promptatlas_` (protected: underscore-prefixed meta is hidden from the
generic custom-fields UI, which is correct — these get a purpose-built editor panel in Phase 05).

### 4.1 Fields on `prompt`

| Field | Storage | Required | Notes |
|---|---|---|---|
| Title | `post_title` | **Yes** | Convention in 4.2 |
| Explanation | `post_content` | **Yes** | The original substance. Min ~120 words. See 4.4 |
| Summary | `post_excerpt` | **Yes** | 120–160 chars. Doubles as the meta description (Phase 12) |
| **Prompt text** | `_promptatlas_prompt_text` | **Yes** | The thing people copy. Plain text, stored raw, never rendered as HTML |
| Variables | `_promptatlas_variables` | No | Repeatable `{name, description}`. See 4.5 |
| Example output | `_promptatlas_example_output` | No | Real output from a real run, or omitted entirely |
| Example tool | `_promptatlas_example_tool` | No | Which tool produced the example output. Required **if** example output is present |
| Attribution | `_promptatlas_attribution` | No | Source and licence if the prompt is adapted rather than original. See 4.7 |
| Related override | `_promptatlas_related` | No | Up to 3 manually chosen prompt IDs. See 4.8 |
| Category | `prompt_category` | **Yes, exactly 1** | |
| Tools | `prompt_tool` | No, multiple | |
| Tags | `prompt_tag` | No, max 5 | |

**Deliberately not in the model:** star ratings, review counts, copy counts, view counts, "trending"
flags, difficulty levels, and estimated time saved. Every one of them would either be fabricated —
which the content-integrity rule forbids outright — or would be a field with no real data behind
it at launch. Phase 10 may track *real* copy counts; if it does, section 4.9 governs when they may
be shown.

### 4.2 Title convention

**State the outcome in plain language. 40–65 characters.**

- Good: `Rewrite a résumé for a specific job description`
- Good: `Turn a long report into a one-page executive summary`
- Bad: `Resume Prompt` — tells the reader nothing
- Bad: `BEST ChatGPT Prompt For Resume Writing 2026 (FREE)` — keyword stuffing, dates badly

Lead with the verb. Name the tool in the title **only** when the prompt is genuinely
tool-specific, e.g. `Cinematic product shot in Midjourney`. Do not append "ChatGPT prompt" to
every title; the template, the URL and the tool taxonomy already carry that, and repeating it
across 60 titles reads as spam.

### 4.3 Summary (`post_excerpt`)

One or two sentences, **120–160 characters**, stating what the prompt produces and who it is for.
Written as a standalone sentence because it is used in three places: the card in a listing, the
meta description, and the social preview. It must not begin "This prompt…" — that wastes the
first four words of the description.

### 4.4 Explanation (`post_content`) — the part that matters

**Minimum ~120 words of genuinely original writing.** This is what makes the page worth indexing
and what distinguishes PromptAtlas from a wall of copyable text. A prompt with no explanation is
not a publishable page.

Required structure, in this order:

1. **What it does and when to use it** — one short paragraph.
2. **What to change** — the variables in plain language, and what good values look like.
3. **Why it works** — the actual technique: the role assignment, the constraints, the output
   format, the worked example. This is the teaching, and it is the hardest part to duplicate.
4. **If the output is wrong** — one or two concrete corrective follow-ups.

Written in original language. Not reworded from another site. Never AI-generated boilerplate
padding to hit a word count — 120 useful words beats 400 empty ones, and a reviewer can tell.

### 4.5 Placeholder variables — part of the model, deliberately

**Yes, variables are a first-class field.** Reasoning: the single biggest failure mode on a prompt
site is a visitor copying a prompt with `[INDUSTRY]` still in it, getting a generic answer, and
concluding the site is useless.

- Convention in the prompt text: `[SQUARE_BRACKETS_UPPERCASE]`.
- Each variable gets a name and a plain-language description of what to put there.
- Phase 08 renders them as a visible checklist beside the prompt, so the substitutions are obvious
  before copying.
- Phase 05 should validate on save that every `[TOKEN]` in the prompt text has a matching variable
  entry, and warn the editor when one does not. **Warn, not block** — an editor must be able to
  save a draft mid-write.

### 4.6 Last-updated date — displayed

**Displayed, and it is a real feature, not a freshness trick.**

- Show the published date always; show "Updated <date>" **only when `post_modified` differs from
  `post_date` by more than 7 days**, so that a typo fix does not relabel the page.
- Why it matters: AI tools change fast, and a prompt written for a model's older behaviour can
  simply stop working. A visible, honest date tells the reader whether to trust the page, and
  gives the editorial process a reason to revisit (Phase 23).
- **Never touch the modified date to fake freshness.** That is the exact dishonesty the
  content-integrity rule exists to prevent.

### 4.7 Attribution

If a prompt is adapted from somewhere else rather than written from scratch, the source and its
licence **must** be recorded in `_promptatlas_attribution` and displayed on the page. Decision 5
is unresolved on the wider licence question, but this field is required regardless: scraped or
near-duplicate content is both a legal exposure and the most-cited cause of AdSense rejection.

An original prompt leaves the field empty. There is no middle option, and "inspired by" is not an
exemption.

### 4.8 Related prompts

**Automatic, with a manual override.**

1. If `_promptatlas_related` holds IDs, show those first, in the given order.
2. Fill the remainder automatically: same `prompt_category`, ranked by **count of shared
   `prompt_tag` terms** descending, then by most recently modified.
3. Show up to 4. Exclude the current prompt. Never pad with unrelated prompts to reach 4 —
   showing 2 genuinely related prompts is better than 4 with filler.

Ranking by shared tags is the reason the tag discipline in 3.5 matters: tags are the relatedness
signal, not decoration.

### 4.9 Fields on `collection`

| Field | Storage | Required | Notes |
|---|---|---|---|
| Title | `post_title` | **Yes** | e.g. `Prompts for teachers` |
| Intro | `post_content` | **Yes** | Min ~150 words of original copy. A bare list does not rank and does not deserve to |
| Summary | `post_excerpt` | **Yes** | 120–160 chars, as 4.3 |
| **Ordered prompts** | `_promptatlas_collection_prompts` | **Yes** | Ordered array of prompt IDs. Order is editorial and meaningful |
| Reverse index | `_promptatlas_in_collection` | — | Written **on the prompt**, one meta row per collection, kept in sync on save. Makes "which collections contain this prompt" a cheap, indexable query |
| Tools | `prompt_tool` | No | Shared taxonomy with prompts |

---

## 5. Collections and hub pages

### 5.1 What a collection is, and how it differs from a category archive

| | Category archive | Collection |
|---|---|---|
| Membership | Automatic — every prompt in the category | **Curated** — editorially chosen |
| Order | Reverse chronological | **Editorial**, and meaningful |
| Copy | A short intro paragraph | **150+ words of original framing** |
| Spans categories | No | **Yes** |
| Count | Fixed at 8 | Grows as topics justify one |
| Serves | Pattern F (category browse) | **Patterns A, C, D — the list intent** |

A collection answers "chatgpt prompts for teachers": prompts drawn from Education, Writing and
Productivity, ordered by what a teacher needs first, with an intro explaining how to use the set.
A category archive cannot do that, because membership and order are mechanical.

### 5.2 Why a post type and not a taxonomy

**Decision: `collection` is a post type.**

- These are the highest-traffic, most-linkable pages on the site, so they need the full editor,
  a real excerpt, a featured image and proper SEO fields — not a taxonomy term-description
  textarea.
- Editorial ordering is the point. Taxonomy archives cannot be hand-ordered without inventing a
  per-term order meta, which is strictly more complex than an ordered ID list.
- **Rejected: taxonomy.** Gives free bidirectional queries and archives, but loses editorial
  ordering and gives a poor authoring experience for a page whose intro copy is half its value.
- **Rejected: ordinary Pages.** Works, but loses the consistent template, the archive at
  `/collections/`, and any structured prompt relationship, making every collection hand-built.

The cost of the post-type choice is that the reverse lookup is not free, which is exactly why
`_promptatlas_in_collection` is mirrored onto the prompt (4.9). Phase 05 must keep both sides in
sync on save, and that sync is the one piece of this model that genuinely needs a test.

### 5.3 The first collections worth building

Each must contain **at least 8 prompts** before publishing. Ordered by priority:

| # | Collection | Slug | Draws from | Serves |
|---|---|---|---|---|
| 1 | Prompts for job seekers | `job-seekers` | 5, 1 | Pattern A, strong for the audience mix |
| 2 | Prompts for teachers | `teachers` | 6, 1, 8 | Pattern A, proven list-intent topic |
| 3 | Prompts for developers | `developers` | 4 | Pattern A |
| 4 | Prompts for freelancers | `freelancers` | 3, 2, 5 | Pattern A, high relevance to the audience |
| 5 | Midjourney prompts that work | `midjourney` | 7 | Pattern C, tool-led |
| 6 | Prompts for small business owners | `small-business` | 3, 2, 8 | Pattern A |
| 7 | Prompts for students | `students` | 6, 1 | Pattern A |
| 8 | Prompts for writers and editors | `writers` | 1 | Pattern A |

Launch with collections 1–4 only. Eight collections over ~50 prompts would mean heavy reuse and
near-duplicate pages; four is honest coverage. 5–8 follow as the prompt library grows.

### 5.4 Linking

- A collection links to **every** prompt in it, in editorial order.
- A prompt links back to **every** collection containing it, via `_promptatlas_in_collection`.
- A prompt links to its one category, its tools, and up to 4 related prompts (4.8).
- The homepage links to all 8 categories and the live collections.
- **Nothing is orphaned.** Every published prompt must be reachable from the homepage in at most
  three clicks. Phase 20 verifies this.

---

## 6. Editorial plan

### 6.1 Publishing rate

**2–3 prompts per working day**, derived directly from decision 10: 40–60 at launch, 120–180 at
three months, 250–350 at six months. That is roughly 10–15 a week, which one person can sustain
while actually testing each prompt.

This rate is a **ceiling set by quality, not a quota**. Decision 10 is explicit that 300
near-duplicate prompts is a worse outcome than 60 useful ones. Missing the rate is acceptable;
padding to hit it is not.

### 6.2 Topic clusters in priority order

Priority reflects the audience mix in decision 2 and the gap analysis in 1.3 — not guessed search
volume.

1. **Career & Job Search** — highest relevance to the audience mix, clear outcomes, evergreen.
2. **Writing & Editing** — broadest applicability, feeds every other cluster.
3. **Marketing & Content** — strong commercial intent, supports later AdSense RPM.
4. **Coding & Development** — well-defined audience, highly shareable, differentiated by the
   explanation requirement.
5. **Education & Study** — large audience, strong list intent, supports the teachers collection.
6. **Business & Strategy** — good commercial intent, slower to write well.
7. **Productivity & Personal** — easy to write, easy to make thin. Deliberately later.
8. **Image & Video** — highest maintenance burden, because tools change fastest.

### 6.3 The Phase 19 launch batch

**48 prompts across 8 categories, 6 each**, plus collections 1–4. The spread is deliberate: no
category page ships thin, and all four launch collections have enough to draw from.

| Category | Count | First prompts, by topic |
|---|---|---|
| Career & Job Search | 6 | Résumé tailored to a job description · cover letter · LinkedIn About section · interview answer practice · salary negotiation · cold outreach to a hiring manager |
| Writing & Editing | 6 | Report → executive summary · tone rewrite · proofread with explanations · long-form outline · simplify jargon · expand notes into prose |
| Marketing & Content | 6 | Ad copy variants for one offer · social post from a blog article · email sequence · SEO content brief · landing-page headline set · content calendar |
| Coding & Development | 6 | Explain unfamiliar code · debug from an error message · refactor for readability · write unit tests · review a diff · SQL from a plain-language question |
| Education & Study | 6 | Explain a concept at three levels · study plan from a syllabus · practice questions with answers · lesson plan · feedback on student writing · summarise a research paper |
| Business & Strategy | 6 | Client proposal · competitor analysis · pricing options · meeting agenda and minutes · decision memo · difficult client email |
| Productivity & Personal | 6 | Weekly plan from a task list · inbox triage · meeting notes → actions · decision framework · habit plan · email → calendar invite |
| Image & Video | 6 | Product shot · character consistency across images · thumbnail · style and lighting direction · short video shot list · image edit instructions |

### 6.4 How originality is verified before publishing

Every prompt, no exceptions. This is the AdSense-approval and legal-risk control, and it is the
one step in this document that must never be skipped under time pressure:

1. **Written from scratch** against the task, not collected from another site.
2. **Actually run** in at least one real tool. A prompt that was never tested must not be
   published — and the tool used is recorded in `_promptatlas_example_tool` when example output
   is shown.
3. **Explanation written from the test**, describing what the prompt really did, including where
   it was weak.
4. **Duplication check:** search an exact-phrase fragment of the most distinctive line of the
   prompt and of the explanation. A match means rewrite, or attribute properly per 4.7.
5. **Editor review** against this document before the status moves to publish (Phase 14 builds
   the workflow).

### 6.5 Feeding zero-result searches back in

Once Phase 18 analytics exist:

- Log internal searches that return **zero results** (Phase 09 implements the logging; it must
  store the query only, with no visitor identifier).
- Review monthly. A repeated zero-result query is a content gap stated in the visitor's own words
  — the single most reliable input to the editorial calendar there is.
- Cross-check against Search Console queries where PromptAtlas gets impressions but a very low
  click rate: that usually means the page exists but does not match the intent, which is a
  rewrite, not a new page.
- **Do not mass-produce pages for keyword variations.** One good page per intent. If two queries
  share an intent, they share a page.

---

## 7. What is out of scope

Not part of content strategy for the initial release: creator profiles, user-submitted prompts,
prompt version history, user ratings or reviews, and any content type requiring moderation.
Phase 25 specifies the creator system; it does not implement it.
