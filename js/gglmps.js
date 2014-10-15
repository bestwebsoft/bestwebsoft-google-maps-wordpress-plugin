( function( $ ) {
	$( document ).ready(function() {
		// Display Google Map
		$( '.gglmps_map' ).each( function() {
			var container = $( this ).attr( 'id' );
			$( '#' + container ).bws_googlemaps({
				'mapType'                   : gglmps_map_data['basic']['map_type'],
				'tilt45'                    : gglmps_map_data['basic']['tilt45'] ? true : false,
				'autoZoom'                  : gglmps_map_data['basic']['auto_zoom'] ? true : false,
				'zoom'                      : Number( gglmps_map_data['basic']['zoom'] ),
				'mapTypeControl'            : gglmps_map_data['controls']['map_type'] ? true : false,
				'panControl'                : gglmps_map_data['controls']['pan'] ? true : false,
				'rotateControl'             : gglmps_map_data['controls']['rotate'] ? true : false,
				'zoomControl'               : gglmps_map_data['controls']['zoom'] ? true : false,
				'scaleControl'              : gglmps_map_data['controls']['scale'] ? true : false,
				'streetViewControl'         : false,
				'overviewMapControl'        : false,
				'overviewMapControlOptions' : false,
				'draggable'                 : false,
				'disableDoubleClickZoom'    : false,
				'scrollwheel'               : false
			});
			// Add markers to the Google Map
			$( '#' + gglmps_map_data['container'] ).bws_googlemaps( 'addMarker', new function() {
				var markers = [];
				for ( var marker in gglmps_map_data['markers'] ) {
					markers.push( gglmps_map_data['markers'][marker] );
				}
				return markers;
			});
		});
	});
}) ( jQuery );

// BWS Google Maps plugin
( function( $ ) {
	var methods = {
		'init' : function( options ) {
			if ( typeof google == 'undefined' ) {
				return;
			}
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
			this.each(function() {
				var $this = $( this ),
					container = $( this ).attr( 'id' );
				if ( ! container ) {
					return;
				}
				var map = new google.maps.Map( document.getElementById( container ), mapOptions );
				if ( mapOptions.tilt45 ) {
					map.setTilt( 45 );
				} else {
					map.setTilt( 0 );
				}
				$( this ).data( 'data', {
					'container' : container,
					'map'       : map,
					'options'   : mapOptions,
					'markers'   : []
				});
			});
		},
		'appendMarker' : function( data ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'],
					options = $this.data( 'data' )['options'],
					container = $( '#' + $this.data( 'data' )['container'] ),
					markers = $this.data( 'data' )['markers'],
					bounds = new google.maps.LatLngBounds(),
					lat = data.latlng.split( ',' )[0],
					lng = data.latlng.split( ',' )[1],
					marker = new google.maps.Marker({
						position : new google.maps.LatLng( lat, lng ),
						map      : map
					}),
					infowindow = new google.maps.InfoWindow({
						content  : data.tooltip,
						maxWidth : container.width() - 20
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
				if ( ! options.autoZoom ) {
					var boundsListener = google.maps.event.addListener( map, 'bounds_changed', function() {
						map.setZoom( options.zoom );
						google.maps.event.removeListener( boundsListener );
					});
				}
			});
		}, //end appendMarker
		'addMarker' : function ( data ) {
			this.each(function() {
				var $this = $( this );
				if ( data instanceof Array ) {
					for ( var i in data ) {
						methods.appendMarker.call( $this, data[ i ] );
					}
				} else if ( data instanceof Object ) {
					methods.appendMarker.call( $this, data );
				}
			});
		}
	} // end methods
	jQuery.fn.bws_googlemaps = function( method ) {
		if ( methods[ method ] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' + method + ' not found!' );
		}
	}
})( jQuery );