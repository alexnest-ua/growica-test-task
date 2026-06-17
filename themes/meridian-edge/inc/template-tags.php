<?php
/**
 * Meridian Edge — shared render helpers pulled into the template files.
 *
 * @package Meridian_Edge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compress the current view into a one-line plain-text summary.
 *
 * Used by both the Twitter Card tags and the JSON-LD description. Falls through
 * post excerpt -> stripped body -> term description -> tagline depending on the
 * query, then clamps the result to a sentence-ish length.
 *
 * @return string
 */
function me_meta_summary() {
	$source = '';

	if ( is_singular() ) {
		$post   = get_queried_object();
		$source = has_excerpt( $post ) ? get_the_excerpt( $post ) : ( $post->post_content ?? '' );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$source = term_description();
	} else {
		$source = get_bloginfo( 'description' );
	}

	$source = wp_strip_all_tags( (string) $source );

	return trim( wp_trim_words( $source, 28, '…' ) );
}

/**
 * Resolve a clean canonical URL for the current view.
 *
 * Built from $wp->request (root-relative, so it is correct on subdirectory
 * installs) and without query args, so tracking parameters never leak into the
 * canonical / og:url.
 *
 * @return string
 */
function me_canonical_url() {
	if ( is_singular() ) {
		return (string) get_permalink();
	}

	if ( is_front_page() ) {
		return home_url( '/' );
	}

	global $wp;

	return home_url( user_trailingslashit( $wp->request ?? '' ) );
}

/**
 * Render the front-page product hero from the page's own fields.
 *
 * Drives entirely off the post object — pill label, the title as the single H1,
 * the excerpt as the subhead and a fixed solid/outline action pair — so it needs
 * no ACF group and no Customizer state. page.php swaps this in for the default
 * entry-header on the front page so the document keeps exactly one H1. The
 * primary action resolves to a passed URL, else the /contact/ page.
 *
 * @param string $cta_url Optional primary destination; falls back to /contact/.
 * @return void
 */
function meridian_edge_page_hero( $cta_url = '' ) {
	$title   = get_the_title();
	$excerpt = get_the_excerpt();
	$primary = $cta_url ? $cta_url : home_url( '/contact/' );
	$journal = home_url( '/journal/' );
	?>
	<section class="me-hero" aria-labelledby="me-hero-title">
		<div class="me-hero__inner">
			<p class="me-hero__eyebrow">
				<span class="me-hero__eyebrow-dot" aria-hidden="true"></span>
				<?php esc_html_e( 'Shipping v2.2 · Built for speed', 'meridian-edge' ); ?>
			</p>

			<h1 id="me-hero-title" class="me-hero__title"><?php echo esc_html( $title ); ?></h1>

			<?php if ( $excerpt ) : ?>
				<p class="me-hero__lede"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>

			<p class="me-hero__actions">
				<a class="me-button me-button--lg" href="<?php echo esc_url( $primary ); ?>">
					<?php esc_html_e( 'Start a project', 'meridian-edge' ); ?>
				</a>
				<a class="me-button me-button--lg me-button--outline" href="<?php echo esc_url( $journal ); ?>">
					<?php esc_html_e( 'Read the journal', 'meridian-edge' ); ?>
				</a>
			</p>
		</div>
	</section>
	<?php
}

/**
 * Print the byline strip for a post: timestamp followed by its categories.
 *
 * @return void
 */
function me_entry_meta() {
	$stamp = sprintf(
		'<time class="me-byline__date" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);

	$cats = get_the_category_list( '<span class="me-byline__dot" aria-hidden="true"></span>' );
	?>
	<p class="me-byline">
		<?php
		echo $stamp; // Assembled from escaped pieces above.

		if ( $cats ) {
			printf(
				'<span class="me-byline__dot" aria-hidden="true"></span><span class="me-byline__cats">%s</span>',
				wp_kses_post( $cats )
			);
		}
		?>
	</p>
	<?php
}

/**
 * Render the ACF call-to-action banner.
 *
 * Invoked straight from single.php after the post body so it never depends on a
 * GeneratePress action firing. The variant slug is clamped to the known set and
 * every field is escaped where it prints.
 *
 * @return void
 */
function me_render_cta() {
	if ( ! function_exists( 'get_field' ) ) {
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

	if ( ! $heading && ! $text && empty( $button['url'] ) ) {
		return;
	}

	$label  = ( is_array( $button ) && ! empty( $button['title'] ) ) ? $button['title'] : __( 'Get started', 'meridian-edge' );
	$target = ( is_array( $button ) && ! empty( $button['target'] ) ) ? $button['target'] : '';
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

		<?php if ( is_array( $button ) && ! empty( $button['url'] ) ) : ?>
			<p class="me-cta__action">
				<a class="me-button" href="<?php echo esc_url( $button['url'] ); ?>"<?php echo $target ? ' target="' . esc_attr( $target ) . '" rel="noopener"' : ''; ?>>
					<?php echo esc_html( $label ); ?>
				</a>
			</p>
		<?php endif; ?>
	</aside>
	<?php
}
