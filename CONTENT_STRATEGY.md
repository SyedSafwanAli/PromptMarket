# PROMPTATLAS — CONTENT STRATEGY

> **Status: stub.** Created in Phase 03 so the documentation set is complete.
> **Phase 04 owns this file** and fills in every section below.
>
> Do not treat the headings here as decided structure — they are the questions Phase 04 has to
> answer, written down so nothing is forgotten.

---

## What is already settled

From [`DECISIONS.md`](DECISIONS.md), these constrain Phase 04 and are not open for re-litigation:

| Settled | Consequence for content |
|---|---|
| Global English audience, large South Asian share (2) | English content, neutral international tone, no region-locked examples |
| English-only, translation-ready, Roman Urdu as *search input* only (3) | One content language. Roman Urdu never becomes a second content set |
| Comments off everywhere (6) | No community discussion to plan for or moderate |
| 40–60 prompts at launch, 120–180 at 3 months (10, proposed) | The taxonomy must look populated at ~50 prompts, not at 500 |
| Content integrity rules (standing) | No fabricated ratings, counts, trending data or testimonials. Honest empty states only |

---

## To be decided in Phase 04

### 1. Audience definition
Who specifically is this for — a marketer, a developer, a student, a small-business owner? What
do they type into a search box? What do they do in the first ten seconds on a prompt page?

### 2. Information architecture
The URL structure, the top-level navigation, and the relationship between prompt categories,
tags and collections. This is the decision that is most expensive to change later, because every
URL it produces has to keep working.

Open questions:
- Prompt URL shape: `/prompts/<slug>/`, or `/<category>/<slug>/`, or `/p/<slug>/`?
- Are categories and collections the same thing or genuinely different?
- How deep may the hierarchy go before it stops helping anyone?

### 3. Taxonomy design
The actual category list, and what tags are for versus what categories are for. A taxonomy that
needs 300 prompts to look reasonable is the wrong taxonomy for a site launching with 50.

### 4. Prompt page anatomy
What a single prompt page contains, in order, and which parts are required versus optional:
the prompt text itself, what it is for, how to use it, what to change, an example of the output,
which models it suits, related prompts.

The written explanation is the part that makes the page original and worth ranking. The prompt
text alone is not a page.

### 5. Prompt content model fields
Whatever Phase 04 decides a prompt page needs becomes the field list that **Phase 05** registers
as post meta. Phase 05 cannot start until this list is settled, so be concrete and complete.

### 6. Editorial standards
The house style, the required length, how a prompt gets reviewed before publishing, and the
originality bar. This feeds the Phase 14 publishing workflow and the Phase 19 launch batch.

### 7. Collections and hub pages
What a collection is for, how prompts get into one, and what makes a hub page worth landing on
rather than a bare list of links. Phase 11 implements whatever is decided here.

### 8. Homepage content plan
What the homepage shows on the day it launches with ~50 prompts and zero usage data. It must work
without "trending", "most copied", or "popular this week" — none of which will have real data,
and none of which may be faked.

---

## Deliberately out of scope

Not part of content strategy for the initial release: creator profiles, user-submitted prompts,
prompt versioning, user ratings or reviews, and any content type that requires moderation.
Phase 25 specifies the creator system; it does not implement it.
