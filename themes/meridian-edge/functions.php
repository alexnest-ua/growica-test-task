<?php
/**
 * Meridian Edge child theme functions.
 *
 * A sharp, high-contrast GeneratePress child theme for product and engineering
 * sites. The split header, dark four-column footer and post call-to-action
 * banner are all defined in code so the theme rebuilds from the repository
 * alone, with nothing relying on Customizer values held in the database.
 *
 * @package Meridian_Edge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'MERIDIAN_EDGE_VERSION', '2.1.0' );

/**
 * Theme setup: translations + a footer menu location.
 */
function meridian_edge_setup() {
	load_child_theme_textdomain( 'meridian-edge', get_stylesheet_directory() . '/languages' );

	register_nav_menus(
		array(
			'footer-menu' => __( 'Footer Menu', 'meridian-edge' ),
		)
	);
}
add_action( 'after_setup_theme', 'meridian_edge_setup' );

/**
 * Enqueue fonts and the child stylesheet.
 *
 * The child stylesheet depends on GeneratePress' "generate-style" handle so it
 * always loads after the parent CSS.
 */
function meridian_edge_enqueue_assets() {
	wp_enqueue_style(
		'meridian-edge-fonts',
		'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap',
		array(),
		MERIDIAN_EDGE_VERSION
	);

	wp_enqueue_style(
		'meridian-edge-style',
		get_stylesheet_uri(),
		array( 'generate-style' ),
		MERIDIAN_EDGE_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'meridian_edge_enqueue_assets', 20 );

/**
 * Preconnect to the Google Fonts hosts.
 *
 * @param array  $hints    URLs / hint arrays to print.
 * @param string $relation The relation type being filtered.
 * @return array
 */
function meridian_edge_resource_hints( $hints, $relation ) {
	if ( 'preconnect' === $relation ) {
		$hints[] = 'https://fonts.googleapis.com';
		$hints[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $hints;
}
add_filter( 'wp_resource_hints', 'meridian_edge_resource_hints', 10, 2 );

/**
 * Float the primary navigation to the right, beside a left-aligned logo.
 *
 * @return string
 */
function meridian_edge_navigation_location() {
	return 'nav-float-right';
}
add_filter( 'generate_navigation_location', 'meridian_edge_navigation_location' );

/**
 * Register the theme's acf-json directory as an ACF load point so the field
 * group ships in the repository rather than living only in the database.
 *
 * @param array $paths Existing JSON load paths.
 * @return array
 */
function meridian_edge_acf_json_load_point( $paths ) {
	$paths[] = get_stylesheet_directory() . '/acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'meridian_edge_acf_json_load_point' );

/**
 * Render the ACF call-to-action banner after the content on single posts.
 *
 * Every value is escaped at output. The "variant" select switches between a
 * dark solid panel and a light outlined panel.
 */
function meridian_edge_render_cta() {
	if ( ! is_singular( 'post' ) || ! function_exists( 'get_field' ) ) {
		return;
	}

	if ( ! get_field( 'cta_enabled' ) ) {
		return;
	}

	$kicker  = get_field( 'cta_kicker' );
	$heading = get_field( 'cta_heading' );
	$text    = get_field( 'cta_text' );
	$button  = get_field( 'cta_button' );
	$variant = get_field( 'cta_variant' );
	$variant = in_array( $variant, array( 'solid', 'outline' ), true ) ? $variant : 'outline';

	if ( ! $heading && ! $text && empty( $button ) ) {
		return;
	}
	?>
	<aside class="me-cta me-cta--<?php echo esc_attr( $variant ); ?>" aria-label="<?php esc_attr_e( 'Call to action', 'meridian-edge' ); ?>">
		<div class="me-cta__body">
			<?php if ( $kicker ) : ?>
				<p class="me-cta__kicker"><?php echo esc_html( $kicker ); ?></p>
			<?php endif; ?>

			<?php if ( $heading ) : ?>
				<h2 class="me-cta__heading"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>

			<?php if ( $text ) : ?>
				<p class="me-cta__text"><?php echo esc_html( $text ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $button['url'] ) ) : ?>
			<p class="me-cta__action">
				<a class="me-button" href="<?php echo esc_url( $button['url'] ); ?>"<?php echo ! empty( $button['target'] ) ? ' target="' . esc_attr( $button['target'] ) . '" rel="noopener"' : ''; ?>>
					<?php echo esc_html( ! empty( $button['title'] ) ? $button['title'] : __( 'Get started', 'meridian-edge' ) ); ?>
				</a>
			</p>
		<?php endif; ?>
	</aside>
	<?php
}
add_action( 'generate_after_content', 'meridian_edge_render_cta' );

/**
 * Replace GeneratePress' default footer with a dark four-column layout and a
 * split bottom bar (copyright left, back-to-top right).
 */
function meridian_edge_footer() {
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
remove_action( 'generate_footer', 'generate_construct_footer_widgets', 5 );
remove_action( 'generate_footer', 'generate_construct_footer', 10 );
add_action( 'generate_footer', 'meridian_edge_footer' );
