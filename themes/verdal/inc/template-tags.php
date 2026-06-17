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
 * True when the current page has any ACF "Page Intro" content to show.
 *
 * Used by page.php to decide between the full editorial hero and the standard
 * entry header. Safe to call when ACF is inactive (returns false).
 *
 * @return bool
 */
function verdal_has_page_intro() {
	if ( ! function_exists( 'get_field' ) ) {
		return false;
	}

	$cta   = get_field( 'intro_cta' );
	$image = get_field( 'intro_image' );

	return (bool) (
		get_field( 'intro_eyebrow' )
		|| get_field( 'intro_lead' )
		|| ( is_array( $cta ) && ! empty( $cta['url'] ) )
		|| ( is_array( $image ) && ! empty( $image['url'] ) )
	);
}

/**
 * Render the full editorial front-page hero.
 *
 * Outputs a generous, airy hero built from the page title plus the ACF "Page
 * Intro" fields: a tracked uppercase eyebrow, the page title as the single
 * large H1, an elegant lead, a refined CTA, and a large feature image. Because
 * this owns the page <h1>, page.php must skip template-parts/entry-header when
 * it calls this. All output is escaped at the point of output and every ACF
 * array is null-checked.
 */
function verdal_page_hero() {
	$eyebrow = function_exists( 'get_field' ) ? get_field( 'intro_eyebrow' ) : '';
	$lead    = function_exists( 'get_field' ) ? get_field( 'intro_lead' ) : '';
	$cta     = function_exists( 'get_field' ) ? get_field( 'intro_cta' ) : null;
	$image   = function_exists( 'get_field' ) ? get_field( 'intro_image' ) : null;

	$has_image = is_array( $image ) && ! empty( $image['url'] );
	$has_cta   = is_array( $cta ) && ! empty( $cta['url'] );
	?>
	<section class="verdal-hero" aria-label="<?php esc_attr_e( 'Introduction', 'verdal' ); ?>">
		<div class="verdal-hero__inner">
			<div class="verdal-hero__copy">
				<?php if ( $eyebrow ) : ?>
					<p class="verdal-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>

				<?php the_title( '<h1 class="verdal-hero__title">', '</h1>' ); ?>

				<?php if ( $lead ) : ?>
					<p class="verdal-hero__lead"><?php echo esc_html( $lead ); ?></p>
				<?php endif; ?>

				<?php if ( $has_cta ) : ?>
					<p class="verdal-hero__actions">
						<a class="verdal-button" href="<?php echo esc_url( $cta['url'] ); ?>"<?php echo ! empty( $cta['target'] ) ? ' target="' . esc_attr( $cta['target'] ) . '" rel="noopener"' : ''; ?>>
							<?php echo esc_html( ! empty( $cta['title'] ) ? $cta['title'] : __( 'Learn more', 'verdal' ) ); ?>
						</a>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( $has_image ) : ?>
				<figure class="verdal-hero__media">
					<img
						src="<?php echo esc_url( $image['url'] ); ?>"
						alt="<?php echo esc_attr( $image['alt'] ?? '' ); ?>"
						<?php if ( ! empty( $image['width'] ) ) : ?>width="<?php echo esc_attr( $image['width'] ); ?>"<?php endif; ?>
						<?php if ( ! empty( $image['height'] ) ) : ?>height="<?php echo esc_attr( $image['height'] ); ?>"<?php endif; ?>
						fetchpriority="high"
						decoding="async"
					/>
				</figure>
			<?php endif; ?>
		</div>
	</section>
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
