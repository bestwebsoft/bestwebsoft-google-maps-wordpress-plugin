( function( $ ) {
	$( document ).ready( function() {
		gglmps_map();
	});
}) ( jQuery );

/* Display Google Map */
function gglmps_map() {
	( function( $ ) {
		$( '.gglmps_map' ).each( function() {
			var map_data_basic =  JSON.parse( $( this ).attr( 'data-basic' ) ),
				map_data_controls = JSON.parse( $( this ).attr( 'data-controls' ) ),
				map_data_markers = JSON.parse( $( this ).attr( 'data-markers' ) );		

			$( this ).bws_googlemaps( {
				'mapType'                   : map_data_basic['map_type'],
				'tilt45'                    : map_data_basic['tilt45'] ? true : false,
				'autoZoom'                  : map_data_basic['auto_zoom'] ? true : false,
				'zoom'                      : Number( map_data_basic['zoom'] ),
				'mapTypeControl'            : map_data_controls['map_type'] ? true : false,
				'panControl'                : map_data_controls['pan'] ? true : false,
				'rotateControl'             : map_data_controls['rotate'] ? true : false,
				'zoomControl'               : map_data_controls['zoom'] ? true : false,
				'scaleControl'              : map_data_controls['scale'] ? true : false,
				'streetViewControl'         : false,
				'overviewMapControl'        : false,
				'overviewMapControlOptions' : false,
				'draggable'                 : false,
				'disableDoubleClickZoom'    : false,
				'scrollwheel'               : false
			}, map_data_markers );
		});
	}) ( jQuery );
}

/* BWS Google Maps function */
( function( $ ) {
	jQuery.fn.bws_googlemaps = function( options, markers_options ) {
		if ( typeof google == 'undefined' )
			return;

		var map_markers = markers_options;

		var mapOptions = $.extend({
			'mapType'                   : 'roadmap',
			'mapTypeId'                 : google.maps.MapTypeId[options.mapType.toUpperCase()],
			'tilt45'                    : true,
			'autoZoom'                  : true,
			'center'                    : new google.maps.LatLng( 39.639538,-103.007813 ),
			'zoom'                      : 3,
			'draggableCursor'           : 'default',
			'mapTypeControl'            : true,
			'panControl'                : true,
			'rotateControl'             : true,
			'zoomControl'               : true,
			'scaleControl'              : true,
			'streetViewControl'         : true,
			'overviewMapControl'        : false,
			'overviewMapControlOptions' : { 'opened' : false },
			'draggable'                 : true,
			'disableDoubleClickZoom'    : false,
			'scrollwheel'               : true
		}, options );

		var map = new google.maps.Map( document.getElementById( this.attr( 'id' ) ), mapOptions );

		if ( mapOptions.tilt45 ) {
			map.setTilt( 45 );
		} else {
			map.setTilt( 0 );
		}

		var markers = [];

		for ( var i in map_markers ) {
			
			var data = map_markers[ i ],
				bounds = new google.maps.LatLngBounds(),
				lat = data.latlng.split( ',' )[0],
				lng = data.latlng.split( ',' )[1].trim(),
				marker = new google.maps.Marker({
					position : new google.maps.LatLng( lat, lng ),
					map      : map
				}),
				infowindow = new google.maps.InfoWindow({
					content  : data.tooltip,
					maxWidth : this.width() - 20
				});

			markers.push({
				'marker'     : marker,
				'infowindow' : infowindow
			});
			
			google.maps.event.addListener( marker, 'click', ( function( marker, infowindow ) {
				return function() {
					if ( infowindow.getContent() != '' ) {
						center = map.getCenter();
						infowindow.open( map, marker );
					}
				}
			})( marker, infowindow ));
			
			google.maps.event.addListener( infowindow, 'closeclick', function() {
				map.panTo( center );
			});

			$.each( markers, function( index, markers ) {
				bounds.extend( markers['marker'].position );
			});
			map.fitBounds( bounds );
		}

		if ( ! mapOptions.autoZoom ) {
			var boundsListener = google.maps.event.addListener( map, 'bounds_changed', function() {					
				map.setZoom( mapOptions.zoom );
				google.maps.event.removeListener( boundsListener );
			});
		}
	}
})( jQuery );