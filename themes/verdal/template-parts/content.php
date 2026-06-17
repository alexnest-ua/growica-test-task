<?php
/**
 * Single post / default singular content.
 *
 * @package Verdal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'verdal-article' ); ?>>
	<?php get_template_part( 'template-parts/entry-header' ); ?>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="verdal-article__media">
			<?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?>
		</figure>
	<?php endif; ?>

	<div class="entry-content verdal-prose">
		<?php
		the_content();
		wp_link_pages(
			array(
				'before' => '<nav class="verdal-pagelinks" aria-label="' . esc_attr__( 'Page', 'verdal' ) . '">',
				'after'  => '</nav>',
			)
		);
		?>
	</div>

	<?php
	$tag_list = get_the_tag_list( '<li>', '</li><li>', '</li>' );
	if ( $tag_list ) :
		?>
		<footer class="verdal-article__footer">
			<ul class="verdal-tags"><?php echo wp_kses_post( $tag_list ); ?></ul>
		</footer>
	<?php endif; ?>
</article>
