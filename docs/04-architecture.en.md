[English](04-architecture.en.md) · [Українська](04-architecture.uk.md)

# Architecture & Code Structure

## Repository layout

```text
growica-test-task/
├── README.md / README.uk.md
├── .editorconfig / .gitignore
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

No build step is required to install a theme — the `*.min` files are committed.
`csso` / `terser` are only needed to rebuild them from source.

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

## Asset pipeline

Readable sources (`css/main.css`, `js/theme.js`) are minified to `*.min` files
with `csso` and `terser`. `functions.php` enqueues the minified files, and serves
the unminified sources when `SCRIPT_DEBUG` is enabled:

```php
$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
wp_enqueue_style( 'verdal-main', "{$uri}/css/main{$min}.css", array( 'generate-style' ), VERDAL_VERSION );
```

The stylesheet depends on the parent handle **`generate-style`** — GeneratePress
enqueues its own CSS under that handle (from `assets/css/`, not its `style.css`),
so the child loads after it without re-enqueuing an empty parent stylesheet.

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
- ACF is rendered **directly in the templates** via `inc/template-tags.php`, so it
  does not depend on a GeneratePress content hook firing.
- `generate_sidebar_layout` is forced to `no-sidebar`; each theme controls its
  content measure in CSS.

## Hooks & filters used (override only what's needed)

| Concern | Mechanism | Verdal (A) | Meridian Edge (B) |
|---------|-----------|------------|-------------------|
| Nav position | `generate_navigation_location` | `nav-below-header` (centred) | `nav-float-right` (logo-left/menu-right) |
| Layout | `generate_sidebar_layout` | `no-sidebar` | `no-sidebar` |
| Footer | remove GP footer + `add_action('generate_footer', …)` (removal deferred to `after_setup_theme`) | 3 columns + centred copyright | dark 4 columns + split bottom bar |
| Font preload | `wp_preload_resources` | Lora 600 + Mulish 400 | Space Grotesk 600 + IBM Plex 400 |
| ACF source | `acf/settings/load_json` → `acf-json/` | ✔ | ✔ |
| ACF render | in-template (`inc/template-tags.php`) | page intro on `page.php` | post CTA on `single.php` |
| Footer menu | `register_nav_menus('footer-menu')` | ✔ | ✔ |
| Head cleanup | `init` / `wp_head` | strip generator/shortlink/RSD/WLW/emoji | same intent, own implementation |

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
| `style.css` Author / Version | Sagewright Studio / 1.1.0 | Brightseam Labs / 2.2.0 |
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
