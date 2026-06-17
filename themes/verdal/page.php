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

while ( have_posts() ) :
	the_post();

	/*
	 * On the front page — or any page given ACF Page Intro content — lead with
	 * the full editorial hero, which owns the single <h1>. Everywhere else keep
	 * the standard entry header + (optional) intro block. This keeps exactly one
	 * <h1> per page and avoids a duplicate, smaller title above the hero.
	 */
	$verdal_use_hero = is_front_page() || verdal_has_page_intro();

	if ( $verdal_use_hero ) {
		verdal_page_hero();
	}
	?>
	<div id="primary" class="content-area">
		<main id="main" class="site-main verdal-main">
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'verdal-page' ); ?>>
				<?php
				if ( ! $verdal_use_hero ) {
					get_template_part( 'template-parts/entry-header' );
					verdal_page_intro();
				}
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
			?>
		</main>
	</div>
	<?php
endwhile;

get_footer();
