[English](04-architecture.en.md) · [Українська](04-architecture.uk.md)

# Architecture & Code Structure

## Repository layout

```text
growica-test-task/
├── README.md / README.uk.md
├── .editorconfig / .gitignore
├── bin/                               # reproducible asset build + vendored parent inputs (never web-served)
├── docs/                              # bilingual docs (01–06, *.en.md / *.uk.md)
└── themes/
    ├── verdal/                        # Child theme A — editorial / wellness
    └── meridian-edge/                 # Child theme B — product / engineering
```

Each child theme has the same shape (a full classic template set), so they are
directly comparable while sharing no footprint:

```text
themes/<theme>/
├── style.css                # theme header only (unique per theme)
├── functions.php            # setup, asset pipeline, hooks, SEO/clean-head, ACF wiring, footer
├── inc/
│   └── template-tags.php     # reusable render helpers (entry meta, ACF block, SEO description)
├── template-parts/
│   ├── entry-header.php       # title (+ post meta) — reused by single & page
│   ├── content.php            # single post body
│   ├── content-card.php       # post card — reused by blog / archive / search
│   └── content-none.php       # empty-state — reused by blog / archive / search
├── page.php  single.php  archive.php  search.php  404.php  index.php
├── css/
│   ├── main.css               # source
│   └── main.min.css           # built (enqueued)
├── js/
│   ├── theme.js               # source
│   └── theme.min.js           # built (enqueued)
├── fonts/                      # self-hosted woff2 (latin subset)
└── acf-json/                   # ACF Local JSON (auto-loaded, reproducible)
```

No build step is required to install a theme — the `*.min` bundles are committed.
`bin/build-assets.sh` (using `csso` / `terser`) only rebuilds them from source.

## Child-theme anatomy

| Path | Responsibility |
|------|----------------|
| `style.css` | Required WordPress theme header (unique Name/Author/Description/Version); styles live in `css/`. |
| `functions.php` | Theme setup (i18n, footer menu, card image size), asset enqueue, font preload, nav/layout filters, ACF load point, SEO + clean-head, the custom footer. |
| `inc/template-tags.php` | Reusable render helpers called from the templates. |
| `template-parts/*` | Components pulled in with `get_template_part()` and reused across templates. |
| `*.php` templates | The template hierarchy: page, single, archive, search, 404, index. |
| `css/`, `js/` | Source + minified assets. |
| `fonts/` | Self-hosted woff2. |
| `acf-json/` | ACF Local JSON field group. |

## Asset pipeline & parent-footprint removal

Each child is served as **one self-contained bundle per type**, built from its
readable sources by `bin/build-assets.sh` (`csso` / `terser`):

- `css/main.min.css` = GeneratePress framework CSS + the site's dynamic preset
  snapshot + GP's comments component + the child's own CSS;
- `js/theme.min.js` = the parent menu + a11y scripts (menu config variable renamed
  per site) + the child's enhancement.

The vendored parent inputs live in `bin/vendor/`, **outside any theme directory**,
so they are never web-served alone. Because the framework now ships inside the
child's own bundle, `functions.php` enqueues only that bundle and removes every
separate parent reference from the page:

```php
// enqueue only the child's own bundle — no generate-style dependency
wp_enqueue_style( 'verdal-main', "{$uri}/css/main.min.css", array(), VERDAL_VERSION );
wp_enqueue_script( 'verdal-theme', "{$uri}/js/theme.min.js", array(), VERDAL_VERSION, true );

add_action( 'wp_enqueue_scripts', 'verdal_mask_parent_assets', 100 ); // drop parent-dir assets + generate-child
add_filter( 'body_class', 'verdal_body_class' );                       // strip wp-theme-generatepress
add_filter( 'generate_print_a11y_script', '__return_false' );          // GP a11y ships in the bundle instead
add_filter( 'wp_speculation_rules_configuration', '__return_null' );   // drops the theme-path speculation block
```

