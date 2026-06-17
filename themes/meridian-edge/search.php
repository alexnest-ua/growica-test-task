<?php
/**
 * Search results template.
 *
 * @package Meridian_Edge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main me-main me-feed">
		<header class="me-feed__head">
			<h1 class="me-feed__title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Results for “%s”', 'meridian-edge' ),
					'<span class="me-feed__query">' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="me-cards">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content-card' );
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination(
				array(
					'mid_size'           => 1,
					'screen_reader_text' => esc_html__( 'Search results navigation', 'meridian-edge' ),
				)
			);
			?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content-none' ); ?>
		<?php endif; ?>
	</main>
</div>
<?php
get_footer();
