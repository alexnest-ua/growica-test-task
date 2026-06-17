<?php
/**
 * Verdal — reusable template tags shared across the template files.
 *
 * @package Verdal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build a plain-text meta description for the current view.
 *
 * @return string
 */
function verdal_meta_description() {
	if ( is_singular() ) {
		$object = get_queried_object();
		$text   = has_excerpt( $object ) ? get_the_excerpt( $object ) : wp_strip_all_tags( $object->post_content );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$text = term_description();
	} else {
		$text = get_bloginfo( 'description' );
	}

	return trim( wp_trim_words( wp_strip_all_tags( (string) $text ), 30, '…' ) );
}

/**
 * Canonical URL for the current request — query-string free, and correct on
 * installs that live in a subdirectory.
 *
 * @return string
 */
function verdal_canonical_url() {
	if ( is_singular() ) {
		return (string) get_permalink();
	}

	if ( is_front_page() ) {
		return home_url( '/' );
	}

	return home_url( user_trailingslashit( $GLOBALS['wp']->request ?? '' ) );
}

/**
 * Output the post meta line: published date + primary category.
 */
function verdal_entry_meta() {
	$date = sprintf(
		'<time class="verdal-meta__date" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);

	$categories = get_the_category_list( esc_html__( ', ', 'verdal' ) );
	?>
	<p class="verdal-meta">
		<?php
		echo $date; // Built from escaped parts above.

		if ( $categories ) {
			printf(
				' <span class="verdal-meta__sep" aria-hidden="true">·</span> <span class="verdal-meta__cats">%s</span>',
				wp_kses_post( $categories )
			);
		}
		?>
	</p>
	<?php
}

/**
 * Render the ACF "Page Intro" block beneath the page title.
 *
 * Called directly from page.php so it does not depend on a GeneratePress hook
 * firing. All output is escaped at the point of output.
 */
function verdal_page_intro() {
	if ( ! function_exists( 'get_field' ) ) {
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
