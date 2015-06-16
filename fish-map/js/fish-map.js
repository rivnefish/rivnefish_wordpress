(function ($) {

jQuery(document).ready(function ($) {
    initializeMap();
    $('#weather').click(toggleWeather);
    $('#search_button').click(searchLocations);
    $('#show_all_button').click(function () {
        _setMarkers(fishMapAllMarkers);
    });
    
    // Init sidebar's markers filtering
    listFilter($('#markersFilter'), $('#markersList'));

});

var map = null,
    weatherLayer = null,

    rivneLatLng = new google.maps.LatLng(50.619616, 26.251379),
    browserSupportFlag = false,
    markers = [],
    fishMapAllMarkers = [],
    markerCluster = null,
    sideBar = null,
    sideBarTotal = null,
    infoWindow = null,
    locationSelect = null,
    fishIcon = null,
    fishIconShadow = null,
    fishIconBig = null,
    fishIconShadowBig = null;

function initializeMap() {
    // Стилі для карти. Можна виділяти кольором водойми, парки, траси і т.п.
    var emphasizeLakesStyles = [
        {
            featureType: "water",
            stylers: [
                {lightness: -20},
                {saturation: 41}
            ]
        }
    ];

    var myOptions = {
        zoom: 10,
        center: rivneLatLng, // Center map at Rivne
        scaleControl: true,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: emphasizeLakesStyles
    };
    var mapCanvas = $('#map_canvas, .map_canvas')[0];
    map = new google.maps.Map(mapCanvas, myOptions);
    sideBar = document.getElementById("markersList");
    sideBarTotal = document.getElementById("markersListTotal");

    infoWindow = new google.maps.InfoWindow();
    // Uncomment to close info window on map click
    // google.maps.event.addListener(map, "click", function() {infoWindow.close();});

    fishIcon = new google.maps.MarkerImage("https://lh4.googleusercontent.com/_AlLFR-j5gDI/TXeEWPwQfkI/AAAAAAAABEQ/C1wZSANaCeg/s800/float_fish_16x47_new.png",
        new google.maps.Size(16,47),
        new google.maps.Point(0,0),
        new google.maps.Point(0,47));
    fishIconShadow = new google.maps.MarkerImage("https://lh6.googleusercontent.com/_AlLFR-j5gDI/TXeEddrdM9I/AAAAAAAABEY/oipNS7GnUb0/s800/float_fish_shadow_56x47_new.png",
        new google.maps.Size(56,47),
        new google.maps.Point(0,0),
        new google.maps.Point(0,47));

    fishIconBig = new google.maps.MarkerImage("https://lh5.googleusercontent.com/_AlLFR-j5gDI/TXd-N3DuFGI/AAAAAAAABDk/jTJ0H9bzOCA/s800/float_fish_20x59_new.png",
        new google.maps.Size(20,59),
        new google.maps.Point(0,0),
        new google.maps.Point(0,59));
    fishIconShadowBig = new google.maps.MarkerImage("https://lh5.googleusercontent.com/_AlLFR-j5gDI/TXeB1zUKbjI/AAAAAAAABEE/WQzH_elwvac/s800/float_fish_shadow_70x59_new.png",
        new google.maps.Size(70,59),
        new google.maps.Point(0,0),
        new google.maps.Point(0,59));

    // locationSelect = document.getElementById("locationSelect");
    // locationSelect.onchange = function() {
    //     var markerNum = locationSelect.options[locationSelect.selectedIndex].value;
    //     if (markerNum != "none"){
    //         google.maps.event.trigger(markers[markerNum], 'click');
    //     }
    // };
    // Init Marker Clusterer
    markerCluster = new MarkerClusterer(map, [], {
        minimumClusterSize: 4
    });

    setupAllMarkers();
    centerMapFromGeolocation(map);
    setupWeather();
}

/* Geocoder functionality - search location on the map */
function searchLocations() {
    var address = document.getElementById("addressInput").value;
    var geocoder = new google.maps.Geocoder();
    geocoder.geocode({address: address}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            searchLocationsNear(results[0].geometry.location);
        } else {
            alert(address + ' не знайдено!');
        }
    });
}

function clearMarkers() {
    infoWindow.close();
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }
    markers = [];

    sideBar.innerHTML = "";
    sideBarTotal.innerHTML = "";
    markerCluster.clearMarkers();
}

function _setMarkers(data) {
    clearMarkers();
    for (var i = 0; i < data.length; i++) {
        var latlng = new google.maps.LatLng(
            parseFloat(data[i]["lat"]),
            parseFloat(data[i]["lng"]));
        createMarker(latlng, data[i]["name"], data[i]["address"], data[i]["marker_id"]);
    }
    updateMarkersCount();
    markerCluster.addMarkers(markers);
    et_listing_make_fluid();
}

