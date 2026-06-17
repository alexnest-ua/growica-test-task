<?php
/**
 * Verdal child theme functions.
 *
 * A calm, editorial GeneratePress child theme. Structural choices (centred
 * masthead, three-column footer, page-intro block) live here in code so the
 * theme is fully reproducible from the repository — none of it depends on
 * Customizer settings stored in the database.
 *
 * @package Verdal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'VERDAL_VERSION', '1.0.3' );

/**
 * Theme setup: i18n + a footer menu location.
 */
function verdal_setup() {
	load_child_theme_textdomain( 'verdal', get_stylesheet_directory() . '/languages' );

	register_nav_menus(
		array(
			'footer-menu' => __( 'Footer Menu', 'verdal' ),
		)
	);
}
add_action( 'after_setup_theme', 'verdal_setup' );

/**
 * Enqueue fonts and the child stylesheet.
 *
 * GeneratePress enqueues its own CSS under the handle "generate-style"
 * (from assets/css/, not its style.css), so the child stylesheet simply
 * declares that handle as a dependency to load after it.
 */
function verdal_enqueue_assets() {
	wp_enqueue_style(
		'verdal-fonts',
		'https://fonts.googleapis.com/css2?family=Lora:wght@500;600;700&family=Mulish:wght@400;500;600;700&display=swap',
		array(),
		VERDAL_VERSION
	);

	wp_enqueue_style(
		'verdal-style',
		get_stylesheet_uri(),
		array( 'generate-style' ),
		VERDAL_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'verdal_enqueue_assets', 20 );

/**
 * Preconnect to the Google Fonts hosts.
 *
 * @param array  $hints    URLs / hint arrays to print.
 * @param string $relation The relation type being filtered.
 * @return array
 */
function verdal_resource_hints( $hints, $relation ) {
	if ( 'preconnect' === $relation ) {
		$hints[] = 'https://fonts.googleapis.com';
		$hints[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $hints;
}
add_filter( 'wp_resource_hints', 'verdal_resource_hints', 10, 2 );

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
 * Load ACF field groups from the theme's acf-json directory so the custom
 * field group is reproducible from the repository (not click-only in the DB).
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
 * Render the ACF "Page Intro" block beneath the page title.
 *
 * All output is escaped at the point of output.
 */
function verdal_render_page_intro() {
	if ( ! is_page() || ! function_exists( 'get_field' ) ) {
		return;
	}

	$eyebrow = get_field( 'intro_eyebrow' );
	$lead    = get_field( 'intro_lead' );
	$cta     = get_field( 'intro_cta' );
	$image   = get_field( 'intro_image' );
	$boxed   = (bool) get_field( 'intro_boxed' );

	if ( ! $eyebrow && ! $lead && empty( $cta ) && empty( $image ) ) {
		return;
	}

	$classes = $boxed ? 'page-intro page-intro--boxed' : 'page-intro';
	?>
	<section class="<?php echo esc_attr( $classes ); ?>" aria-label="<?php esc_attr_e( 'Introduction', 'verdal' ); ?>">
		<?php if ( $eyebrow ) : ?>
			<p class="page-intro__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $lead ) : ?>
			<p class="page-intro__lead"><?php echo esc_html( $lead ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $cta['url'] ) ) : ?>
			<p class="page-intro__cta">
				<a class="verdal-button" href="<?php echo esc_url( $cta['url'] ); ?>"<?php echo ! empty( $cta['target'] ) ? ' target="' . esc_attr( $cta['target'] ) . '" rel="noopener"' : ''; ?>>
					<?php echo esc_html( ! empty( $cta['title'] ) ? $cta['title'] : __( 'Learn more', 'verdal' ) ); ?>
				</a>
			</p>
		<?php endif; ?>

		<?php if ( ! empty( $image['url'] ) ) : ?>
			<figure class="page-intro__media">
				<img
					src="<?php echo esc_url( $image['url'] ); ?>"
					alt="<?php echo esc_attr( $image['alt'] ?? '' ); ?>"
					<?php if ( ! empty( $image['width'] ) ) : ?>width="<?php echo esc_attr( $image['width'] ); ?>"<?php endif; ?>
					<?php if ( ! empty( $image['height'] ) ) : ?>height="<?php echo esc_attr( $image['height'] ); ?>"<?php endif; ?>
					loading="lazy"
					decoding="async"
				/>
			</figure>
		<?php endif; ?>
	</section>
	<?php
}
add_action( 'generate_after_entry_header', 'verdal_render_page_intro' );

/**
 * Replace GeneratePress' default footer with a three-column layout and a
 * centred copyright bar.
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
 * Remove GeneratePress' default footer widgets and site-info credit so only the
 * custom footer renders.
 *
 * Deferred to after_setup_theme: the parent registers these callbacks while its
 * functions.php loads, which happens *after* the child's, so removing them at
 * child parse time would be a no-op.
 */
function verdal_replace_footer_hooks() {
	remove_action( 'generate_footer', 'generate_construct_footer_widgets', 5 );
	remove_action( 'generate_footer', 'generate_construct_footer', 10 );
}
add_action( 'after_setup_theme', 'verdal_replace_footer_hooks' );
