<?php
/**
 * Static page template.
 *
 * @package Meridian_Edge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$me_is_front = is_front_page();

// The front-page hero is rendered above #content from generate_after_header
// (see functions.php) so it spans full width; here we only skip the duplicate
// entry-header on the front page.
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main me-main">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'me-page' ); ?>>
				<?php
				if ( ! $me_is_front ) {
					get_template_part( 'template-parts/entry-header' );
				}
				?>

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
