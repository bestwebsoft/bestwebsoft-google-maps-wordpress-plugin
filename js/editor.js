( function( $ ) {
	$( document ).ready(function() {
		/* Add support Google autocomplete */
		$( '#gglmps_marker_location' ).autocomplete({
			'onGetResult' : function( lat, lng ) {
				$( '#gglmps_marker_latlng' ).val( lat.toFixed( 6 ) + ', ' + lng.toFixed( 6 ) );
				$( '#gglmps_marker_location' ).removeClass( 'gglmps_editor_error' );
			}
		}).on( 'input paste', function() {
			var isCoordinates = $( this ).val().search( /^[-]?[\d]{1,2}[.][\d]{3,9}[,][ ]?[-]?[\d]{1,3}[.][\d]{3,9}$/ );
			$( '#gglmps_marker_location' ).removeClass( 'gglmps_editor_error' );
			 if ( isCoordinates == 0 ) {
				$( '#gglmps_marker_latlng' ).val( $( this ).val() );
			 } else {
			 	$( '#gglmps_marker_latlng' ).val( '' );
			 }
		}).on( 'keydown', function( e ) {
			if ( e.keyCode == 13 ) {
				return false;
			}
		});

		/* Adds a marker to the list markers */
		$( '#gglmps_marker_add' ).on( 'click', function() {
			var isCoordinates = $( '#gglmps_marker_location' ).val().search( /^[-]?[\d]{1,2}[.][\d]{3,9}[,][ ]?[-]?[\d]{1,3}[.][\d]{3,9}$/ );
			if ( $( '#gglmps_marker_location' ).val() == '' || $( '#gglmps_marker_latlng' ).val() == '' ) {
				$( '#gglmps_marker_location' ).addClass( 'gglmps_editor_error' );
				return;
			}

			var geocoder = new google.maps.Geocoder(),
				lat = $( '#gglmps_marker_latlng' ).val().split(',')[0],
				lng = $( '#gglmps_marker_latlng' ).val().split(',')[1].trim();

			geocoder.geocode({ 'latLng' : new google.maps.LatLng( lat, lng ) }, function( results, status ) {
				if ( results[0] && isCoordinates == 0 ) {
					$( '#gglmps_marker_location' ).val( results[0]['formatted_address'] );
				}
				var $marker = '\
					<li class="gglmps_marker">\
						<div class="gglmps_marker_control">\
							<span class="gglmps_marker_delete">{delete}</span>\
							<span class="gglmps_marker_edit">{edit}</span>\
							<span class="gglmps_marker_latlng">[{gglmps_latlang}]</span>\
						</div>\
						<div class="gglmps_marker_data">\
							<div class="gglmps_marker_location">{gglmps_location}</div>\
							<xmp class="gglmps_marker_tooltip">{gglmps_tooltip}</xmp>\
							<input class="gglmps_input_latlng" name="gglmps_list_marker_latlng[]" type="hidden" value="{gglmps_latlang}">\
							<textarea class="gglmps_textarea_location" name="gglmps_list_marker_location[]">{gglmps_location}</textarea>\
							<textarea class="gglmps_textarea_tooltip" name="gglmps_list_marker_tooltip[]">{gglmps_tooltip}</textarea>\
						</div>\
					</li>\
				';

				var $tooltip = $( '#gglmps_marker_tooltip' ).val().replace( /<script.*?>.*?<\/script>/g, "");

				$marker = $marker.replace( /{gglmps_latlang}/g, $( '#gglmps_marker_latlng' ).val() );
				$marker = $marker.replace( /{gglmps_location}/g, $( '#gglmps_marker_location' ).val() );
				$marker = $marker.replace( /{gglmps_tooltip}/g, $tooltip );
				$marker = $marker.replace( /{delete}/g, gglmps_translation.deleteMarker );
				$marker = $marker.replace( /{edit}/g, gglmps_translation.editMarker );
				$( '#gglmps_markers_container' ).append( $marker );
				$( '#gglmps_marker_cancel' ).hide();
				$( '#gglmps_marker_latlng, #gglmps_marker_location, #gglmps_marker_tooltip' ).val( '' );
				if ( $( '.gglmps_marker' ).size() > 0 ) {
					$( '.gglmps_no_markers' ).remove();
				}
			});
		});

		// Editing marker
		$( '#gglmps_markers_container' ).on( 'click', '.gglmps_marker_edit', function() {
			var markerIndex = $( this ).parents( '.gglmps_marker' ).index(),
				$marker = $( '#gglmps_markers_container .gglmps_marker' ).eq( markerIndex );
			$( '#gglmps_marker_add' ).hide();
			$( '#gglmps_marker_update' ).data( 'markerIndex', markerIndex ).show();
			$( '#gglmps_marker_cancel' ).show();
			$( '#gglmps_marker_location' ).val( $marker.find( '.gglmps_textarea_location' ).text() );
			$( '#gglmps_marker_latlng' ).val( '' );
			$( '#gglmps_marker_tooltip' ).val( $marker.find( '.gglmps_textarea_tooltip' ).text() );
			$( '#gglmps_marker_location' ).autocomplete( 'disabled', true );
		});

		// Deleting marker from the list markers
		$( '#gglmps_markers_container' ).on( 'click', '.gglmps_marker_delete', function() {
			var markerIndex = $( this ).parents( '.gglmps_marker' ).index(),
				$marker = $( '#gglmps_markers_container .gglmps_marker' ).eq( markerIndex );
			$marker.remove();
			$( '#gglmps_marker_cancel' ).trigger( 'click' );
			if ( $( '#gglmps_markers_container .gglmps_marker' ).size() == 0 ) {
				$( '#gglmps_markers_container' ).append( '<li class="gglmps_no_markers">' + gglmps_translation.noMarkers + '</li>' );
			}
		});

		// Cancel editing marker
		$( '#gglmps_marker_cancel' ).on( 'click', function() {
			$( '#gglmps_marker_update' ).data( 'markerIndex', null ).hide();
			$( '#gglmps_marker_cancel' ).hide();
			$( '#gglmps_marker_add' ).show();
			$( '#gglmps_marker_location, #gglmps_marker_tooltip' ).val( '' );
			$( '#gglmps_marker_location' ).autocomplete( 'disabled', false );
		});

		// Update edited marker
		$( '#gglmps_marker_update' ).on( 'click', function() {
			if ( $( '#gglmps_marker_location' ).val() == '' ) {
				$( '#gglmps_marker_location' ).addClass( 'gglmps_editor_error' );
				return;
			}
			var markerIndex = $( this ).data( 'markerIndex' ),
				$marker = $( '#gglmps_markers_container .gglmps_marker' ).eq( markerIndex ),
				$tooltip = $( '#gglmps_marker_tooltip' ).val().replace( /<script.*?>.*?<\/script>/g, "");
			$marker.find( '.gglmps_marker_location' ).text( $( '#gglmps_marker_location' ).val() );
			$marker.find( '.gglmps_marker_tooltip' ).text( $tooltip );
			$marker.find( '.gglmps_textarea_location' ).text( $( '#gglmps_marker_location' ).val() );
			$marker.find( '.gglmps_textarea_tooltip' ).text( $tooltip );
			$( '#gglmps_marker_update' ).data( 'markerIndex', null ).hide();
			$( '#gglmps_marker_cancel' ).hide();
			$( '#gglmps_marker_add' ).show();
			$( '#gglmps_marker_location, #gglmps_marker_tooltip, #gglmps_marker_latlng' ).val( '' );
			$( '#gglmps_marker_latlng' ).attr( 'disabled', false );
			$( '#gglmps_marker_location' ).autocomplete( 'disabled', false );
		});

		// Check availability Map View 45°
		if ( $( '#gglmps_basic_map_type' ).find( 'option:selected' ).val() == 'roadmap' || $( '#gglmps_basic_map_type' ).find( 'option:selected' ).val() == 'terrain' ) {
			$( '#gglmps_basic_tilt45' ).attr( 'disabled', true );
		}

		/* Check availability of Rotate Map control */
		if ( $( '#gglmps_basic_tilt45' ).is( ':checked' ) ) {
			$( '#gglmps_control_rotate' ).attr( 'disabled', false );
		} else {
			$( '#gglmps_control_rotate' ).attr( 'disabled', true );
		}

		/* Disable rotate map control if Map View 45° is not checked */
		$( '#gglmps_basic_tilt45' ).on( 'change', function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '#gglmps_control_rotate' ).attr( 'disabled', false );
			} else {
				$( '#gglmps_control_rotate' ).attr( 'disabled', true );
			}
		} );

		// Change map type in the preview map and check availability Map View 45° when changed map type
		$( '#gglmps_basic_map_type' ).on( 'change', function() {
			if ( $( this ).find( 'option:selected' ).val() == 'satellite' || $( this ).find( 'option:selected' ).val() == 'hybrid' ) {
				$( '#gglmps_basic_tilt45' ).attr( 'disabled', false );
			} else {
				$( '#gglmps_basic_tilt45' ).attr( 'disabled', true );
			}
			$( '#gglmps_zoom_slider' ).slider( {
				max : $( '#gglmps_basic_map_type' ).data( 'maxZoom' )[ $( '#gglmps_basic_map_type' ).find( 'option:selected' ).val() ],
				value  : $( '#gglmps_basic_zoom' ).val(),
			} );
		});

		// Hide zoom slider if auto zoom is checked
		if ( $( '#gglmps_basic_auto_zoom' ).is( ':checked' ) ) {
			$( '#gglmps_zoom_wrap' ).hide();
		}

		// Switching between auto zoom and manual zoom
		$( '#gglmps_basic_auto_zoom' ).on( 'change', function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '#gglmps_zoom_wrap' ).hide();
			} else {
				$( '#gglmps_zoom_wrap' ).show();
			}
		});

		// Zoom slider
		// Set up max zoom to map types
		$( '#gglmps_basic_map_type' ).data( 'maxZoom', {
			'roadmap'   : 21,
			'terrain'   : 15,
			'satellite' : 19,
			'hybrid'    : 19
		});

		/* Get max zoom */
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

		// Checking visibility additional options on the editor page
		if ( $( '#gglmps_editor_additional_options' ).is( ':checked' ) == false ) {
			$( '.gglmps_editor_additional_options' ).hide();
		}

		// Show or hide additional options on the editor page
		$( '#gglmps_editor_additional_options' ).on( 'click', function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '.gglmps_editor_additional_options' ).show();
			} else {
				$( '.gglmps_editor_additional_options' ).hide();
			}
		});

		/* Resizing width of the map*/
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

