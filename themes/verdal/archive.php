<?php
/**
 * Archive template (categories, tags, dates, authors).
 *
 * @package Verdal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main verdal-main verdal-archive">
		<header class="verdal-archive__header">
			<?php
			the_archive_title( '<h1 class="verdal-archive__title">', '</h1>' );
			the_archive_description( '<div class="verdal-archive__desc">', '</div>' );
			?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="verdal-cards">
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
					'screen_reader_text' => esc_html__( 'Posts navigation', 'verdal' ),
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
