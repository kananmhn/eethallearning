/**
 * Live-preview handlers for the Customizer.
 */
( function ( $ ) {
	'use strict';

	wp.customize( 'blogname', function ( value ) {
		value.bind( function ( to ) {
			$( '.site-title a, .logo span' ).text( to );
		} );
	} );

	wp.customize( 'blogdescription', function ( value ) {
		value.bind( function ( to ) {
			$( '.site-description' ).text( to );
		} );
	} );
}( jQuery ) );
