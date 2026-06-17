<?php
/**
 * Meridian Edge — theme bootstrap.
 *
 * Wires up the child theme entirely from code: asset loading, the split header
 * placement, the dark footer, head cleanup and structured-data output. Keeping
 * all of it here means a clean checkout reproduces the site without leaning on
 * any Customizer state saved in the database.
 *
 * @package Meridian_Edge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ME_VERSION', '2.2.0' );

require get_stylesheet_directory() . '/inc/template-tags.php';

/**
 * Register translations, the footer menu slot and the card crop size.
 *
 * @return void
 */
function me_after_setup_theme() {
	load_child_theme_textdomain( 'meridian-edge', get_stylesheet_directory() . '/languages' );

	register_nav_menus(
		array(
			'footer-menu' => __( 'Footer Menu', 'meridian-edge' ),
		)
	);

	add_image_size( 'me-card', 760, 480, true );
}
add_action( 'after_setup_theme', 'me_after_setup_theme' );

/**
 * Enqueue the compiled stylesheet and the deferred enhancement script.
 *
 * The stylesheet is chained behind GeneratePress' "generate-style" handle so it
 * always wins the cascade. SCRIPT_DEBUG swaps the readable sources in for the
 * minified files; fonts are self-hosted through @font-face in main.css.
 *
 * @return void
 */
function me_enqueue_assets() {
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$base   = get_stylesheet_directory_uri();

	wp_enqueue_style( 'meridian-edge', "{$base}/css/main{$suffix}.css", array( 'generate-style' ), ME_VERSION );
	wp_enqueue_script( 'meridian-edge', "{$base}/js/theme{$suffix}.js", array(), ME_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'me_enqueue_assets', 20 );

/**
 * Preload the heading + body faces used above the fold to steady LCP and CLS.
 *
 * @param array $resources Queued preload entries.
 * @return array
 */
function me_preload_fonts( $resources ) {
	$dir = get_stylesheet_directory_uri() . '/fonts';

	$critical = array(
		'space-grotesk-v22-latin-700.woff2',
		'ibm-plex-sans-v23-latin-regular.woff2',
	);

	foreach ( $critical as $face ) {
		$resources[] = array(
			'href'        => "{$dir}/{$face}",
			'as'          => 'font',
			'type'        => 'font/woff2',
			'crossorigin' => 'anonymous',
		);
	}

	return $resources;
}
add_filter( 'wp_preload_resources', 'me_preload_fonts' );

/**
 * Pin the primary menu to the right of the logo as a single header row.
 *
 * @return string
 */
function me_navigation_location() {
	return 'nav-float-right';
}
add_filter( 'generate_navigation_location', 'me_navigation_location' );

/**
 * Drop the GeneratePress sidebar so content runs full measure.
 *
 * @return string
 */
function me_sidebar_layout() {
	return 'no-sidebar';
}
add_filter( 'generate_sidebar_layout', 'me_sidebar_layout' );

/**
 * Add the theme's acf-json folder to ACF's load points so the CTA field group
 * lives in version control rather than only in the database.
 *
 * @param array $paths Registered load paths.
 * @return array
 */
function me_acf_json_load_point( $paths ) {
	$paths[] = get_stylesheet_directory() . '/acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'me_acf_json_load_point' );

/**
 * Remove legacy <head> output the theme does not use.
 *
 * Drops the generator string, shortlink, RSD/WLW discovery links and the emoji
 * loader in a single pass.
 *
 * @return void
 */
function me_trim_head() {
	$head_actions = array(
		'wp_head' => array(
			'wp_generator'         => 10,
			'wp_shortlink_wp_head' => 10,
			'rsd_link'             => 10,
			'wlwmanifest_link'     => 10,
			'print_emoji_detection_script' => 7,
		),
	);

	foreach ( $head_actions['wp_head'] as $callback => $priority ) {
		remove_action( 'wp_head', $callback, $priority );
	}

	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	add_filter( 'the_generator', '__return_empty_string' );
}
add_action( 'init', 'me_trim_head' );

/**
 * Emit Twitter Card meta plus a schema.org JSON-LD node.
 *
 * Single posts describe themselves as an Article; everything else as the
 * WebSite. Bows out when a dedicated SEO plugin owns the head to avoid
 * duplicate tags.
 *
 * @return void
 */
function me_structured_data() {
	if ( defined( 'AIOSEO_VERSION' ) || defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) {
		return;
	}

	$title       = wp_get_document_title();
	$description = me_meta_summary();
	$is_article  = is_singular( 'post' );
	$image       = ( $is_article && has_post_thumbnail() )
		? get_the_post_thumbnail_url( null, 'large' )
		: get_stylesheet_directory_uri() . '/assets/og-image.png';
	$canonical   = me_canonical_url();

	// Core already prints rel=canonical on singular; only add it where it doesn't.
	if ( ! is_singular() ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );
	}
	printf( '<meta name="twitter:card" content="%s">' . "\n", 'summary_large_image' );
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );

	if ( '' !== $description ) {
		printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );
	}

	printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );

	if ( $is_article ) {
		$schema = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'Article',
			'headline'      => $title,
			'datePublished' => get_the_date( DATE_W3C ),
			'dateModified'  => get_the_modified_date( DATE_W3C ),
			'author'        => array(
				'@type' => 'Person',
				'name'  => get_the_author(),
			),
			'mainEntityOfPage' => array(
					'@type' => 'WebPage',
					'@id'   => $canonical,
				),
		);

		if ( '' !== $description ) {
			$schema['description'] = $description;
		}

		if ( '' !== $image ) {
			$schema['image'] = $image;
		}
	} else {
		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'WebSite',
			'name'     => get_bloginfo( 'name' ),
			'url'      => home_url( '/' ),
		);

		if ( '' !== $description ) {
			$schema['description'] = $description;
		}
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP )
	);
}
add_action( 'wp_head', 'me_structured_data', 5 );

