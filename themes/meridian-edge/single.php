<?php
/**
 * Single post template.
 *
 * @package Meridian_Edge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main me-main">
		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', get_post_type() );

			me_render_cta();

			the_post_navigation(
				array(
					'class'      => 'me-post-nav',
					'prev_text'  => '<span class="me-post-nav__label">' . esc_html__( 'Previous', 'meridian-edge' ) . '</span> <span class="me-post-nav__title">%title</span>',
					'next_text'  => '<span class="me-post-nav__label">' . esc_html__( 'Next', 'meridian-edge' ) . '</span> <span class="me-post-nav__title">%title</span>',
					'aria_label' => esc_attr__( 'Posts', 'meridian-edge' ),
				)
			);

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</main>
</div>
<?php
get_footer();
