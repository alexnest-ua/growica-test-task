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

Visual polish was deliberately kept modest — the brief explicitly values
functional and structural difference over looking pretty.

## Hooks over template overrides

No parent template files were copied. Header/footer/nav structure is achieved via
GeneratePress hooks + CSS. This keeps each child minimal, avoids drift from the
parent's markup, and preserves the parent's own hooks for plugins. (DRY / YAGNI:
override only what's needed.)

## ACF: Local JSON, free fields only

- **Local JSON** (committed under `acf-json/`, auto-loaded via
  `acf/settings/load_json`) is ACF's recommended reproducible workflow and is
  exactly what the brief asks for ("reproducible from the repo, not click-only").
- Field **keys are readable** (`field_verdal_intro_eyebrow`) rather than random,
  so the JSON is reviewable in a PR.
- Only **free** ACF field types are used (text, textarea, link, image,
  true/false, select) — no Pro-only Repeater/Flexible Content.

## Fonts via Google Fonts CDN

For a hosted demo, the Google Fonts CDN with `display=swap` + `preconnect` is the
pragmatic choice. For production I would **self-host** the woff2 subset (privacy
/ GDPR, one fewer third-party connection, better LCP) — noted as a next step, not
done here to stay in scope.

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

Both are recorded in the git history as a `fix:` commit rather than amended away,
so the verify→fix loop is visible.

## Risks & mitigations (this implementation)

| Risk | Mitigation |
|------|------------|
| Free hosted demos expire | Use InstaWP (more persistent); README notes links may need refreshing; themes are installable from the repo regardless. |
| GP Customizer settings not in repo | All structural choices are in **code**, so the theme rebuilds without any DB-stored Customizer state. |
| Self-signed cert on local dev | Verified over `https://testwp`; production runs valid TLS. |
| Theme name/slug collision across a network | Each site uses unique Theme Name, text domain and prefixes; at scale, generate per-site identities. |

## Other notes

- The assignment brief is **git-ignored** rather than republished — it is a
  company's private interview material and this repo is public.
- `noindex` is applied as a **site setting** (Settings → Reading) on each demo,
  not hardcoded into the theme — indexing is a deployment concern, not a theme
  concern.
- Commits are layered and conventional; git identity is set **locally** to
  `alexnest-ua` so it does not use the machine's default account.
