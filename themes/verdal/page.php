<?php
/**
 * Page template.
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
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'verdal-page' ); ?>>
				<?php
				get_template_part( 'template-parts/entry-header' );
				verdal_page_intro();
				?>
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
			</article>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</main>
</div>
<?php
get_footer();
