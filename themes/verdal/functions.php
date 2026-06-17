<?php
/**
 * Verdal child theme functions.
 *
 * A calm, editorial GeneratePress child theme. Structure (centred masthead,
 * three-column footer, page intro, the custom template set) lives here in code
 * so the theme rebuilds from the repository alone — nothing depends on
 * Customizer values stored in the database.
 *
 * @package Verdal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'VERDAL_VERSION', '1.2.0' );

require get_stylesheet_directory() . '/inc/template-tags.php';

/**
 * Theme setup: i18n, a footer menu, and the thumbnail size used by the cards.
 */
function verdal_setup() {
	load_child_theme_textdomain( 'verdal', get_stylesheet_directory() . '/languages' );

	register_nav_menus(
		array(
			'footer-menu' => __( 'Footer Menu', 'verdal' ),
		)
	);

	add_image_size( 'verdal-card', 720, 460, true );
}
add_action( 'after_setup_theme', 'verdal_setup' );

/**
 * Enqueue the minified stylesheet and progressive-enhancement script.
 *
 * GeneratePress enqueues its own CSS under the handle "generate-style" (from
 * assets/css/, not its style.css), so the child stylesheet declares that handle
 * as a dependency to load after it. Self-hosted fonts are declared with
 * @font-face inside main.css. Under SCRIPT_DEBUG the unminified sources load.
 */
function verdal_assets() {
	$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$uri = get_stylesheet_directory_uri();

	wp_enqueue_style( 'verdal-main', "{$uri}/css/main{$min}.css", array( 'generate-style' ), VERDAL_VERSION );
	wp_enqueue_script( 'verdal-theme', "{$uri}/js/theme{$min}.js", array(), VERDAL_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'verdal_assets', 20 );

/**
 * Trim WordPress core block CSS that this theme does not use.
 *
 * Core inlines the full block-library sheet plus the theme.json preset sheet
 * (colours, gradients, spacing utilities) into every page head. Verdal styles
 * its own prose with its own design tokens and never references those presets,
 * so the markup is dead weight in the document head — dequeue it. The skip-link
 * and screen-reader-text rules live in main.css, so accessibility never depends
 * on the sheets removed here.
 */
function verdal_trim_block_styles() {
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'global-styles-css-custom-properties' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
}
add_action( 'wp_enqueue_scripts', 'verdal_trim_block_styles', 100 );

/**
 * Preload the two above-the-fold font files (heading + body) to protect LCP/CLS.
 *
 * @param array $preloads Existing preload definitions.
 * @return array
 */
function verdal_preload_fonts( $preloads ) {
	$fonts = get_stylesheet_directory_uri() . '/fonts';
	foreach ( array( 'lora-v37-latin-700.woff2', 'mulish-v18-latin-regular.woff2' ) as $file ) {
		$preloads[] = array(
			'href'        => "{$fonts}/{$file}",
			'as'          => 'font',
			'type'        => 'font/woff2',
			'crossorigin' => 'anonymous',
		);
	}
	return $preloads;
}
add_filter( 'wp_preload_resources', 'verdal_preload_fonts' );

/**
 * Place the primary navigation below the centred masthead.
 *
 * @return string
 */
function verdal_navigation_location() {
	return 'nav-below-header';
}
add_filter( 'generate_navigation_location', 'verdal_navigation_location' );

/**
 * Verdal owns its content layout, so disable GeneratePress' sidebar columns and
 * render a single, centred reading measure instead.
 *
 * @return string
 */
function verdal_layout() {
	return 'no-sidebar';
}
add_filter( 'generate_sidebar_layout', 'verdal_layout' );

/**
 * Render the editorial hero above the content container.
 *
 * GeneratePress makes #content a flex row, so a hero echoed from inside the page
 * template lands beside the content column. Hooking generate_after_header
 * outputs it above #content instead, at full width. The main loop is spun once
 * (then rewound) so the_title() and the ACF fields resolve for the queried page.
 */
function verdal_render_page_hero() {
	if ( ! is_page() || ! have_posts() ) {
		return;
	}

	the_post();

	if ( is_front_page() || verdal_has_page_intro() ) {
		verdal_page_hero();
	}

	rewind_posts();
}
add_action( 'generate_after_header', 'verdal_render_page_hero' );

/**
 * Load ACF field groups from the theme's acf-json directory so the custom field
 * group is reproducible from the repository (not click-only in the DB).
 *
 * @param array $paths Existing JSON load paths.
 * @return array
 */
function verdal_acf_json_load_point( $paths ) {
	$paths[] = get_stylesheet_directory() . '/acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'verdal_acf_json_load_point' );

/**
 * Tidy the document head: drop output the theme does not use (generator/version,
 * shortlink, RSD and WLW discovery links, and the emoji loader).
 */
function verdal_clean_head() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	add_filter( 'the_generator', '__return_empty_string' );
}
add_action( 'init', 'verdal_clean_head' );

/**
 * Lightweight SEO: a meta description plus Open Graph tags.
 *
 * Yields to a dedicated SEO plugin if one is active, to avoid duplicate tags.
 */
function verdal_seo_meta() {
	if ( defined( 'AIOSEO_VERSION' ) || defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) {
		return;
	}

	$description = verdal_meta_description();
	$canonical   = verdal_canonical_url();

	// WordPress prints rel=canonical for singular views itself; supply it elsewhere.
	if ( ! is_singular() ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );
	}

	if ( $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	}

	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( wp_get_document_title() ) );

	if ( $description ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	}

	printf( '<meta property="og:type" content="%s">' . "\n", is_singular() ? 'article' : 'website' );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $canonical ) );

	$og_image = '';
	if ( is_singular() && has_post_thumbnail() ) {
		$og_image = get_the_post_thumbnail_url( null, 'large' );
	}
	if ( ! $og_image ) {
		$og_image = get_stylesheet_directory_uri() . '/assets/og-image.webp';
	}
	printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $og_image ) );
}
add_action( 'wp_head', 'verdal_seo_meta', 1 );

