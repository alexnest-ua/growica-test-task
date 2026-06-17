<!-- Language: English | [Українська](README.uk.md) -->

# Growica Test Task — Theme Uniqueization

[English](README.md) · [Українська](README.uk.md)

Two GeneratePress **child themes** built to feel like **different products** — a
practical answer to "theme uniqueization / de-footprinting": making sites that
share a parent theme look and read as independent properties so they don't
cluster as one network.

- **Verdal** — editorial / wellness (warm green, Lora + Mulish, centred masthead, light 3-column footer).
- **Meridian Edge** — product / engineering (cobalt + ink, Space Grotesk + IBM Plex Sans, logo-left/menu-right header, dark 4-column footer).

There is **no shared footprint** between them: independent `style.css` headers,
colours, fonts, header/footer structure, text domains, class prefixes and ACF
field groups.

## Live demos

> Deployed on InstaWP, with *Settings → Reading → Discourage search engines*
> enabled on each (per the noindex requirement). Free hosted sites are
> ephemeral — if a link has expired, the themes install from this repo in
> minutes (see [Install](#install-locally)).

| Theme | Live URL |
|-------|----------|
| **Verdal** | _added after deployment_ |
| **Meridian Edge** | _added after deployment_ |

## Why GeneratePress

The leanest of GeneratePress / Astra / Kadence, with a hook/filter architecture
that lets two children diverge **structurally in code** (navigation position,
header, footer) with almost no template duplication — the cleanest way to
demonstrate de-footprinting. Full reasoning, including the trade-off of sharing
one parent, is in [docs/06-decisions](docs/06-decisions.en.md).

## Repository structure

```text
themes/<theme>/
├── style.css            Theme header (unique per theme)
├── functions.php        Setup, asset pipeline, hooks, SEO/clean-head, ACF
├── inc/                 Reusable template tags + render helpers
├── template-parts/      Shared components (entry-header, cards, no-results)
├── page.php single.php archive.php search.php 404.php index.php
├── css/                 main.css (source) + main.min.css (enqueued)
├── js/                  theme.js (source) + theme.min.js (enqueued)
├── fonts/               Self-hosted woff2 (font-display: swap)
└── acf-json/            ACF Local JSON (auto-loaded, reproducible)

docs/                    Bilingual documentation (see index below)
```

Each theme ships a full template set built on reusable `template-parts/`,
self-hosted fonts, a minified CSS/JS pipeline (unminified sources serve under
`SCRIPT_DEBUG`), lightweight SEO + a head-cleanup pass, and progressive-
enhancement JS. A deeper walkthrough — child-theme anatomy, the hooks used and
the full A-vs-B differentiation matrix — is in
[docs/04-architecture](docs/04-architecture.en.md).

## Install locally

Requirements: WordPress 6.5+, PHP 7.4+ (built and verified on PHP 8.3 / WP 7.0).

1. **Parent theme** — install and keep *GeneratePress* active-capable:
   `Appearance → Themes → Add New → search "GeneratePress" → Install`.
2. **ACF (free)** — `Plugins → Add New → search "Advanced Custom Fields" →
   Install → Activate`.
3. **Child theme** — copy **one** folder into `wp-content/themes/`:
   ```bash
   cp -r themes/verdal /path/to/wp-content/themes/
   # or
   cp -r themes/meridian-edge /path/to/wp-content/themes/
   ```
4. **Activate** the child theme (`Appearance → Themes`).
5. **Menus** — `Appearance → Menus`: assign a menu to **Primary** and to
   **Footer Menu**.
6. **ACF is already reproducible** — the field group auto-loads from the theme's
   `acf-json/` directory (no import needed). To see it in action, edit a **page**
   (Verdal → "Page Intro") or a **post** (Meridian Edge → "Post CTA Banner"),
   fill the fields and view the front end.

> The two child themes are installed the same way on the hosted demos. Each is a
> classic child theme — no build step.

## Documentation

| Topic | English | Українська |
|-------|---------|------------|
| Requirements | [01-requirements.en](docs/01-requirements.en.md) | [01-requirements.uk](docs/01-requirements.uk.md) |
| Part 1 — Solution (de-footprinting) | [02-solution.en](docs/02-solution.en.md) | [02-solution.uk](docs/02-solution.uk.md) |
| Implementation plan | [03-implementation-plan.en](docs/03-implementation-plan.en.md) | [03-implementation-plan.uk](docs/03-implementation-plan.uk.md) |
| Architecture & code | [04-architecture.en](docs/04-architecture.en.md) | [04-architecture.uk](docs/04-architecture.uk.md) |
| Database & data | [05-database.en](docs/05-database.en.md) | [05-database.uk](docs/05-database.uk.md) |
| Decisions & trade-offs | [06-decisions.en](docs/06-decisions.en.md) | [06-decisions.uk](docs/06-decisions.uk.md) |

## Tech & versions

PHP 8.3 · MySQL 8.0 · WordPress 7.0 · GeneratePress 3.6.1 · ACF (free) 6.8.4.

## Notes

- `noindex` is a **site setting** on each demo (not hardcoded in the theme) —
  indexing is a deployment concern.
- The private assignment brief is intentionally **not** committed to this public
  repo.
- Git identity for this repo is set locally to `alexnest-ua`.
