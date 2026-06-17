<?php
/**
 * 404 — not found template.
 *
 * @package Meridian_Edge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main me-main me-404">
		<header class="me-404__head">
			<p class="me-404__code" aria-hidden="true">404</p>
			<h1 class="me-404__title"><?php esc_html_e( 'Page not found', 'meridian-edge' ); ?></h1>
		</header>

		<p class="me-404__text">
			<?php esc_html_e( 'That URL did not resolve. Search for what you need, or jump back to the homepage.', 'meridian-edge' ); ?>
		</p>

		<?php get_search_form(); ?>

		<p class="me-404__home">
			<a class="me-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return home', 'meridian-edge' ); ?></a>
		</p>

		<?php
		$me_recent = new WP_Query(
			array(
				'posts_per_page'      => 4,
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			)
		);
		if ( $me_recent->have_posts() ) :
			?>
			<section class="me-404__recent" aria-label="<?php esc_attr_e( 'Recent posts', 'meridian-edge' ); ?>">
				<h2 class="me-404__recent-title"><?php esc_html_e( 'Latest from the journal', 'meridian-edge' ); ?></h2>
				<ul class="me-404__list">
					<?php
					while ( $me_recent->have_posts() ) :
						$me_recent->the_post();
						?>
						<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
						<?php
					endwhile;
					?>
				</ul>
			</section>
			<?php
			wp_reset_postdata();
		endif;
		?>
	</main>
</div>
<?php
get_footer();
