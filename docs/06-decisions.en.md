[English](06-decisions.en.md) · [Українська](06-decisions.uk.md)

# Decisions, Notes & Trade-offs

The reasoning behind the implementation choices — the "how I think" part.

## Why GeneratePress as the parent

Chosen over Astra and Kadence because it is the **leanest** of the three (~30 KB,
minimal default markup) and its **hook/filter architecture** lets the two
children diverge **structurally in code** — navigation position, header and
footer — with almost no template duplication:

- `generate_navigation_location` moves the menu (below-header vs floated-right)
  from a single filter;
- `generate_footer` actions can be removed and replaced, so each footer is a
  bespoke structure defined in the child;
- minimal, predictable class names make CSS overrides clean.

Astra and Kadence are heavier and ship a larger, more recognisable default
footprint — ironic for a de-footprinting exercise. GeneratePress lets the
divergence be **additive and intentional**, which is exactly the skill the task
probes.

## The honest tension: one parent for two children

Using the **same** parent theme for both children is itself a shared footprint
(same base class names, same asset structure). The brief requires it, and the
point of the exercise is to show you can make two children read as different
products. In real de-footprinting at scale I would also **vary the parent across
sites** (e.g. GeneratePress, _s/underscores, a hand-rolled theme), vary the
plugin set, and diverge at the infrastructure layer — see
[the solution doc](02-solution.en.md). This is called out so the limitation is
explicit, not hidden.

## Two products, not two skins

The differentiation is driven by giving each theme a distinct *product persona*
so colour, type and structure diverge naturally rather than arbitrarily:

- **Verdal** — editorial / wellness: warm green, serif display (Lora), centred
  masthead, calm reading rhythm, a 3-column light footer.
- **Meridian Edge** — product / engineering: cool cobalt + ink, grotesque
  display (Space Grotesk), split logo-left/menu-right header, dark 4-column
  footer.

After an initial modest cut, both themes were **elevated to a premium feel** on
request: oversized fluid type scales (`clamp()`), generous whitespace, a real
full-width hero per theme (editorial for Verdal, product/SaaS for Meridian Edge),
refined buttons and soft low-opacity elevation — while preserving the functional
and structural differentiation the brief values most. The palettes, fonts,
headers, footers and ACF groups stayed distinct throughout.

## Templates: a deliberate override set

The original brief favours "override only what you need"; the expanded scope asks
for a full, uniquely-designed template set to demonstrate the template hierarchy
and reusable components. The tension is resolved as follows:

- Each theme ships `page/single/archive/search/404/index.php` plus a
  `template-parts/` folder of reusable components (entry header, post card,
  no-results) pulled in with `get_template_part()`.
- The **header and footer stay hook-based** — the templates call
  `get_header()`/`get_footer()`, so the centred/split masthead and the custom
  footers still come from `functions.php`; no `header.php`/`footer.php` is copied.
- The front-page hero renders above `#content` via `generate_after_header` (GP
  makes `#content` a flex row, so an in-template hero would sit *beside* the
  content); the remaining ACF output renders **directly in the templates** (via
  `inc/template-tags.php`), so it does not depend on a GP content hook firing.
- `generate_sidebar_layout` is forced to `no-sidebar` so each theme fully owns
  its content layout in CSS.

This is more surface than a pure-hook GP child needs — an intentional trade to
show template-hierarchy fluency. It also reinforces de-footprinting: the two
sites have genuinely different template markup, not just different CSS.

## ACF: Local JSON, free fields only

- **Local JSON** (committed under `acf-json/`, auto-loaded via
  `acf/settings/load_json`) is ACF's recommended reproducible workflow and is
  exactly what the brief asks for ("reproducible from the repo, not click-only").
- Field **keys are readable** (`field_verdal_intro_eyebrow`) rather than random,
  so the JSON is reviewable in a PR.
- Only **free** ACF field types are used (text, textarea, link, image,
  true/false, select) — no Pro-only Repeater/Flexible Content.

## Self-hosted fonts

Each theme self-hosts its woff2 faces (latin subset) under `fonts/`, declared
with `@font-face` + `font-display: swap`, and preloads the two above-the-fold
faces via the `wp_preload_resources` filter. This removes the third-party Google
Fonts connection (privacy / one less origin) and protects LCP/CLS.

## Minified, self-contained asset pipeline

