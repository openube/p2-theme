/**
 * p2 theme admin scripts for wordpress theme customiser
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @package WordPress
 */
/*
( function( $ ) {

	// Update the site title in real time...
/*	wp.customize( 'blogname', function( value ) {
		value.bind( function( newval ) {
			$( '#site-title a' ).html( newval );
		} );
	} );
	
	//Update the site description in real time...
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( newval ) {
			$( '.site-description' ).html( newval );
		} );
	} );

	//Update site title color in real time...
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( newval ) {
			$('#site-title a').css('color', newval );
		} );
	} );

	//Update site background color...
	wp.customize( 'background_color', function( value ) {
		value.bind( function( newval ) {
			$('body').css('background-color', newval );
		} );
	} );
	
	//Update site title color in real time...
	wp.customize( 'mytheme_options[link_textcolor]', function( value ) {
		value.bind( function( newval ) {
			$('a').css('color', newval );
		} );
	} );
	
} )( jQuery );

wp.customize( 'YOUR_SETTING_ID', function( value ) {
	value.bind( function( newval ) {
		//Do stuff (newval variable contains your "new" setting data)
	} );
} );

*/