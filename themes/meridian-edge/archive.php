<?php
/**
 * Archive template — categories, tags, authors and date archives.
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
			<?php
			the_archive_title( '<h1 class="me-feed__title">', '</h1>' );
			the_archive_description( '<div class="me-feed__desc">', '</div>' );
			?>
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
					'screen_reader_text' => esc_html__( 'Posts navigation', 'meridian-edge' ),
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