CSS and JS live as readable sources in `css/main.css` and `js/theme.js`. A small,
reproducible build (`bin/build-assets.sh`, using `csso` / `terser`) fuses each
child's source with the vendored parent code into **one bundle per type**:
`main.min.css` = GeneratePress framework CSS + that site's dynamic preset
snapshot + GP's comments-component sheet + the child's styles; `theme.min.js` =
the parent menu + a11y scripts + the child's enhancement. The vendored parent inputs live in `bin/vendor/`,
**outside any theme directory**, so they are never web-served on their own and
carry no comment banner that could leak the parent's name into a bundle. The theme
enqueues only the single bundle per type — which is also what lets the page drop
the parent's separate asset references entirely (see below).

## A lean document head (after review)

WordPress inlines a large `theme.json` preset stylesheet plus the core
`block-library` / `classic-theme` sheets into every page `<head>`. Neither theme
uses those block presets — they paint from their own design tokens — so each
**unhooks `wp_enqueue_global_styles`** and dequeues the unused core sheets, letting
WordPress serve only the small per-block CSS actually used. `screen-reader-text`
is defined in each theme's `main.css`, so removing the core sheets never affects
the skip-link. Implemented with different code per theme (explicit
`wp_dequeue_style()` calls vs an array loop) to avoid a shared footprint.

## Images: WebP, with two deliberate PNG exceptions

All content and Open Graph images are **WebP** (smaller, modern, widely
supported). Two assets stay PNG on purpose: the theme `screenshot.png`
(WordPress's themes screen expects PNG/JPG) and `apple-touch-icon.png` (iOS
home-screen icons must be PNG). The favicon is an SVG with the PNG apple-touch
fallback.

## SEO + clean head (self-made, plugin-aware)

Rather than depend on a plugin, each theme emits its own lightweight meta and
strips WordPress fingerprints (generator/version, shortlink, RSD, WLW, emoji)
from `<head>` — itself a de-footprinting measure. The **method differs per
theme** (Verdal: meta description + Open Graph; Meridian Edge: Twitter Card +
JSON-LD), and both **yield to a dedicated SEO plugin** (AIOSEO / Yoast / Rank
Math) when one is active, to avoid duplicate tags. On the hosted demos a small,
**different** plugin set per site extends the de-footprinting to the plugin layer.

## Progressive-enhancement JS

Each theme ships one small vanilla-JS enhancement, gated on `prefers-reduced-
motion` and degrading to full functionality without JS: Verdal reveals
below-the-fold cards with `IntersectionObserver` (above-the-fold content is left
untouched to protect LCP); Meridian Edge condenses its header on scroll. Two
different behaviours, no shared code.

## De-footprinting the two themes (after review)

A code review of the first cut flagged that the two themes shared identical
comments and a byte-identical helper — one author's find-and-replace. They were
rebuilt to read as independent work: different comment voice (Verdal prose
banners vs Meridian Edge terse markers), different function naming and code
organisation, a different SEO method, different JS, and different template
markup — on top of the already-distinct palettes, fonts, headers, footers and
ACF groups.

## Removing the GeneratePress parent footprint (after review)

A later review flagged the deepest shared trace: both demos referenced the parent
**identically** — same path `/wp-content/themes/generatepress/…`, same handle
`generate-style`, same `?ver=3.6.1`. On a 30-site network that exact URL is a
trivial cross-site cluster key, even though the *child* assets already differed.
The fix makes each child fully self-contained and strips every parent reference
from the rendered HTML, while keeping the required parent→child architecture.

Per child, in `functions.php`:

- **Self-contained bundle** — framework CSS, the site's dynamic preset snapshot,
  GP's comments-component sheet and the child's own CSS are fused into one
  `main.min.css`; the parent menu + a11y scripts and the child's enhancement into
  one `theme.min.js` (see the pipeline above). The page now loads only the child's
  own files. (GP serves the comments sheet on demand on singular views with open
  comments — which the mask below also strips — so it is folded in; the themes use
  no other on-demand GP component, so nothing else needs bundling.)
- **Mask the parent's copies** — `*_mask_parent_assets()` dequeues any style or
  script whose source lives in the parent directory, plus the parent-injected
  link to the child `style.css`. The GP menu config object is re-emitted under a
  per-site variable name (`vdMenuCfg` / `meMenuCfg`); GP's inline "using-mouse"
  helper — printed directly on `wp_footer` with a hard-coded `id="generate-a11y"`,
  so a dequeue can't reach it — is switched off through its own
  `generate_print_a11y_script` filter, since the same behaviour ships in the
  bundle.