function _extendBounds() {
    var bounds = new google.maps.LatLngBounds();
    for (var i = 0, n = markers.length; i < n; ++i) {
        bounds.extend(markers[i].position);
    }
    map.fitBounds(bounds);
}

function searchLocationsNear(center) {
    map.setCenter(center);

    var radius = document.getElementById('radiusSelect').value;
    var searchUrl = WP_AJAX_URL + '?action=fish_map_markers_search&lat=' + center.lat() + '&lng=' + center.lng() + '&radius=' + radius;
    $.getJSON(searchUrl, function(data) {
        _setMarkers(data);
        if (data.length > 3) {
            // _extendBounds();
        }
    });
}

function setupAllMarkers () {
    var searchUrl = WP_AJAX_URL + '?action=fish_map_markers&lat=' + rivneLatLng.lat() + '&lng=' + rivneLatLng.lng();
    $.getJSON(searchUrl, function(data) {
        _setMarkers(data);
        fishMapAllMarkers = data;
    }); // End $.get()
}

function createMarker(latlng, name, address, id) {
    var marker = new google.maps.Marker({
        map: map,
        position: latlng,
        title: name,
        icon: fishIcon,
        shadow: fishIconShadow,
        id: id,
        draggable: WP_ADMIN
    });

    google.maps.event.addListener(marker, 'click', function() {
        showInfoWindow(marker);
    });
    google.maps.event.addListener(marker, 'dblclick', function() {
        map.setCenter(marker.position);
    });
    google.maps.event.addListener(marker, 'dragstart', function () {
        marker.originPosition = marker.getPosition();
    });
    google.maps.event.addListener(marker, 'dragend', function () {
        updateMarkerPosition(marker);
    });
    
    addToSideBar(marker, name);
    markers.push(marker);
}

function updateMarkerPosition(marker) {
    if (!confirm('Оновити позицію маркера?')) {
        revertPosition(marker);
        return;
    }
    var position = marker.getPosition();
    var postData = { marker_id: marker.id, lat: position.lat(), lng: position.lng() };
    $.post(WP_AJAX_URL + '?action=fish_map_update_position', postData, function (response) {
        if (response.marker_id) {
            noty({ text: 'Позицію маркера оновлено', layout: 'topRight', type: 'success', timeout: 5000 });
        }
    }, 'json');
}

function revertPosition(marker) {
    marker.setPosition(marker.originPosition);
}

function openInfoWindow(marker, html) {
    infoWindow.setContent(html);
    infoWindow.open(map, marker);
  }

function parseXml(str) {
    if (window.ActiveXObject) {
        var doc = new ActiveXObject('Microsoft.XMLDOM');
        doc.loadXML(str);
        return doc;
    } else if (window.DOMParser) {
        return (new DOMParser).parseFromString(str, 'text/xml');
    }
}

function doNothing() {}

/* Show/Hide controls */
  function hide_map_controls(checked) {
    if (checked) {
      //map.setOptions({disableDefaultUI:true});
      map.setOptions({panControl: false,
                       scaleControl: false,
                       zoomControl: false,
                       mapTypeControl: false,
                       streetViewControl: false});
    } else {
      map.setOptions({panControl: true,
                       scaleControl: true,
                       zoomControl: true,
                       mapTypeControl: true,
                       streetViewControl: false});
    }
  };

/* Change map's Scale Control position: BOTTOM, BOTTOM_LEFT,BOTTOM_RIGHT, LEFT, RIGHT, TOP, TOP_LEFT, TOP_RIGHT */
  function change_scale_position(val) {
    switch(val) {
      case "BOTTOM":
        map.setOptions({scaleControlOptions:{position:google.maps.ControlPosition.BOTTOM}});
        break;
      case "BOTTOM_LEFT":
        map.setOptions({scaleControlOptions:{position:google.maps.ControlPosition.BOTTOM_LEFT}});
        break;
      case "BOTTOM_RIGHT":
        map.setOptions({scaleControlOptions:{position:google.maps.ControlPosition.BOTTOM_RIGHT}});
        break;
      case "TOP":
        map.setOptions({scaleControlOptions:{position:google.maps.ControlPosition.TOP}});
        break;
      case "TOP_LEFT":
        map.setOptions({scaleControlOptions:{position:google.maps.ControlPosition.TOP_LEFT}});
        break;
      case "TOP_RIGHT":
        map.setOptions({scaleControlOptions:{position:google.maps.ControlPosition.TOP_RIGHT}});
        break;
      case "LEFT":
        map.setOptions({scaleControlOptions:{position:google.maps.ControlPosition.LEFT}});
        break;
      case "RIGHT":
        map.setOptions({scaleControlOptions:{position:google.maps.ControlPosition.RIGHT}});
        break;
    }
  };
