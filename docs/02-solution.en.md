[English](02-solution.en.md) · [Українська](02-solution.uk.md)

# Part 1 — Making sites stop looking like one network

*Free-form and first person — the brief asks how I think, so this is me reasoning out loud rather than reciting a checklist.*

**What Google actually reacts to.** Not "you used GeneratePress" — millions of sites share a theme. The problem is *footprints*: a group of sites that correlate too tightly. Same DOM and class names, same asset paths and file hashes, the same footer credit, the same plugins, often the same IP, analytics ID and WHOIS. Once enough of those line up, the sites read as one operator gaming rankings — and that's what gets devalued. So to me, uniqueization means breaking the *correlation*: each site should look independently built and maintained, not one template re-skinned.

**The layers, and the one I'd protect first.** I think of three, in order of weight:

1. **Technical / structural — most important.** Markup, class names, theme metadata, asset hashes, plugin set, hosting, IPs, analytics IDs. This is exactly what automated footprint analysis clusters on — get it wrong and no amount of restyling saves you.
2. **Content & architecture.** Templates, navigation, boilerplate copy. Duplicated structure is itself a signal.
3. **Presentation.** Colour, type, spacing — what humans notice, but the cheapest layer to change, so the weakest on its own.

My rule of thumb: presentation is necessary, never sufficient.

**Five sites in a week.** I'd front-load the audit and the expensive signals:

- **Day 1** — audit everything shared; write a per-site identity matrix (palette, type pair, header/footer structure, content model).
- **Days 2–4** — diverge structurally: different header/footer skeletons, class naming and theme metadata, a varied plugin mix, ideally a different parent on some sites.
- **Day 5** — presentation, rewritten boilerplate, unique imagery.
- **Day 6** — infrastructure: spread hosting/IPs, separate Analytics and Search Console.
- **Day 7** — re-run the fingerprint audit and diff the sites against each other.

**First vs last.** First: the audit and the high-correlation technical signals (theme / plugin / analytics / hosting). Last: visual polish and microcopy — low-risk and quick, once the structure is sound.

**Risks I'd watch:**

- *SEO dips while restructuring* → stage on clones, keep URLs and redirects stable, watch Search Console.
- *Cosmetic-only changes still cluster* → insist on structural **and** infrastructure divergence.
- *A shared "randomiser" becoming its own footprint* → vary by hand, not procedurally.
- *Chasing detection instead of quality* → build genuinely independent, better sites, not just evasion.

Part 2 is this made concrete: two children off one parent, but different markup, class prefixes, metadata, headers, footers, fonts and ACF groups — see [decisions & notes](06-decisions.en.md).
