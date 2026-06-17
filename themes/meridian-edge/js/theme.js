/*
 * meridian edge — progressive enhancement (source).
 * compiled to js/theme.min.js, which is the file the theme enqueues.
 *
 * condenses the sticky header once the page scrolls past a threshold. the
 * header works as a normal static bar without js; this only opts the sticky
 * + shrink behaviour in, and stays off when the visitor prefers reduced motion.
 */
( () => {
	'use strict';

	const root = document.documentElement;
	const header = document.querySelector( '.site-header' );

	if ( ! header ) {
		return;
	}

	const reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	if ( reduced ) {
		return;
	}

	// opt the sticky styles in only when js is running.
	root.classList.add( 'me-sticky' );

	const threshold = 80;
	let shrunk = false;
	let ticking = false;

	const sync = () => {
		const past = window.scrollY > threshold;

		if ( past !== shrunk ) {
			shrunk = past;
			root.classList.toggle( 'me-shrink', shrunk );
		}

		ticking = false;
	};

	const onScroll = () => {
		if ( ! ticking ) {
			ticking = true;
			window.requestAnimationFrame( sync );
		}
	};

	window.addEventListener( 'scroll', onScroll, { passive: true } );
	sync();
} )();
