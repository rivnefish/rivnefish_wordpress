(function($){
	$(document).ready( function(){
		var $et_admin_map = $( '#et_admin_map' ),
			marker_lat,
			marker_lng;

		marker_lat = $( '#et_listing_lat' ).val();
		marker_lng = $( '#et_listing_lng' ).val();

		if ( marker_lat == '' ) marker_lat = '37.715342685425995';
		if ( marker_lng == '' ) marker_lng = '-122.43436531250012';

		$et_admin_map.after( '<a href="#" id="et_decect_marker_address">' + et_map_admin_settings.detect_address + '</a>' + ' ( ' + et_map_admin_settings.note_address + ' )' );

		$( '#et_listing_custom_address' ).after( '<small>' + '<a href="#" id="et_detect_lat_lng">' + et_map_admin_settings.detect_lat_lng + '</a>' + '</small>' );

		$et_admin_map.gmap3({
			map:{
				options:{
					center : [marker_lat, marker_lng],
					zoom:3,
					mapTypeId: google.maps.MapTypeId.ROADMAP,
					mapTypeControl: true,
					navigationControl: true,
					scrollwheel: true,
					streetViewControl: true
				}
			},
			marker : {
				id : 'et_admin_marker',
				latLng : [marker_lat, marker_lng],
				options: {
					icon : et_map_admin_settings.theme_dir + "/images/red-marker.png",
					draggable : true
				},
				events:{
					dragend: function(marker){
						$( '#et_listing_lat' ).val( marker.getPosition().lat() );
						$( '#et_listing_lng' ).val( marker.getPosition().lng() );
					}
				}
			}
		});

		$( '#et_decect_marker_address' ).click( function() {
			var et_listing_latitude = $( '#et_listing_lat' ).val(),
				et_listing_longtitude = $( '#et_listing_lng' ).val();

			if ( et_listing_latitude == '' || et_listing_longtitude == '' ) return false;

			$et_admin_map.gmap3({
				getaddress:{
					latLng : [et_listing_latitude, et_listing_longtitude],
					callback: function( results ) {
						var content = results && results[1] ? results && results[1].formatted_address : "no address";

						$( '#et_listing_custom_address' ).val( content );
					}
				}
			});

			return false;
		} );

		$( '#et_detect_lat_lng' ).click( function() {
			var et_listing_address = $( '#et_listing_custom_address' ).val();

			if ( '' == et_listing_address ) {
				alert( et_map_admin_settings.fill_address_notice );
				return false;
			}

			$et_admin_map.gmap3({
				getlatlng:{
    				address: et_listing_address,
					callback: function( results ) {
						var et_admin_marker;

						if ( ! results ) return;

						et_admin_marker = $(this).gmap3({
							get: {
								id : 'et_admin_marker'
							}
						});

						et_admin_marker.setPosition( results[0].geometry.location );
						$(this).gmap3("get").panTo( results[0].geometry.location );

						$( '#et_listing_lat' ).val( results[0].geometry.location.lat() );
						$( '#et_listing_lng' ).val( results[0].geometry.location.lng() );
					}
				}
			});

			return false;
		} );
	} );
})(jQuery)