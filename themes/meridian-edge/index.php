<?php
/**
 * Blog index / catch-all template.
 *
 * @package Meridian_Edge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$me_posts_page = (int) get_option( 'page_for_posts' );
$me_feed_title = ( is_home() && $me_posts_page )
	? get_the_title( $me_posts_page )
	: __( 'Journal', 'meridian-edge' );
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main me-main me-feed">
		<header class="me-feed__head">
			<h1 class="me-feed__title"><?php echo esc_html( $me_feed_title ); ?></h1>
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
