<?php
/**
 * List card — used by the blog index, archives and search.
 *
 * Built as a "spec sheet" row: a thumbnail, then a stacked block of byline,
 * title and excerpt with the read-more pinned to the foot.
 *
 * @package Meridian_Edge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'me-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="me-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'me-card', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>

	<div class="me-card__body">
		<?php me_entry_meta(); ?>

		<h2 class="me-card__title">
			<a class="me-card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<p class="me-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ); ?></p>

		<a class="me-card__more" href="<?php the_permalink(); ?>">
			<span aria-hidden="true">→</span> <?php esc_html_e( 'Read article', 'meridian-edge' ); ?>
			<span class="screen-reader-text"><?php the_title(); ?></span>
		</a>
	</div>
</article>
