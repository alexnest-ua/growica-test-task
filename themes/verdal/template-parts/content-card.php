<?php
/**
 * Post card used by the blog, archives and search results.
 *
 * @package Verdal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'verdal-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="verdal-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'verdal-card', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>

	<div class="verdal-card__body">
		<?php verdal_entry_meta(); ?>

		<h2 class="verdal-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<p class="verdal-card__excerpt">
			<?php echo esc_html( wp_trim_words( get_the_excerpt(), 24, '…' ) ); ?>
		</p>

		<a class="verdal-card__more" href="<?php the_permalink(); ?>">
			<?php esc_html_e( 'Read more', 'verdal' ); ?> <span aria-hidden="true">→</span>
			<span class="screen-reader-text"><?php the_title(); ?></span>
		</a>
	</div>
</article>
