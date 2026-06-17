<?php
/**
 * Entry header — title, with the byline strip on single posts.
 *
 * @package Meridian_Edge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header class="me-entry-head">
	<?php
	if ( 'post' === get_post_type() ) {
		me_entry_meta();
	}

	the_title( '<h1 class="me-entry-title">', '</h1>' );
	?>
</header>
