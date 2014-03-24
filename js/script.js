( function( $ ) {
	$( document ).ready(function() {
		// Enter only digits in the width and height fields
		$( '#gglmps_basic_width, #gglmps_basic_height' ).numericOnly();
	});
})( jQuery );

// Plugin for enter only digits
( function( $ ) {
	jQuery.fn.numericOnly = function() {
		return this.each( function() {
			$( this ).on( 'keydown', function( e ) {
				var key = e.charCode || e.keyCode || 0;
				return (
					key == 8 || 
					key == 9 ||
					key == 46 ||
					( key >= 37 && key <= 40 ) ||
					( key >= 48 && key <= 57 ) ||
					( key >= 96 && key <= 105 )
				);
			});
		});
	};
})( jQuery );