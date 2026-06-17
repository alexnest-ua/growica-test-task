<?php
/**
 * Empty-state partial for loops that return nothing.
 *
 * @package Meridian_Edge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="me-empty">
	<h2 class="me-empty__title"><?php esc_html_e( 'No results found', 'meridian-edge' ); ?></h2>
	<p class="me-empty__text"><?php esc_html_e( 'There is nothing to show here. Try a different search term below.', 'meridian-edge' ); ?></p>
	<?php get_search_form(); ?>
</section>
