<?php
/**
 * Single post template.
 *
 * @package Verdal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main verdal-main">
		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', get_post_type() );

			the_post_navigation(
				array(
					'class'      => 'verdal-post-nav',
					'prev_text'  => '<span class="verdal-post-nav__label">' . esc_html__( 'Previous', 'verdal' ) . '</span> <span class="verdal-post-nav__title">%title</span>',
					'next_text'  => '<span class="verdal-post-nav__label">' . esc_html__( 'Next', 'verdal' ) . '</span> <span class="verdal-post-nav__title">%title</span>',
					'aria_label' => esc_attr__( 'Posts', 'verdal' ),
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
