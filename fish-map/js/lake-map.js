jQuery(function($) {
    var markerInfo = JSON.parse($('#marker-data').html());

    var mapStyles = [{
            featureType: "water",
            stylers: [
                {lightness: -20},
                {saturation: 41}
            ]
        }];

    var markerCoords = new google.maps.LatLng(markerInfo.lat, markerInfo.lng)

    var mapOptions = {
        zoom: 11,
        center: markerCoords, // Center map at particular marker
        scaleControl: true,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: mapStyles
    };
    var map = new google.maps.Map(document.getElementById('et-single-map'), mapOptions);

    // Draw marker
    var fishIconBig = new google.maps.MarkerImage("https://lh5.googleusercontent.com/_AlLFR-j5gDI/TXd-N3DuFGI/AAAAAAAABDk/jTJ0H9bzOCA/s800/float_fish_20x59_new.png",
            new google.maps.Size(20, 59),
            new google.maps.Point(0, 0),
            new google.maps.Point(0, 59));
    var fishIconShadowBig = new google.maps.MarkerImage("https://lh5.googleusercontent.com/_AlLFR-j5gDI/TXeB1zUKbjI/AAAAAAAABEE/WQzH_elwvac/s800/float_fish_shadow_70x59_new.png",
            new google.maps.Size(70, 59),
            new google.maps.Point(0, 0),
            new google.maps.Point(0, 59));

    var marker = new google.maps.Marker({
        map: map,
        position: markerCoords,
        title: markerInfo.name,
        icon: fishIconBig,
        shadow: fishIconShadowBig,
        id: markerInfo.marker_id
    });
});