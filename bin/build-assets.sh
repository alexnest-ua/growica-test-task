#!/usr/bin/env bash
#
# Build the self-contained, de-footprinted theme asset bundles.
#
# Each child theme is served as exactly ONE stylesheet and ONE script that fuse
# together (a) the GeneratePress framework CSS, (b) the theme's dynamic preset
# snapshot, (c) the parent menu + a11y scripts and (d) the child's own CSS/JS.
# Because the framework now ships inside the child's bundle, the rendered page
# references no /wp-content/themes/generatepress/ asset, no shared "generate-*"
# handle and no shared "?ver=3.6.1" — and each site's bundle is unique in path,
# name, version and content.
#
# Vendored parent inputs live in bin/vendor/ (GPL, captured from GeneratePress
# 3.6.1) and sit OUTSIDE every theme directory, so they are never web-served on
# their own. They carry no comment banner, so no "generatepress" token leaks
# into the concatenated bundle. The menu config object is emitted separately as
# an inline-before script from functions.php under a per-theme variable name.
#
# Bundle order is cascade/runtime significant:
#   CSS: framework  ->  dynamic preset snapshot  ->  comments component  ->  child
#   JS : menu (config var renamed)  ->  a11y  ->  child enhancement
#
# The comments component sheet is folded in because the parent-asset mask
# (functions.php) also strips GeneratePress' on-demand "generate-comments" sheet,
# which it serves on singular views with open comments; the themes use no other
# on-demand GP component (no sidebars, no Customizer-gated nav extras), so only
# this one is re-bundled.
#
# Usage: bin/build-assets.sh        (rebuilds both themes)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
VENDOR="$ROOT/bin/vendor"
CSSO="${CSSO:-$HOME/.bun/bin/csso}"
TERSER="${TERSER:-$HOME/.bun/bin/terser}"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

build() {
	local theme="$1" menu_var="$2"
	local dir="$ROOT/themes/$theme"

	# CSS bundle: framework + dynamic preset snapshot + minified child CSS.
	"$CSSO" "$dir/css/main.css" --no-restructure -o "$TMP/child.css"
	{
		cat "$VENDOR/gp-framework.min.css"; printf '\n'
		cat "$VENDOR/$theme-dynamic.css";   printf '\n'
		cat "$VENDOR/gp-comments.min.css";  printf '\n'
		cat "$TMP/child.css"
	} > "$dir/css/main.min.css"

	# JS bundle: parent menu (config var renamed) + a11y + minified child JS.
	sed "s/generatepressMenu/$menu_var/g" "$VENDOR/gp-menu.min.js" > "$TMP/menu.js"
	"$TERSER" "$dir/js/theme.js" --compress --mangle -o "$TMP/child.js"
	{
		cat "$TMP/menu.js";        printf ';\n'
		cat "$VENDOR/a11y.min.js"; printf ';\n'
		cat "$TMP/child.js"
	} > "$dir/js/theme.min.js"

	printf '  %-14s css %6d B   js %6d B\n' "$theme" \
		"$(wc -c < "$dir/css/main.min.css")" "$(wc -c < "$dir/js/theme.min.js")"
}

echo "Building de-footprinted bundles:"
build verdal        vdMenuCfg
build meridian-edge meMenuCfg
echo "Done."