/**
 * Point browsers at the theme's icons when the site has no Site Icon of its own.
 */
function me_site_icons() {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return;
	}

	$icons = get_stylesheet_directory_uri() . '/assets';
	printf( '<link rel="icon" href="%s/favicon.svg" type="image/svg+xml">' . "\n", esc_url( $icons ) );
	printf( '<link rel="apple-touch-icon" href="%s/apple-touch-icon.png">' . "\n", esc_url( $icons ) );
}
add_action( 'wp_head', 'me_site_icons' );

/**
 * Print the dark four-column footer with a split utility bar.
 *
 * @return void
 */
function me_footer() {
	?>
	<footer class="me-footer" aria-label="<?php esc_attr_e( 'Site footer', 'meridian-edge' ); ?>">
		<div class="grid-container me-footer__grid">
			<div class="me-footer__brand">
				<p class="me-footer__brand-name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
				<p class="me-footer__brand-blurb"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
			</div>

			<nav class="me-footer__col" aria-label="<?php esc_attr_e( 'Footer navigation', 'meridian-edge' ); ?>">
				<h2 class="me-footer__heading"><?php esc_html_e( 'Navigate', 'meridian-edge' ); ?></h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer-menu',
						'container'      => false,
						'menu_class'     => 'me-footer__list',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>

			<div class="me-footer__col">
				<h2 class="me-footer__heading"><?php esc_html_e( 'Resources', 'meridian-edge' ); ?></h2>
				<ul class="me-footer__list">
					<li><a href="<?php echo esc_url( home_url( '/journal/' ) ); ?>"><?php esc_html_e( 'Journal', 'meridian-edge' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'meridian-edge' ); ?></a></li>
				</ul>
			</div>

			<div class="me-footer__col">
				<h2 class="me-footer__heading"><?php esc_html_e( 'Contact', 'meridian-edge' ); ?></h2>
				<ul class="me-footer__list">
					<li><a href="mailto:hello@meridianedge.dev">hello@meridianedge.dev</a></li>
					<li><?php esc_html_e( 'Remote · UTC+2', 'meridian-edge' ); ?></li>
				</ul>
			</div>
		</div>

		<div class="grid-container me-footer__bar">
			<p class="me-footer__copy">
				<?php
				printf(
					/* translators: 1: current year, 2: site name. */
					esc_html__( '© %1$s %2$s. Built for speed.', 'meridian-edge' ),
					esc_html( gmdate( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</p>
			<a class="me-footer__top" href="#page"><?php esc_html_e( '↑ Back to top', 'meridian-edge' ); ?></a>
		</div>
	</footer>
	<?php
}
add_action( 'generate_footer', 'me_footer' );

/**
 * Unhook the stock GeneratePress footer pieces so only ours renders.
 *
 * The parent theme attaches these callbacks while its own functions.php runs,
 * which is after this child file is parsed — so the removal has to wait until
 * after_setup_theme to land on registered hooks.
 *
 * @return void
 */
function me_unhook_default_footer() {
	remove_action( 'generate_footer', 'generate_construct_footer_widgets', 5 );
	remove_action( 'generate_footer', 'generate_construct_footer', 10 );
}
add_action( 'after_setup_theme', 'me_unhook_default_footer' );