/**
 * Output favicon links, unless the site already defines a Site Icon (which then
 * takes precedence).
 */
function verdal_favicon() {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return;
	}

	$assets = get_stylesheet_directory_uri() . '/assets';
	printf( '<link rel="icon" href="%s/favicon.svg" type="image/svg+xml">' . "\n", esc_url( $assets ) );
	printf( '<link rel="apple-touch-icon" href="%s/apple-touch-icon.png">' . "\n", esc_url( $assets ) );
}
add_action( 'wp_head', 'verdal_favicon' );

/**
 * Replace GeneratePress' default footer with a three-column layout and a centred
 * copyright bar.
 *
 * The remove_action() calls are deferred to after_setup_theme because the parent
 * registers these callbacks while its functions.php loads — which happens after
 * the child's — so removing them at child parse time would be a no-op.
 */
function verdal_footer() {
	?>
	<footer class="verdal-footer" aria-label="<?php esc_attr_e( 'Site footer', 'verdal' ); ?>">
		<div class="grid-container verdal-footer__grid">
			<div class="verdal-footer__col verdal-footer__brand">
				<p class="verdal-footer__title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
				<p class="verdal-footer__blurb"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
			</div>

			<nav class="verdal-footer__col" aria-label="<?php esc_attr_e( 'Footer navigation', 'verdal' ); ?>">
				<h2 class="verdal-footer__heading"><?php esc_html_e( 'Explore', 'verdal' ); ?></h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer-menu',
						'container'      => false,
						'menu_class'     => 'verdal-footer__menu',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>

			<div class="verdal-footer__col">
				<h2 class="verdal-footer__heading"><?php esc_html_e( 'Visit', 'verdal' ); ?></h2>
				<p class="verdal-footer__text">
					<?php esc_html_e( '12 Riverside Walk', 'verdal' ); ?><br />
					<?php esc_html_e( 'Kyiv, Ukraine', 'verdal' ); ?>
				</p>
			</div>
		</div>

		<div class="verdal-footer__bar">
			<p class="verdal-footer__copy">
				<?php
				printf(
					/* translators: 1: current year, 2: site name. */
					esc_html__( '© %1$s %2$s. Made calmly, by hand.', 'verdal' ),
					esc_html( gmdate( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</p>
		</div>
	</footer>
	<?php
}
add_action( 'generate_footer', 'verdal_footer' );

/**
 * Defer removal of GeneratePress' footer widgets + site-info credit (see above).
 */
function verdal_replace_footer_hooks() {
	remove_action( 'generate_footer', 'generate_construct_footer_widgets', 5 );
	remove_action( 'generate_footer', 'generate_construct_footer', 10 );
}
add_action( 'after_setup_theme', 'verdal_replace_footer_hooks' );