// Autocomplete plugin
( function( $ ) {
	var methods = {
		'init' : function( options ) {
			var autocompleteOptions = $.extend({
				'onGetResult' : false
			}, options );
			return this.each(function() {
				var $this = $( this ),
					container = $this.attr( 'id' );
				if ( ! container || $this.attr( 'type' ) != 'text' || $this.size() == 0 ) {
					return;
				}
				var autocomplete = new google.maps.places.Autocomplete( document.getElementById( container ) );
				google.maps.event.addListener( autocomplete, 'place_changed', function() {
					var place = autocomplete.getPlace();
					methods.onGetResult.call( $this, place.geometry.location.lat(), place.geometry.location.lng() );
				});
				$this.data( 'data', {
					'options' : autocompleteOptions
				});
				methods.disabled.call( $this, false );
			});
		}, // end init
		'disabled' : function( value ) {
			if ( value ) {
				$( '.pac-container' ).css({ 'visibility' : 'hidden'	});
			} else {
				$( '.pac-container' ).css({ 'visibility' : 'visible' });
			}
		}, // end disabled
		'onGetResult' : function( lat, lng ) {
			var $this = this,
				options = $this.data( 'data' )['options'];
			if ( typeof options.onGetResult == 'function' ) options.onGetResult.call( $this, lat, lng );
		}
	}
	jQuery.fn.autocomplete = function( method ) {
		if ( methods[ method ] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' + method + ' not found!' );
		}
	}
})( jQuery );