<?php
/**
 * Singular body — featured image, content and the tag list.
 *
 * @package Meridian_Edge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'me-article' ); ?>>
	<?php get_template_part( 'template-parts/entry-header' ); ?>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="me-article__media">
			<?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?>
		</figure>
	<?php endif; ?>

	<div class="entry-content me-prose">
		<?php
		the_content();
		wp_link_pages(
			array(
				'before' => '<nav class="me-pagelinks" aria-label="' . esc_attr__( 'Page', 'meridian-edge' ) . '">',
				'after'  => '</nav>',
			)
		);
		?>
	</div>

	<?php
	$me_tags = get_the_tag_list( '<li>', '</li><li>', '</li>' );
	if ( $me_tags ) :
		?>
		<footer class="me-article__foot">
			<ul class="me-tags"><?php echo wp_kses_post( $me_tags ); ?></ul>
		</footer>
	<?php endif; ?>
</article>
