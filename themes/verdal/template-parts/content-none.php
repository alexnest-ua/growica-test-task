<?php
/**
 * Shown when a loop has no results (blog, archive, search).
 *
 * @package Verdal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="verdal-none">
	<h2 class="verdal-none__title"><?php esc_html_e( 'Nothing here yet', 'verdal' ); ?></h2>
	<p class="verdal-none__text"><?php esc_html_e( 'We could not find anything to match. Try a search instead.', 'verdal' ); ?></p>
	<?php get_search_form(); ?>
</section>
