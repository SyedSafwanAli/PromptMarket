# PHASE 09 — SEARCH AND DISCOVERY

**Prerequisites:** Phase 05 and Phase 08 complete.
**Produces:** working search that returns real results, plus filters backed by real data.
**Do not:** add a paid search service, a hosted search engine, or a vector database without demonstrating a measured need and getting my approval.

Read the project documentation and inspect the existing prompt content model.

## Goal

Implement a useful search experience that returns real prompt results. Start with a lightweight, maintainable WordPress-native solution.

## Search requirements

Support:
- Prompt title and content matching
- Searching across the prompt description and custom fields where that is genuinely useful
- Categories and tags
- Basic relevance ordering
- Pagination
- Empty queries
- No-result states
- Search result snippets
- Filters that reflect real available data
- Search terms passed safely through WordPress
- Accessible forms and results

Investigate the installed WordPress search behavior first and improve it where appropriate. Avoid querying the entire database on every keystroke.

Decide deliberately whether search returns only prompts or also pages and collections, and make the scope visible to the user.

## Better matching

Create a maintainable path toward:
- Common synonyms
- Alternate spellings
- Aliases, including movie, character, and brand names where relevant to the content
- English and Roman Urdu variations, if the Phase 01 language decision called for it
- Typo-tolerant matching
- Multilingual search, only if Phase 01 put it in scope

Start with curated aliases, normalized text, and efficient indexed fields where suitable. Build the alias list as editable data rather than hardcoded arrays, so a non-developer can extend it.

Do not claim semantic search works unless a real semantic retrieval mechanism exists.

For larger datasets, evaluate a dedicated search engine only after measuring the limitations of the current solution. Explain hosting, maintenance, and cost implications against the Phase 01 budget before proposing external infrastructure.

## Zero-result logging

Capture what visitors search for and find nothing, in a privacy-respecting way, and make it visible to the owner.

This is the single most valuable content-strategy signal the site can produce: it tells the team exactly what to publish next, and it feeds Phase 19 and the ongoing editorial plan.

Store only the normalized query string and a count. Do not store IP addresses, user agents, session identifiers, or anything that could identify a visitor. Cap the table size, deduplicate, and document the retention period. Describe this behavior accurately for the Phase 17 privacy policy.

## Security and abuse prevention

Validate and sanitize every search parameter. Use prepared statements for any custom query. Limit expensive requests. Reject or clamp absurdly long queries. Do not allow arbitrary SQL fragments or unrestricted database scans from user input.

If live suggestions are added, implement a small, accessible debounce with request cancellation, rate limiting, and a cheap server-side endpoint. Do not load a large JavaScript library for simple suggestions, and make sure search works fully without JavaScript.

## Testing

Create and actually run test cases for:
- Exact title matches
- Content matches
- Description and custom field matches
- Category and tag filtering, including combined filters
- Common misspellings
- Known synonyms and aliases
- Empty queries
- Very long queries
- Special characters and regex-like input
- Non-English and Roman Urdu terms
- Queries with no results
- Pagination of a result set
- SQL-injection-shaped input
- Search with JavaScript disabled

Record the actual result for each case. Report which cases genuinely work, which require more content before they can work, and which would need a future advanced search service.

## Completion report

Report the implementation approach, the query cost, the alias mechanism and how to extend it, the zero-result logging design and its privacy posture, the full test matrix with real outcomes, and the honest limitations of the current search.
