( function( $ ) {
	$( document ).ready(function() {
		/* Check availability Map View 45° */
		if ( $( '#gglmps_basic_map_type' ).find( 'option:selected' ).val() == 'roadmap' || $( '#gglmps_basic_map_type' ).find( 'option:selected' ).val() == 'terrain' ) {
			$( '#gglmps_basic_tilt45' ).attr( 'disabled', true );
			$( '#gglmps_control_rotate' ).attr( 'disabled', true );
		}
		
		/* Change map type in the preview map and check availability Map View 45° when changed map type */
		$( '#gglmps_basic_map_type' ).on( 'change', function() {
			if ( $( this ).find( 'option:selected' ).val() == 'satellite' || $( this ).find( 'option:selected' ).val() == 'hybrid' ) {
				$( '#gglmps_basic_tilt45' ).removeAttr( 'disabled' );
				if ( $( '#gglmps_basic_tilt45' ).is( ':checked' ) ) {
					$( '#gglmps_control_rotate' ).removeAttr( 'disabled' );
				} else {
					$( '#gglmps_control_rotate' ).attr( 'disabled', true );
				}	
			} else {
				$( '#gglmps_basic_tilt45' ).attr( 'disabled', true );
				$( '#gglmps_control_rotate' ).attr( 'disabled', true );
			}
		});

		/* Check availability of Rotate Map control */
		if ( $( '#gglmps_basic_tilt45' ).is(':enabled') && $( '#gglmps_basic_tilt45' ).is( ':checked' ) ) {
			$( '#gglmps_control_rotate' ).removeAttr( 'disabled' );
		} else {
			$( '#gglmps_control_rotate' ).attr( 'disabled', true );
		}

		/* Disable rotate map control if Map View 45° is not checked */
		$( '#gglmps_basic_tilt45' ).on( 'change', function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '#gglmps_control_rotate' ).removeAttr( 'disabled' );
			} else {
				$( '#gglmps_control_rotate' ).attr( 'disabled', true );
			}
		} );

		/* Set up max zoom to map types */
		$( '#gglmps_basic_map_type' ).data( 'maxZoom', {
			'roadmap'   : 21,
			'terrain'   : 15,
			'satellite' : 19,
			'hybrid'    : 19
		});

		/* Get max zoom */
		$( '#gglmps_basic_map_type' ).on( 'change', function() {
			var maxZoom = $( '#gglmps_basic_map_type' ).data( 'maxZoom' )[ $( this ).find( 'option:selected' ).val() ];
			if ( $( '#gglmps_basic_zoom' ).val() > maxZoom ) {
				$( '#gglmps_basic_zoom' ).val( maxZoom );
			}
			$( '#gglmps_zoom_slider' ).slider({
				value  : $( '#gglmps_basic_zoom' ).val(),
				max    : maxZoom
			});
		});

		/* Zoom slider */
		if ( typeof $( '#gglmps_basic_map_type' ).find( 'option:selected' ).val() != 'undefined' ) {
			$( '#gglmps_zoom_slider' ).slider({
				value  : $( '#gglmps_basic_zoom' ).val(),
				min    : 0,
				max    : $( '#gglmps_basic_map_type' ).data( 'maxZoom' )[ $( '#gglmps_basic_map_type' ).find( 'option:selected' ).val() ],
				step   : 1,
				create : function( event, ui ) {
					$( '#gglmps_zoom_value' ).text( '[' + $( this ).slider( 'value' ) + ']' );
					$( '#gglmps_basic_zoom' ).hide();
				},
				slide : function( event, ui ) {
					$( '#gglmps_zoom_value' ).text( '[' + ui.value + ']' );
				},
				change: function( event, ui ) {
					$( '#gglmps_basic_zoom' ).val( ui.value );
					$( '#gglmps_zoom_value' ).text( '[' + ui.value + ']' );
				}
			});
		}

		/* Show or hide overview map control */
		$( '#gglmps_control_overview_map' ).on( 'change', function() {
			if ( $( this ).is( ':checked' ) == false ) {
				$( '#gglmps_control_overview_map_opened' ).attr( 'checked', false );
			}
		});

		/* Open overview map control */
		$( '#gglmps_control_overview_map_opened' ).on( 'change', function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '#gglmps_control_overview_map' ).attr( 'checked', true );
			} else {
				$( '#gglmps_control_overview_map' ).attr( 'checked', false );
			}
		});

		/* Resizing width of the map */
		$( '#gglmps_basic_width, select[name="gglmps_basic_width_unit"]' ).on( 'change', function() {
			if ( 'px' == $( 'select[name=gglmps_basic_width_unit]' ).val() ) {
				if ( $( '#gglmps_basic_width' ).val() < 150 ) {
					$( '#gglmps_basic_width' ).val( 150 );
				}
			} else {
				if ( $( '#gglmps_basic_width' ).val() > 100 ) {
					$( '#gglmps_basic_width' ).val( 100 );
				}
			}
		} );
	});
})( jQuery );