The result: the rendered HTML carries no `/themes/generatepress/` path, no
`generate-*` handle, no `generatepressMenu` variable and no shared `?ver=3.6.1`;
each site's only asset traces (the bundle URL, version and inline menu-config
variable) are unique. See
[the decisions doc](06-decisions.en.md#removing-the-generatepress-parent-footprint-after-review)
for the full reasoning and the honest residuals.

### A lean document head

Each theme also unhooks WordPress' largest inlined stylesheet — the `theme.json`
preset sheet (`wp_enqueue_global_styles`) — and dequeues the core
`block-library` / `classic-theme` styles it never uses, because both themes paint
entirely from their own design tokens and never touch the block colour/gradient
presets. WordPress then serves only each *used* block's small stylesheet on
demand. The `screen-reader-text` utility lives in `main.css`, so the skip-link
accessibility never depends on the removed sheets. This is implemented with
different code in each theme (Verdal: explicit `wp_dequeue_style()` calls;
Meridian Edge: an array loop) so it adds no shared footprint.

### Images

All content and Open Graph images are served as **WebP**. The theme
`screenshot.png` and `apple-touch-icon.png` deliberately stay PNG — WordPress
requires a PNG/JPG theme screenshot and iOS home-screen icons must be PNG.
Featured images declare `width`/`height` to reserve space (no CLS); the LCP hero
image uses `fetchpriority="high"`, and below-the-fold media uses `loading="lazy"`.

## Self-hosted fonts

Fonts are shipped as woff2 (latin) under `fonts/`, declared with `@font-face` +
`font-display: swap` in `main.css`, and the two above-the-fold faces are preloaded
via the `wp_preload_resources` filter — removing the third-party Google Fonts
connection and protecting LCP/CLS.

## Templates & reusable parts

The templates own the content area; the header and footer remain hook-based:

- Every template calls `get_header()` / `get_footer()`, so the masthead and the
  custom footer still come from `functions.php` hooks (no `header.php`/`footer.php`).
- Shared markup lives in `template-parts/` and is pulled in with
  `get_template_part()` (e.g. the post card is reused by blog, archive and search).
- The **front-page hero** is rendered above `#content` via the
  `generate_after_header` hook. GeneratePress makes `#content` a flex row, so a
  hero echoed from inside the template would sit *beside* the content column;
  the hook places it full-width above. The main loop is spun once and
  `rewind_posts()` restores it for the template's own loop, keeping exactly one
  `<h1>` per view.
- Other ACF output (Verdal's interior page-intro block, Meridian's post CTA) is
  rendered **directly in the templates** via `inc/template-tags.php`, so it does
  not depend on a GeneratePress content hook firing.
- `generate_sidebar_layout` is forced to `no-sidebar`; each theme controls its
  content measure in CSS.

## Hooks & filters used (override only what's needed)

| Concern | Mechanism | Verdal (A) | Meridian Edge (B) |
|---------|-----------|------------|-------------------|
| Nav position | `generate_navigation_location` | `nav-below-header` (centred) | `nav-float-right` (logo-left/menu-right) |
| Layout | `generate_sidebar_layout` | `no-sidebar` | `no-sidebar` |
| Front-page hero | `generate_after_header` (full-width, above `#content`) | eyebrow + title + lead + image | pill + title + lede + CTA pair |
| Trim core CSS | unhook `wp_enqueue_global_styles`; dequeue `block-library`/`classic-theme` | ✔ explicit calls | ✔ array loop |
| Footer | remove GP footer + `add_action('generate_footer', …)` (removal deferred to `after_setup_theme`) | 3 columns + centred copyright | dark 4 columns + split bottom bar |
| Font preload | `wp_preload_resources` | Lora 700 + Mulish 400 | Space Grotesk 700 + IBM Plex 400 |
| ACF source | `acf/settings/load_json` → `acf-json/` | ✔ | ✔ |
| ACF render | hero via `generate_after_header`; rest in-template | Page Intro → front-page hero | post CTA on `single.php` |
| Footer menu | `register_nav_menus('footer-menu')` | ✔ | ✔ |
| Head cleanup | `init` / `wp_head` | strip generator/shortlink/RSD/WLW/emoji | same intent, own implementation |
| Mask parent assets | `wp_enqueue_scripts` (pri 100): dequeue by source dir + `generate-child` | ✔ | ✔ |
| Body classes | `body_class`: strip `wp-theme-generatepress` / `wp-child-theme-*` | ✔ | ✔ |
| Parent a11y | `generate_print_a11y_script` → `false` (behaviour bundled instead) | ✔ | ✔ |
| Speculation rules | `wp_speculation_rules_configuration` → `null` (removes theme-path block) | ✔ | ✔ |

## SEO & clean head

Each theme emits its own lightweight metadata and strips WordPress fingerprints
from `<head>`. The **method differs per theme** and both yield to a dedicated SEO
plugin (AIOSEO / Yoast / Rank Math) when one is active:

- **Verdal** → meta description + Open Graph.
- **Meridian Edge** → Twitter Card + schema.org JSON-LD (Article / WebSite),
  encoded with `wp_json_encode( …, JSON_HEX_TAG | JSON_HEX_AMP )` so a title
  containing `</script>` cannot break out of the script block.

## Progressive-enhancement JS

One small vanilla-JS enhancement per theme, reduced-motion aware, degrading to
full functionality without JS:

- **Verdal** — reveal below-the-fold cards/intro with `IntersectionObserver`
  (above-the-fold content is left untouched to protect LCP).
- **Meridian Edge** — condensing sticky header on scroll (rAF-throttled, passive
  listener; the sticky CSS is opted in only when JS runs).

## Differentiation matrix (no shared footprint)

| Axis | Verdal (A) | Meridian Edge (B) |
|------|------------|-------------------|
| Product feel | Editorial / wellness | Product / engineering |
| Background / ink | `#f4f7f4` / `#18271f` (warm green) | `#ffffff` / `#14161f` (cool ink) |
| Primary | `#1f6b53` teal-green | `#2348c8` cobalt |
| Heading font | Lora (serif) | Space Grotesk (grotesque) |
| Body font | Mulish | IBM Plex Sans |
| Header | Centred logo, nav centred below | Logo left, uppercase nav right |
| Footer | Light, 3 columns, centred italic copyright | Dark, 4 columns, copyright left + back-to-top |
| Copyright | "© {y} {site}. Made calmly, by hand." | "© {y} {site}. Built for speed." |
| SEO method | meta description + Open Graph | Twitter Card + JSON-LD |
| JS | reveal-on-scroll | condensing sticky header |
| Card style | soft, rounded, media-top | sharp "spec-sheet", uppercase byline |
| Comment voice | prose banner comments | terse lowercase markers |
| Text domain / prefixes | `verdal`, `--vd-*`, `.verdal-*`, `verdal_*` | `meridian-edge`, `--me-*`, `.me-*`, `meridian_edge_*` |
| `style.css` Author / Version | Sagewright Studio / 1.3.0 | Brightseam Labs / 2.4.0 |
| ACF group | Page Intro on pages, 5 fields | Post CTA Banner on posts, 6 fields + conditional logic |

The themes share **no authored comments, docblocks, helper bodies, section-comment
style, prefixes or class names** — only unavoidable WordPress/CSS API surface
(e.g. the WPCS `translators:` comment format, `@font-face`, hook argument keys).

## Output safety & accessibility

- Every dynamic value is escaped at output (`esc_html` / `esc_attr` / `esc_url` /
  `esc_html__` / `wp_kses_post`); ACF link/image arrays are null-checked.
- One `<h1>` per view, logical heading order, one `<main>`, `<nav aria-label>`
  landmarks, `<article>` / `<aside>` / `<footer>` semantics.
- Focus-visible outlines, ≥44px touch targets, AA-contrast palettes, alt text,
  `prefers-reduced-motion` handling, and a `screen-reader-text` utility for
  context links.
