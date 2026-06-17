[English](02-solution.en.md) · [Українська](02-solution.uk.md)

# Part 1 — Solution Document: Theme Uniqueization

> Free-form reasoning on de-footprinting a network of sites that share a common
> theme. The goal is structured thinking, not a single "correct" answer.

## What "theme uniqueization" means from Google's perspective

Google does not penalize a shared theme as such — it reacts to **footprints**:
repeating, machine-detectable signals that several sites are run by one operator
to manipulate rankings (the PBN / doorway pattern). A theme is one of the
strongest footprints because it leaves fingerprints at **every layer**: identical
DOM structure and CSS class names, the same asset paths and file hashes, the same
fonts, the same footer credit line, the same plugin set, and often the same
hosting, IPs and analytics IDs. Uniqueization means breaking the **correlation**
between sites so each one reads as an independently built and maintained
property — visually, structurally and technically — not merely a re-skin of one
template.

## Layers of uniqueization, and which matters most

1. **Technical / structural footprint — most important.** HTML structure and
   class names, theme name/author/version, asset URLs and hashes, plugin
   fingerprints, robots/sitemaps, hosting, IP ranges, analytics/AdSense IDs,
   WHOIS. This is exactly what automated link-graph and footprint analysis
   clusters on. If two sites share these, visual differences will not save them.
2. **Content & information architecture.** Page templates, navigation, content
   model and boilerplate copy. Duplicated structure across sites is itself a
   clustering signal.
3. **Presentation — colours, typography, spacing.** Essential for *human*
   dissimilarity and trust, but the cheapest layer to change, so the weakest
   signal on its own.

**Structural/technical de-footprinting matters most; presentation is necessary
but not sufficient.**

## How I would uniqueize 5 sites in a week

- **Day 1 — Audit & fingerprint.** Catalogue everything shared: theme, child
  structure, plugins, fonts, footer/credits, analytics IDs, hosting/IP,
  sitemaps. Define a per-site "identity matrix" (palette, type pair, header and
  footer structure, content model).
- **Days 2–4 — Structural divergence.** Give each site a genuinely different
  template skeleton: distinct header/footer structure, distinct DOM/class
  naming, distinct theme metadata, a varied plugin mix, self-hosted vs CDN
  fonts. Where feasible, vary the **parent theme** too, not only the child.
- **Day 5 — Presentation & content.** Apply distinct palettes/typography,
  rewrite shared boilerplate (footers, about, legal), use unique imagery.
- **Day 6 — Infrastructure.** Spread hosting/IP, separate Analytics and Search
  Console properties, distinct WHOIS where legitimate.
- **Day 7 — Verify.** Re-run the fingerprint audit, diff the sites against each
  other, and confirm no shared IDs, hashes or credit strings remain.

## First vs last

- **First:** the audit, then the highest-correlation technical signals
  (theme / plugin / analytics / hosting fingerprints) — they carry the most
  clustering weight.
- **Last:** visual polish and microcopy — cheap and low-risk, done once the
  structure is sound.

## Risks and how I would mitigate them

- **SEO regressions while restructuring** → stage on clones, keep URLs and
  redirects stable, watch Search Console for crawl/index changes.
- **Cosmetic-only changes still cluster** → insist on structural **and**
  infrastructure divergence, not just CSS.
- **A new uniformity** (the same "randomiser" applied to every site becomes a
  *new* footprint) → vary deliberately and by hand, not procedurally.
- **Over-optimisation that looks manipulative** → aim for genuinely
  independent-looking properties and improve real UX/quality, rather than only
  evading detection.

---

See [decisions & notes](06-decisions.en.md) for how this thinking maps onto the
two child themes built in Part 2.
