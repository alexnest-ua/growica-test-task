[English](01-requirements.en.md) · [Українська](01-requirements.uk.md)

# Requirements

A restatement of the assignment in my own words, with explicit acceptance
criteria. (The original brief is kept locally and intentionally not republished
in this public repository.)

## Context

A group of sites was demoted by Google because they share a common-theme
**footprint** — they read as one network (a PBN). The task is to make sites
visually **and** technically dissimilar so they no longer cluster as the same
operator. This is "theme uniqueization / de-footprinting".

## Part 1 — Solution document (~1h)

A free-form, 300–500 word write-up covering:

- what "theme uniqueization" means from Google's perspective;
- the layers of uniqueization and which matters most;
- how to uniqueize 5 sites in a week — first vs last;
- the risks and how to mitigate them.

Structured thinking is graded, not a single correct answer. → [02-solution](02-solution.en.md)

## Part 2 — Implementation (~3–4h)

Pick **one** free parent theme from wordpress.org (GeneratePress / Astra /
Kadence — my choice, justified) and build **two child themes** that feel like
**different products**. Each child theme must differ in:

| # | Axis | Requirement |
|---|------|-------------|
| 1 | **Colour scheme** | Completely different — not a different shade. |
| 2 | **Typography** | Different heading + body font pairs. |
| 3 | **Header** | Different structure (e.g. centred logo vs logo-left + menu-right). |
| 4 | **Footer** | Different copyright **and** different column structure. |
| 5 | **`style.css`** | Unique Theme Name / Author / Version, with **no shared comments or signatures** between the two (this is the footprint point). |
| 6 | **ACF field group** | At least one custom ACF group, with a **different structure** in each theme and different from the parent. Registered in code / committed as ACF Local JSON so it is reproducible from the repo — not click-only in the DB. |

## Deployment

Deploy **both** sites to TasteWP or InstaWP (free hosted WordPress) and put the
live links in the README.

## Cross-cutting constraints

- **Idiomatic WordPress:** correct child-theme structure (`style.css` header +
  `functions.php` with proper parent→child enqueue), template overrides only
  where needed, hooks/filters, ACF field groups, `wp_enqueue_*`, i18n, and the
  WordPress Coding Standards.
- **Security:** escape all output (`esc_html` / `esc_attr` / `esc_url`), sanitize
  any input, no secrets in the repo.
- **Frontend quality:** external CSS with reusable classes, Flexbox, `rem` units
  (min `1rem`), semantic HTML5, WCAG 2.1 AA.
- **Docs:** bilingual (English + Ukrainian), kept in sync.
- **noindex:** keep the deployed demos out of search indexes via the WordPress
  *Settings → Reading → Discourage search engines* site setting — **not**
  hardcoded into the distributable theme.
- **Git:** initialise here; local identity `alexnest-ua`
  <alexnest2002@gmail.com>; public repo; step-by-step conventional commits; no
  secrets committed.

## Acceptance criteria ("done")

1. Two child themes that share **no footprint**: distinct `style.css` headers,
   colours, fonts, header and footer structure, text domains and class prefixes.
2. One reproducible ACF group per theme (Local JSON), differing in structure,
   wired into the front end with escaped output.
3. Both themes verified rendering in a real browser (colours, fonts, header,
   footer, ACF), with no PHP notices or console errors.
4. Both sites deployed and reachable; links in the README; demos set to
   discourage search engines.
5. Bilingual documentation covering requirements, plan, architecture, database
   and decisions.
