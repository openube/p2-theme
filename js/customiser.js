/**
 * p2 theme admin scripts for wordpress theme customiser
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @package WordPress
 */
( function( $ ) {

	// Update site title
	wp.customize( 'blogname', function( value ) {
		value.bind( function( newval ) {
			$( '#site-header a' ).text( newval );
		} );
	} );
	
	// Update title color
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( newval ) {
			$('#site-header a').css('color', newval );
		} );
	} );

	// Update background color
	wp.customize( 'background_color', function( value ) {
		value.bind( function( newval ) {
			$('body').css('background-color', newval );
		} );
	} );
	
	// Update text color
	wp.customize( 'p2_options[text_colour]', function( value ) {
		value.bind( function( newval ) {
			$('body').css('color', newval );
		} );
	} );

	// Update link color
	wp.customize( 'p2_options[link_colour]', function( value ) {
		value.bind( function( newval ) {
			$('a').css('color', newval );
		} );
	} );

	// Update headings colour
	wp.customize( 'p2_options[heading_colour]', function( value ) {
		value.bind( function( newval ) {
			console.log(newval);
			$('h2,h3,h4,h5,h6').css('color', newval );
		} );
	} );

} )( jQuery );