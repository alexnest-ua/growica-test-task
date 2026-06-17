[English](05-database.en.md) · [Українська](05-database.uk.md)

# Database & Data Structure

The themes add **no custom tables** and run no migrations. They read and write
only standard WordPress core tables. This document records exactly what data
each theme touches, and how ACF persists it.

## Tables involved (all WordPress core)

| Table | Used for |
|-------|----------|
| `wp_options` | Front-page settings, per-theme `theme_mods_*` (incl. `nav_menu_locations`), ACF's own settings. |
| `wp_postmeta` | **ACF field values** (and ACF's field-key references), per post/page. |
| `wp_posts` | Demo pages/posts and `nav_menu_item` records for menus. |
| `wp_terms`, `wp_term_taxonomy`, `wp_term_relationships` | Menus (the `nav_menu` taxonomy) and their items. |

## ACF: definition vs. values (the reproducibility split)

- **Field group definition** lives in the **repository** as ACF Local JSON
  (`themes/<theme>/acf-json/*.json`), auto-loaded via the
  `acf/settings/load_json` filter. This is the reproducible-from-repo part — it
  is *not* click-only in the database.
- **Field values** (what an editor types per page/post) live in `wp_postmeta`.

ACF stores each field as **two** meta rows: the value, plus a `_`-prefixed row
holding the field **key** (so ACF can resolve the field definition).

### Verdal — "Page Intro" on the Home page (post 5774)

```text
intro_eyebrow    A quiet practice
_intro_eyebrow   field_verdal_intro_eyebrow
intro_lead       Verdal is a calm, unhurried space for slow li…
_intro_lead      field_verdal_intro_lead
intro_cta        a:3:{s:5:"title";s:14:"Book a session";…}   (serialised link array)
_intro_cta       field_verdal_intro_cta
intro_boxed      1
_intro_boxed     field_verdal_intro_boxed
```

### Meridian Edge — "Post CTA Banner" on a post (post 5780)

```text
cta_enabled      1
_cta_enabled     field_me_cta_enabled
cta_kicker       Try it free
cta_heading      Ship your next idea on the edge
cta_text         Spin up a globally distributed preview e…
cta_button       a:3:{s:5:"title";s:14:"Start building";…}   (serialised link array)
cta_variant      solid
_cta_variant     field_me_cta_variant
```

The two groups are **structurally different** (5 vs 6 fields, different field
names/types, different location rule — `page` vs `post`) and different from the
parent (GeneratePress ships no ACF group).

## Menu locations are per-theme

Each theme registers a `footer-menu` location (the `primary` location comes from
GeneratePress). The *assignment* of a menu to a location is a **theme mod**, so
it is stored per stylesheet:

```text
wp_options → theme_mods_verdal         (nav_menu_locations: primary, footer-menu)
wp_options → theme_mods_meridian-edge  (nav_menu_locations: primary, footer-menu)
```

This is why switching the active theme requires re-assigning the menus — the
locations are independent per theme. (On deployment each site assigns its own.)

## Front-page settings (demo content)

```text
wp_options → show_on_front  = page
wp_options → page_on_front  = <Home page id>      # exercises Verdal's page intro
wp_options → page_for_posts = <Journal page id>
```

## Local development database

| Fact | Value |
|------|-------|
| DB name | `test_wp` |
| Host | `localhost` (MySQL 8.0.46) |
| Table prefix | `wp_` |

Credentials live only in `/var/www/testwp/wp-config.php` on the dev box and are
**never** committed (`wp-config.php` is git-ignored). Nothing in the themes
hard-codes any credential, host or secret.
