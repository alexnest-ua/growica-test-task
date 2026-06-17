<?php
/**
 * Reusable entry header: title, plus post meta on single posts.
 *
 * @package Verdal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header class="verdal-entry-header">
	<?php the_title( '<h1 class="verdal-entry-title">', '</h1>' ); ?>
	<?php
	if ( 'post' === get_post_type() ) {
		verdal_entry_meta();
	}
	?>
</header>
