[English](03-implementation-plan.en.md) · [Українська](03-implementation-plan.uk.md)

# Implementation Plan

How the work was sequenced, and how each step was verified with evidence rather
than assumed.

## Environment (verified before coding)

| Component | Version / fact |
|-----------|----------------|
| PHP | 8.3.6 |
| Database | MySQL 8.0.46 |
| Web server | Nginx 1.24 + PHP-FPM (pool user: `alexnest`) |
| WordPress (current upstream) | 7.0 "Armstrong" (May 2026) |
| GeneratePress (parent) | 3.6.1 |
| Advanced Custom Fields (free) | 6.8.4 |
| Local dev sandbox | `/var/www/testwp` → `https://testwp` (WP 6.7.1, DB `test_wp`) |

The local sandbox is isolated from the real projects on the box; PHP-FPM runs as
`alexnest`, so themes can be developed in this repo and **symlinked** into the
sandbox with no `sudo` and no permission juggling. WP-CLI was added to the home
directory (no root) to drive installs and verification.

## Ordered plan

1. **Repo init** — git with the required *local* identity, `.gitignore`
   (secrets, build artefacts, the private brief), `.editorconfig` (tabs per
   WPCS).
2. **Dev setup** — install GeneratePress + ACF-free into the sandbox; create
   demo pages/posts, a primary and a footer menu, a static front page.
3. **Read the parent** — map GeneratePress' header/footer/nav hooks and filters
   *before* overriding anything (override only what's needed). → [architecture](04-architecture.en.md)
4. **Child theme A — Verdal** — `style.css` header + tokens, `functions.php`
   (enqueue, nav location, ACF load point, intro render, custom footer), ACF
   Local JSON; symlink, activate, verify, commit.
5. **Child theme B — Meridian Edge** — the same surface built as a deliberately
   **different** product; symlink, activate, verify, commit.
6. **Browser verification** — load both at `https://testwp`, confirm distinct
   colours / fonts / header / footer and working ACF, check the console, capture
   screenshots; fix what testing surfaces.
7. **Documentation** — Part 1 solution, requirements, this plan, architecture,
   database, decisions — all bilingual; then the README.
8. **Deploy** — both sites to InstaWP, enable *Discourage search engines*, add
   live links.
9. **Final review** — code-review + security pass, confirm deploys load.

## Verification approach (acceptance bar)

- **PHP:** `php -l` on every PHP file; pages loaded with **zero** notices/warnings
  (`curl` grep + browser).
- **Structure:** `curl` markers per theme (enqueue handles, class names, copy);
  cross-checked that theme A's markers are **absent** on theme B (no footprint
  bleed).
- **ACF:** `get_field()` returns saved values on the front end (proves Local JSON
  loaded), banner/intro render with escaped output.
- **Visual:** real-browser screenshots at desktop width for both themes' home,
  footer and the single-post CTA.
- **Console:** no errors/warnings on load.

## Scope notes

- The first cut was a minimal, hook-only GeneratePress child. The scope was then
  expanded (on request) to a full template set, self-hosted fonts, a minified
  CSS/JS pipeline, SEO + head cleanup, and progressive JS — see
  [decisions](06-decisions.en.md).
- Still **classic, not block**: GeneratePress is a PHP-template theme, so the
  children stay classic (no `theme.json`); no page builder.
- The **header and footer stay hook-based** even though content templates are
  overridden — only what each theme needs is duplicated.
- ACF uses **free** field types only (no Pro repeater / flexible content).
