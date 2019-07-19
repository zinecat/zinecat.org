/*!
 * Font Awesome Icon Picker
 * https://itsjavi.com/fontawesome-iconpicker/
 *
 * Originally written by (c) 2016 Javi Aguilar
 * Licensed under the MIT License
 * https://github.com/itsjavi/fontawesome-iconpicker/blob/master/LICENSE
 *
 */

( function( $ ) {

	$( function() {
		$( '.icp' ).iconpicker().on( 'iconpickerUpdated', function() {
			$( this ).trigger( 'change' );
		} );
	} );

} )( jQuery );