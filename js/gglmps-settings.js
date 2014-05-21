( function( $ ) {
	$( document ).ready(function() {
		// Check availability Map View 45°
		if ( $( '#gglmps_basic_map_type' ).find( 'option:selected' ).val() == 'roadmap' || $( '#gglmps_basic_map_type' ).find( 'option:selected' ).val() == 'terrain' ) {
			$( '#gglmps_basic_tilt45' ).attr( 'disabled', true );
		}
		
		// Change map type in the preview map and check availability Map View 45° when changed map type
		$( '#gglmps_basic_map_type' ).on( 'change', function() {
			if ( $( this ).find( 'option:selected' ).val() == 'satellite' || $( this ).find( 'option:selected' ).val() == 'hybrid' ) {
				$( '#gglmps_basic_tilt45' ).attr( 'disabled', false );
			} else {
				$( '#gglmps_basic_tilt45' ).attr( 'disabled', true );
			}
		});

		// Check availability preview map auto zoom
		if ( $( '#gglmps_basic_auto_zoom' ).is( ':checked' ) ) {
			$( '#gglmps_zoom_wrap' ).hide();
		}

		// Switching between auto zoom and manual zoom
		$( '#gglmps_basic_auto_zoom' ).on( 'change', function() {
			switch ( $( this ).is( ':checked' ) ) {
				case true:
					$( '#gglmps_zoom_wrap' ).hide();
					break;
				case false:
					$( '#gglmps_zoom_wrap' ).show();
					break;
			}
		});

		// Set up max zoom to map types
		$( '#gglmps_basic_map_type' ).data( 'maxZoom', {
			'roadmap'   : 21,
			'terrain'   : 15,
			'satellite' : 19,
			'hybrid'    : 19 
		});

		// Get max zoom
		$( '#gglmps_basic_map_type' ).on( 'change', function() {
			var maxZoom = $( '#gglmps_basic_map_type' ).data( 'maxZoom' )[ $( this ).find( 'option:selected' ).val() ];
			if ( $( '#gglmps_basic_zoom' ).val() >  maxZoom ) {
				$( '#gglmps_basic_zoom' ).val( maxZoom );
			}
			$( '#gglmps_zoom_slider' ).slider({
				value  : $( '#gglmps_basic_zoom' ).val(),
				max    : maxZoom
			});
		});

		// Zoom slider
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

		// Checking visibility additional options on the settings page
		if ( $( '#gglmps_settings_additional_options' ).is( ':checked' ) == false ) {
			$( '.gglmps_settings_additional_options' ).hide();
		}

		// Show or hide additional options on the settings page
		$( '#gglmps_settings_additional_options' ).on( 'click', function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '.gglmps_settings_additional_options' ).show();
			} else {
				$( '.gglmps_settings_additional_options' ).hide();
			}
		});		

		// Show or hide overview map control
		$( '#gglmps_control_overview_map' ).on( 'change', function() {
			if ( $( this ).is( ':checked' )  == false ) {
				$( '#gglmps_control_overview_map_opened' ).attr( 'checked', false );
			}
		});

		// Open overview map control
		$( '#gglmps_control_overview_map_opened' ).on( 'change', function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '#gglmps_control_overview_map' ).attr( 'checked', true );
			} else {
				$( '#gglmps_control_overview_map' ).attr( 'checked', false );
			}
		});

		// Save or update main settings
		$( '#gglmps_settings_form' ).on( 'submit', function() {
			if ( $( '#gglmps_basic_width' ).val() < 150 ) {
				$( '#gglmps_basic_width' ).val( 150 );
			}
			if ( $( '#gglmps_basic_height' ).val() < 150 ) {
				$( '#gglmps_basic_height' ).val( 150 );
			}
			if ( $( '#gglmps_basic_zoom' ).val() < 0 ) {
				$( '#gglmps_basic_zoom' ).val( 0 );
			}
			if ( $( '#gglmps_basic_zoom' ).val() > 21 ) {
				$( '#gglmps_basic_zoom' ).val( 21 );
			}
		});
		$( '#gglmps_settings_form input, #gglmps_settings_form select' ).on( 'change select', function() {
			if ( $( this ).attr( 'type' ) != 'submit' ) {
				$( '.updated.fade' ).css( 'display', 'none' );
				$( '#gglmps_update_notice' ).css( 'display', 'block' );
			}
		});
	}); // end document ready
})( jQuery );