<?php
/**
 * Blog index / fallback template.
 *
 * @package Verdal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$verdal_blog_title = ( is_home() && get_option( 'page_for_posts' ) )
	? get_the_title( (int) get_option( 'page_for_posts' ) )
	: __( 'Journal', 'verdal' );
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main verdal-main verdal-archive">
		<header class="verdal-archive__header">
			<h1 class="verdal-archive__title"><?php echo esc_html( $verdal_blog_title ); ?></h1>
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
