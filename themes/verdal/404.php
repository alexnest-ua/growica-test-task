<?php
/**
 * 404 (not found) template.
 *
 * @package Verdal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main verdal-main verdal-404">
		<header>
			<p class="verdal-404__code" aria-hidden="true">404</p>
			<h1 class="verdal-404__title"><?php esc_html_e( 'This page wandered off', 'verdal' ); ?></h1>
		</header>

		<p class="verdal-404__text">
			<?php esc_html_e( 'The page you were looking for is not here. Try a search, or head back to the start.', 'verdal' ); ?>
		</p>

		<?php get_search_form(); ?>

		<p class="verdal-404__home">
			<a class="verdal-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'verdal' ); ?></a>
		</p>

		<?php
		$verdal_recent = new WP_Query(
			array(
				'posts_per_page'      => 4,
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			)
		);
		if ( $verdal_recent->have_posts() ) :
			?>
			<section class="verdal-404__recent" aria-label="<?php esc_attr_e( 'Recent posts', 'verdal' ); ?>">
				<h2 class="verdal-404__recent-title"><?php esc_html_e( 'Recent journal entries', 'verdal' ); ?></h2>
				<ul class="verdal-404__list">
					<?php
					while ( $verdal_recent->have_posts() ) :
						$verdal_recent->the_post();
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
