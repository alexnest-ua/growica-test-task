[English](04-architecture.en.md) · [Українська](04-architecture.uk.md)

# Architecture & Code Structure

## Repository layout

```text
growica-test-task/
├── README.md / README.uk.md          # overview + parent rationale + install + live links
├── .editorconfig                      # tabs for PHP/CSS/JS (WordPress Coding Standards)
├── .gitignore                         # secrets, build artefacts, the private brief
├── docs/
│   ├── 01-requirements.{en,uk}.md
│   ├── 02-solution.{en,uk}.md         # Part 1 essay
│   ├── 03-implementation-plan.{en,uk}.md
│   ├── 04-architecture.{en,uk}.md     # (this file)
│   ├── 05-database.{en,uk}.md
│   └── 06-decisions.{en,uk}.md
└── themes/
    ├── verdal/                        # Child theme A — editorial / wellness
    │   ├── style.css                  # theme header + full stylesheet (tokens + classes)
    │   ├── functions.php              # enqueue, nav location, footer, ACF wiring
    │   └── acf-json/
    │       └── group_verdal_page_intro.json
    └── meridian-edge/                 # Child theme B — product / tech
        ├── style.css
        ├── functions.php
        └── acf-json/
            └── group_meridian_edge_cta.json
```

Themes are installed by copying a single folder from `themes/` into
`wp-content/themes/`. Each is a **classic** GeneratePress child theme — no build
step, no block theme / `theme.json`.

## Child-theme anatomy

| File | Responsibility |
|------|----------------|
| `style.css` | Required WordPress theme header (unique per theme) **plus** the full stylesheet: design tokens in `:root`, reusable BEM-ish classes, Flexbox layout, `rem` units, focus-visible, reduced-motion. |
| `functions.php` | Theme setup (i18n, footer menu), asset enqueue, navigation placement, ACF load point + render, custom footer. All output escaped. |
| `acf-json/*.json` | ACF **Local JSON** — the field group definition, version-controlled and auto-loaded. |

## Parent → child style enqueue (a deliberate detail)

GeneratePress enqueues its own CSS under the handle **`generate-style`** (from
`assets/css/`, *not* from its `style.css`). So the child does **not** blindly
re-enqueue a parent `style.css` (which contains no CSS). Instead it enqueues its
own stylesheet declaring the parent handle as a dependency, so it always loads
after the parent:

```php
wp_enqueue_style( 'verdal-style', get_stylesheet_uri(), array( 'generate-style' ), VERDAL_VERSION );
```

Fonts are enqueued separately (Google Fonts, `display=swap`) with `preconnect`
resource hints via the `wp_resource_hints` filter.

## Hooks & filters used (override only what's needed)

| Concern | Mechanism | Verdal (A) | Meridian Edge (B) |
|---------|-----------|------------|-------------------|
| Nav position | `generate_navigation_location` filter | `nav-below-header` (centred) | `nav-float-right` (logo-left/menu-right) |
| Footer | `remove_action` GP footer + `add_action('generate_footer', …)` | 3 columns + centred copyright | dark 4 columns + split bottom bar |
| Footer-hook removal timing | deferred to `after_setup_theme` | ✔ | ✔ |
| ACF source | `acf/settings/load_json` → theme `acf-json/` | ✔ | ✔ |
| ACF render | `generate_after_entry_header` | page intro | — |
| ACF render | `generate_after_content` | — | single-post CTA |
| Footer menu | `register_nav_menus('footer-menu')` | ✔ | ✔ |

> **Load-order note:** a WordPress child theme's `functions.php` loads *before*
> the parent's. GeneratePress registers its footer callbacks at parent load
> time, so the `remove_action()` calls are deferred to `after_setup_theme` —
> otherwise they would run too early and no-op. (This was caught in browser
> testing; see [decisions](06-decisions.en.md).)

No parent template files are copied — header, footer and navigation structure
are all achieved through hooks/filters + CSS, which keeps the children lean and
the divergence purely additive.

## Differentiation matrix (no shared footprint)

| Axis | Verdal (A) | Meridian Edge (B) |
|------|------------|-------------------|
| Product feel | Editorial / wellness | Product / engineering |
| Background / ink | `#f4f7f4` / `#18271f` (warm green) | `#ffffff` / `#14161f` (cool ink) |
| Primary | `#1f6b53` teal-green | `#2348c8` cobalt |
| Heading font | Lora (serif) | Space Grotesk (grotesque) |
| Body font | Mulish (humanist sans) | IBM Plex Sans |
| Header | Centred logo, nav centred below | Logo left, uppercase nav right |
| Footer | Light, 3 columns, centred italic copyright | Dark, 4 columns, copyright left + back-to-top |
| Copyright string | "© {y} {site}. Made calmly, by hand." | "© {y} {site}. Built for speed." |
| Text domain | `verdal` | `meridian-edge` |
| CSS prefix | `--vd-*`, `.verdal-*` | `--me-*`, `.me-*` |
| Function prefix | `verdal_*` | `meridian_edge_*` |
| `style.css` Author / Version | Sagewright Studio / 1.0.3 | Brightseam Labs / 2.1.0 |
| ACF group | Page Intro on **pages**, 5 fields | Post CTA Banner on **posts**, 6 fields + conditional logic |

There are **no shared comments, signatures, prefixes or class names** between the
two themes — the `style.css` headers and all identifiers are independent.

## Output safety & accessibility

- Every dynamic value is escaped at output: `esc_html`, `esc_attr`, `esc_url`,
  and `esc_html__` / `printf` for translatable strings.
- ACF link/image arrays are null-checked before use; the image renders with
  `alt`, `width`/`height` (no CLS) and `loading="lazy"`.
- Semantic landmarks: a single `<footer>` per page, `<nav aria-label>` for the
  footer menu, `<aside>` for the CTA. Focus-visible outlines, ≥44px touch
  targets on buttons, AA-contrast palettes, and `prefers-reduced-motion`
  handling.
