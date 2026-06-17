/*
 * Verdal — progressive enhancement (source).
 * Built to js/theme.min.js, which is what the theme enqueues.
 *
 * Gently reveals cards and the page intro as they scroll into view. Content is
 * fully visible without JS; this only adds motion for users who allow it.
 */
( function () {
	'use strict';

	var prefersReduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	if ( prefersReduced || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	var targets = document.querySelectorAll( '.verdal-card, .page-intro' );

	if ( ! targets.length ) {
		return;
	}

	var observer = new IntersectionObserver(
		function ( entries, obs ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-visible' );
					obs.unobserve( entry.target );
				}
			} );
		},
		{ rootMargin: '0px 0px -10% 0px', threshold: 0.1 }
	);

	targets.forEach( function ( el ) {
		// Leave anything already in view untouched — no opacity flash, protects LCP.
		if ( el.getBoundingClientRect().top < window.innerHeight ) {
			return;
		}
		el.classList.add( 'is-reveal' );
		observer.observe( el );
	} );
}() );