- **Body classes** — a `body_class` filter drops `wp-theme-generatepress` and
  `wp-child-theme-*`.
- **Speculation rules** — WordPress hard-codes the active template + stylesheet
  directories into the `<script type="speculationrules">` exclusion list and
  exposes no filter over the final rules, so the parent path can't be removed
  surgically. The block is disabled (`wp_speculation_rules_configuration` →
  `null`), which also stops it advertising the theme directory layout;
  prefetch-on-hover is a minor nicety these lean pages don't need.

**Result (verified in-browser on both live demos).** The source HTML of every
page — home, journal, about — contains **zero** occurrences of `generatepress`,
`generate-*`, `/themes/generatepress/`, `?ver=3.6.1`, the `generatepressMenu`
variable or the `wp-theme-generatepress` body class. The only per-site traces
left — the bundle URLs, their versions and the inline menu-config variable —
**differ between the two sites** in path, version and name, exactly as the child
assets always did. Layout, the mobile menu toggle and the enhancement JS were all
confirmed working after the change.

**Honest residuals.** Two traces are inherent to the brief's requirement to build
on a shared parent, and are called out rather than hidden:

- GeneratePress still renders generic framework **class names** in its markup
  (`grid-container`, `main-navigation`, `gp-icon`, `sf-menu`, the
  `separate-containers` body class). These are shared by millions of GP sites, so
  they are *not* a per-network cluster key the way the now-removed unique
  path + version was. Erasing them entirely means forking GP into a standalone
  theme — which the parent + two-children brief forbids.
- The child `style.css` keeps the WordPress-required `Template: generatepress`
  header. It is fetchable only by requesting that file directly and never appears
  in any rendered page.

At true network scale the complete answer is also to **vary the parent** across
sites (see [the honest tension](#the-honest-tension-one-parent-for-two-children)),
which removes even the class-name commonality.

## Bugs found during verification (and fixed)

Real-browser testing earned its keep:

1. **GeneratePress credit still rendered.** The child's `remove_action()` for
   GP's footer ran at parse time, *before* the parent registered those callbacks
   (child `functions.php` loads first). Fix: defer removal to `after_setup_theme`
   in both themes. Leaving it would have been both a duplicate copyright and a
   shared footprint.
2. **Verdal footer third column escaped its container.** A `<nav>` opened in the
   footer was closed with `</div>`, so the browser closed the grid early. Fix:
   close it with `</nav>`. Caught by inspecting the live DOM vs. the server HTML.
3. **Redesigned hero rendered beside the content, not above it.** The new hero was
   echoed from inside the template, but GeneratePress makes `#content` a flex row —
   so the hero became a flex sibling of `#primary`, sat in a narrow column and
   squashed the feature image to a thumbnail. Caught immediately in the browser.
   Fix: render the hero from `generate_after_header` (full width, above `#content`)
   in both themes, spinning the loop once and `rewind_posts()`-ing it back.

These are recorded in git history as `fix:` commits rather than amended away, so
the verify→fix loop stays visible.

## Risks & mitigations (this implementation)

| Risk | Mitigation |
|------|------------|
| Hosted demos disappearing | Both run on InstaWP's paid Sandbox plan, so they persist (no 48-hour expiry); each theme also installs from the repo in minutes if ever needed. |
| GP Customizer settings not in repo | All structural choices are in **code**, so the theme rebuilds without any DB-stored Customizer state. |
| UI regression from CSS/markup changes | Every change is verified in a real browser on the live demos — hero placement, one `<h1>` per view, WebP delivery, reduced inline CSS — before sign-off. |
| Theme name/slug collision across a network | Each site uses unique Theme Name, text domain and prefixes; at scale, generate per-site identities. |

## Other notes

- The assignment brief is **git-ignored** rather than republished — it is a
  company's private interview material and this repo is public.
- `noindex` is applied as a **site setting** (Settings → Reading) on each demo,
  not hardcoded into the theme — indexing is a deployment concern, not a theme
  concern.
- Commits are layered and conventional; git identity is set **locally** to
  `alexnest-ua` so it does not use the machine's default account.