var fish_scores = [
    "https://lh3.googleusercontent.com/-pA3e1NFvUm8/Trz_UZ8Fs-I/AAAAAAAABdM/aEK8mt1ZS_I/s800/score_01.png",
    "https://lh6.googleusercontent.com/-4DN2LTsUbG4/Trz_UYtEUtI/AAAAAAAABdI/YVTX3zrQTSo/s800/score_02.png",
    "https://lh6.googleusercontent.com/-ZMiSp_fH5OE/Trz_URksCkI/AAAAAAAABdQ/dfnXeIogSiM/s800/score_03.png",
    "https://lh4.googleusercontent.com/-3pJyfPwa85U/Trz_UsRN6YI/AAAAAAAABdY/Bai3V0RKzY8/s800/score_04.png",
    "https://lh4.googleusercontent.com/-upUuE-VV6WQ/Trz_VH03c0I/AAAAAAAABdc/YDxOsgTmC-U/s800/score_05.png",
    "https://lh5.googleusercontent.com/-cu-ov_hiSGc/Trz_VOgc8_I/AAAAAAAABdk/m3lw1UgdJ58/s800/score_06.png",
    "https://lh6.googleusercontent.com/-L0vacmk1T6I/Trz_VQJw6UI/AAAAAAAABdo/a-D6BUkTsek/s800/score_07.png",
    "https://lh3.googleusercontent.com/-nD79CO5CYYs/Trz_XhSMy4I/AAAAAAAABeM/5odbngZEYQc/s800/score_08.png",
    "https://lh6.googleusercontent.com/-BRSSsL8dsVk/Trz_V1FCwEI/AAAAAAAABds/wdhGbYRQjL4/s800/score_09.png",
    "https://lh5.googleusercontent.com/-gBXX50iC2uw/Trz_WGWwfSI/AAAAAAAABd4/RjzfJj0CbAQ/s800/score_10.png"
];

function showInfoWindow(marker) {
    var searchUrl = WP_AJAX_URL + '?action=fish_map_marker_info&marker_id=' + marker.id;
    $.getJSON(searchUrl, function(markerInfo) {
        // Create marker
        var name = markerInfo["name"];
        var payment = markerInfo["paid_fish"] ? markerInfo["paid_fish"] : "-";
        var contact = markerInfo["contact"] ? markerInfo["contact"] : "-";
        var contact_name = markerInfo["contact_name"] ? markerInfo["contact_name"] : "-";
        var photo1 = markerInfo["photo_url1"];
        var photo2 = markerInfo["photo_url2"];
        var photo3 = markerInfo["photo_url3"];
        var photo4 = markerInfo["photo_url4"];
        var page_url = markerInfo["page_url"];

        var html = '<div class="marker-info">';

        // Title
        html += '<div><strong>' + name + '</strong>' + markerEditLink(marker.id) + '</div>';

        // Add fishes
        var fishes = markerInfo['fishes'];
        if (fishes) {
            html += "<div><i>Риба: </i>";
            for (var i = 0; i < fishes.length; i++) {
                var article_url = fishes[i]["article_url"];
                var icon_url = fishes[i]["icon_url"];
                var weight_avg = fishes[i]["weight_avg"] || "- ";
                var weight_max = fishes[i]["weight_max"] || "- ";
                var amount = fishes[i]["amount"] || "-";
                if (article_url){
                    html += "<a href='"+article_url+"' >";
                }
                html += "<img class='fish_icon' style='width:" + fishes[i]["icon_width"] +
                    "px; height:" + fishes[i]["icon_height"] +
                    "px' src='" + fishes[i]["icon_url"] +
                    "' alt='" + fishes[i]["name"] +
                    "' title='" + fishes[i]["name"] +
                        ", середня вага: " + weight_avg +
                        "гр, максимальна " + weight_max +
                        "гр, кльов " + amount + "/10" +
                    "' />";
                if (article_url){
                    html += "</a>";
                }
                // Add score image if necessary
                var score = parseInt(amount);
                if (score) {
                    html += "<img class='fish_score' style='width:3px; height:28px'" +
                    " src='" + fish_scores[score-1] +
                    "' alt='" + score +
                    "' />";
                }
            }
            html += "</div>";
        }

        html += "<div><i>Оплата: </i>" + payment + "</div>"
              + "<div><i>Контакт: </i>" + contact + "</div>";

        if (photo1 || photo2 || photo2 || photo4) {
            html += "<div>";
            if (photo1) {
                var photo1_ico = scaled_url(photo1);
                html += "<a href='"+photo1+"' target='_blank' title='Збільшити'>"+
                "<img class='marker_photo' style='width:53px; height:40px' src='"+photo1_ico+"'/></a>";
            }
            if (photo2) {
                var photo2_ico = scaled_url(photo2);
                html += "<a href='"+photo2+"' target='_blank' title='Збільшити'>"+
                "<img class='marker_photo' style='width:53px; height:40px' src='"+photo2_ico+"'/></a>";
            }
            if (photo3) {
                var photo3_ico = scaled_url(photo3);
                html += "<a href='"+photo3+"' target='_blank' title='Збільшити'>"+
                "<img class='marker_photo' style='width:53px; height:40px' src='"+photo3_ico+"'/></a>";
            }
            if (photo4) {
                var photo4_ico = scaled_url(photo4);
                html += "<a href='"+photo4+"' target='_blank' title='Збільшити'>"+
                "<img class='marker_photo' style='width:53px; height:40px' src='"+photo4_ico+"'/></a>";
            }
            html += "</div>";
        } else if (markerInfo['photos']) {
            html += '<div class="photos">';
            for (var i in markerInfo['photos']) {
                html += "<a href='" + markerInfo['photos'][i]['photo'] + "' target='_blank' title='Збільшити'>" +
                        "<img class='marker_photo' src='" + markerInfo['photos'][i]['thumbnail'] + "'/></a>";
            }
            html += "</div>";
        }
        if (page_url) {
            html += "<div>";
            html += "<a title='Прочитати статтю про цю точку, переглянути/додати коментарі'"+
                     " href='" + page_url + "'>Деталі/Коментарі &gt;&gt;&gt;</a>";
            html += "</div>";
        }
        html += "</div>";

        openInfoWindow(marker, html);
    }); // End $.get()
}

function apiAdminUrl() {
    return 'http://api.rivnefish.com/admin/site_manager';
}

function markerUrl(markerId) {
    return apiAdminUrl() + '/markers/' + markerId + '/';
}

function markerEditLink(markerId) {
    if (!WP_ADMIN) return '';
    return '<a href="' + markerUrl(markerId) + '" class="action edit"></a>';
}

function scaled_url(str) {
    /* Create PicasaWeb URL with scale 's53' */
    var clear_str = str.replace(/\/s\d{1,4}\//,'/'); // remove '/s1200/' scale mark if exist
    if (clear_str)
        str = clear_str;
    var scale = str.substr(str.lastIndexOf('/')); // e.g. /Bochanitsa_I.JPG
    res = str.replace(scale, '/s53'+scale);
    return res;
}

function updateMarkersCount() {
    var cnt = sideBar.childNodes.length;
    $(sideBarTotal).text('(' + cnt + ')');
}

function addToSideBar(marker, caption) {
    var $li = $('<li class="clearfix"></li>'),
        $anchor = $('<a class="sidebar-list" href="#"></a>').text(caption || marker.title);

    $li.append($anchor);

    $li.click(function(){
        $('.et-active-listing').removeClass('et-active-listing');
        $(this).addClass('et-active-listing');
        google.maps.event.trigger(marker, 'click');
        map.setCenter(marker.position);
        return false;
    });

    $(sideBar).append($li);

    google.maps.event.addListener(marker, "click", function() {
        return false;
    });
}

function setupWeather() {
    // Set weather
    weatherLayer = new google.maps.weather.WeatherLayer({
        temperatureUnits: google.maps.weather.TemperatureUnit.CELSIUS,
        windSpeedUnits: google.maps.weather.WindSpeedUnit.METERS_PER_SECOND
    });
}

    function toggleWeather() {
        var c = document.getElementById('weather');
        if (c.checked)
            weatherLayer.setMap(map);
        else
            weatherLayer.setMap(null);
    }
    /* Filter Markers functionality*/
    // custom css expression for a case-insensitive contains()
    jQuery.expr[':'].Contains = function(a, i, m) {
        return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
    };

    function listFilter(input, list) {
        input
            .change(function() {
                var filter = $(this).val();
                if (filter) {
                    list.find("a:not(:Contains(" + filter + "))").parent().hide();
                    list.find("a:Contains(" + filter + ")").each(function() {
                        var $eleContainer = $(this);
                        $eleContainer.parent().show();
                        var text = $eleContainer.text();
                        var index = text.toUpperCase().indexOf(filter.toUpperCase());
                        $eleContainer.html(text.substring(0, index) +
                                '<span style="color: red;">' +
                                text.substring(index, index + filter.length) +
                                '</span>' + text.substring(index + filter.length));
                    });
                } else {
                    list.find("li").show();
                    list.find("a").each(function() {
                        var $eleContainer = $(this);
                        $eleContainer.text($eleContainer.text());
                    });
                }
                return false;
            })
            .keyup(function() {
                // fire the above change() event after every letter
                $(this).change();
            });
    }

}(jQuery));
$ = jQuery;