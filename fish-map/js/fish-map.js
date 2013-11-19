/**
 * JavaScript code
 *
 **/
var map = null;
var weatherLayer = null;
var cloudLayer = null;

var RivneLatLng = new google.maps.LatLng(50.619616, 26.251379);
var browserSupportFlag =  new Boolean();
var initialLocation;
var markers = [];
var markerCluster = null;
var sideBar = null;
var sideBarTotal = null;
var infoWindow = null;
var siteUrl = 'http://rivnefish.com';
var searchUrlPrefix =
    'http://rivnefish.com/wp-content/plugins/fish-map/fish_map_genxml.php'
var locationSelect = null;
var fishIcon = null;
var fishIconShadow = null;
var fishIconBig = null;
var fishIconShadowBig = null;

function initialize() {
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
        center: RivneLatLng, // Center map at Rivne
        panControl: true,
        scaleControl: true,
        //scaleControlOptions: {position: google.maps.ControlPosition.BOTTOM_LEFT},
        zoomControl: true,
        mapTypeControl: true,
        //mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
        streetViewControl: false,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: emphasizeLakesStyles
    //MapTypeId.ROADMAP displays the default road map view
    //MapTypeId.SATELLITE displays Google Earth satellite images
    //MapTypeId.HYBRID displays a mixture of normal and satellite views
    //MapTypeId.TERRAIN displays a physical map based on terrain information.
    };
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
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

    locationSelect = document.getElementById("locationSelect");
    locationSelect.onchange = function() {
        var markerNum = locationSelect.options[locationSelect.selectedIndex].value;
        if (markerNum != "none"){
            google.maps.event.trigger(markers[markerNum], 'click');
        }
    };
    // Init Marker Clusterer
    markerCluster = new MarkerClusterer(map);

    // Show all marker in the end of INITIALIZE()
    // TODO: experimental setupWeather();
    setupAllMarkers();
    // TryGeolocation();
}

/* END W3C Geolocation and Google Gears Geolocation */
function TryGeolocation() {
    // Try W3C Geolocation (Preferred)
    if(navigator.geolocation) {
        browserSupportFlag = true;
        navigator.geolocation.getCurrentPosition(function(position) {
            initialLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
            map.setCenter(initialLocation);
        }, function() {
            handleNoGeolocation(browserSupportFlag);
        });
    // Try Google Gears Geolocation
    } else if (google.gears) {
        browserSupportFlag = true;
        var geo = google.gears.factory.create('beta.geolocation');
        geo.getCurrentPosition(function(position) {
            initialLocation = new google.maps.LatLng(position.latitude,position.longitude);
            map.setCenter(initialLocation);
        }, function() {
            handleNoGeoLocation(browserSupportFlag);
        });
    // Browser doesn't support Geolocation
    } else {
        browserSupportFlag = false;
        handleNoGeolocation(browserSupportFlag);
    }
}

function handleNoGeolocation(errorFlag) {
    if (errorFlag == true) {
        initialLocation = RivneLatLng;
        console.log("Geolocation service failed.");
    } else {
        initialLocation = RivneLatLng;
        console.log("Your browser doesn't support geolocation. We've placed you in Siberia.");
    }
    map.setCenter(initialLocation);
}
/* END W3C Geolocation and Google Gears Geolocation */

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

function clearLocations() {
    infoWindow.close();
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }
    markers.length = 0;

    sideBar.innerHTML = "";
    sideBarTotal.innerHTML = "";

    locationSelect.innerHTML = "";
    var option = document.createElement("option");
    option.value = "none";
    option.innerHTML = "See all results:";
    locationSelect.appendChild(option);
    
    // Clear Marker Clusterer
    markerCluster.clearMarkers();
}

function searchLocationsNear(center) {
    clearLocations();
    map.setCenter(center);

    var radius = document.getElementById('radiusSelect').value;
    var searchUrl = searchUrlPrefix + '?action=search&lat=' + center.lat() + '&lng=' + center.lng() + '&radius=' + radius;
    downloadUrl(searchUrl, function(data) {
        var xml = parseXml(data);
        var markerNodes = xml.documentElement.getElementsByTagName("marker");
        var bounds = new google.maps.LatLngBounds();
        for (var i = 0; i < markerNodes.length; i++) {
            var id = markerNodes[i].getAttribute("marker_id");
            var name = markerNodes[i].getAttribute("name");
            var address = markerNodes[i].getAttribute("address");
            var distance = parseFloat(markerNodes[i].getAttribute("distance"));
            var latlng = new google.maps.LatLng(
                parseFloat(markerNodes[i].getAttribute("lat")),
                parseFloat(markerNodes[i].getAttribute("lng")));

            createOption(name, distance, i);
            createMarker(latlng, name, address, id);
            bounds.extend(latlng);
        }

        if (markerNodes.length > 3) {
            // Sets the viewport to contain the given bounds.
            map.fitBounds(bounds);
        }
        countSideBar(); //window.setTimeout(countSideBar, 0);
        
        // Refresh Marker Clusterer
        markerCluster.addMarkers(markers);

        locationSelect.style.visibility = "visible";
        locationSelect.onchange = function() {
            var markerNum = locationSelect.options[locationSelect.selectedIndex].value;
            google.maps.event.trigger(markers[markerNum], 'click');
        };
    }); // End downloadUrl()
}

function setupAllMarkers () {
    clearLocations();
    map.setCenter(RivneLatLng);

    var searchUrl = searchUrlPrefix + '?action=show&lat=' + RivneLatLng.lat() + '&lng=' + RivneLatLng.lng();
    downloadUrl(searchUrl, function(data) {
        var xml = parseXml(data);
        var markerNodes = xml.documentElement.getElementsByTagName("marker");
        var bounds = new google.maps.LatLngBounds();
        for (var i = 0; i < markerNodes.length; i++) {
            var id = markerNodes[i].getAttribute("marker_id");
            var name = markerNodes[i].getAttribute("name");
            var address = markerNodes[i].getAttribute("address");
            var latlng = new google.maps.LatLng(
                parseFloat(markerNodes[i].getAttribute("lat")),
                parseFloat(markerNodes[i].getAttribute("lng")));

            createMarker(latlng, name, address, id);
            bounds.extend(latlng);
        }
        // Sets the viewport to contain the given bounds. Rather unnecessary
//        if (markerNodes.length > 3) {
//            // Sets the viewport to contain the given bounds.
//            map.fitBounds(bounds);
//        }

        countSideBar();    //window.setTimeout(countSideBar, 0);

        // Refresh Marker Clusterer
        markerCluster.addMarkers(markers);

    }); // End downloadUrl()
}

function createMarker(latlng, name, address, id) {
    var marker = new google.maps.Marker({
        map: map,
        position: latlng,
        title: name,
        icon: fishIcon,
        shadow: fishIconShadow,
        id: id
    });

    google.maps.event.addListener(marker, 'click', function() {
        showInfoWindow(marker);
    });
    addToSideBar(marker, name);
    markers.push(marker);
}

function openInfoWindow(marker, html) {
    infoWindow.setContent(html);
    infoWindow.open(map, marker);
  }

function createOption(name, distance, num) {
    var option = document.createElement("option");
    option.value = num;
    option.innerHTML = name + " - " + distance.toFixed(2) + " km";
    locationSelect.appendChild(option);
}

function downloadUrl(url, callback) {
    var request = window.ActiveXObject ?
    new ActiveXObject('Microsoft.XMLHTTP') :
    new XMLHttpRequest;

    request.onreadystatechange = function() {
        if (request.readyState == 4) {
            request.onreadystatechange = doNothing;
            callback(request.responseText, request.status);
        }
    };

    request.open('GET', url, true);
    request.send(null);
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
var fish_scores = new Array(
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
); // condensed array

function showInfoWindow(marker) {
    var searchUrl = searchUrlPrefix + '?action=info&marker_id=' + marker.id;
    downloadUrl(searchUrl, function(data) {
        var xml = parseXml(data);
        // Create marker
        var markerNode = xml.documentElement.getElementsByTagName("marker")[0];
        var name = markerNode.getAttribute("name");
        var payment = markerNode.getAttribute("paid_fish") ? markerNode.getAttribute("paid_fish") : "-";
        var contact = markerNode.getAttribute("contact") ? markerNode.getAttribute("contact") : "-";
        var photo1 = markerNode.getAttribute("photo_url1");
        var photo2 = markerNode.getAttribute("photo_url2");
        var photo3 = markerNode.getAttribute("photo_url3");
        var photo4 = markerNode.getAttribute("photo_url4");
        var url_suffix = markerNode.getAttribute("url_suffix");

        var html = "<table class='markers'>" +
        "<tr>"+
        "<th>"+ name + "</th>"+
        "</tr>";

        // Add fishes
        var fishNodes = xml.documentElement.getElementsByTagName("fish");
        if (fishNodes) {
            html += "<tr><td><i>Риба: </i>";
            for (var i = 0; i < fishNodes.length; i++) {
                var fish_name = fishNodes[i].getAttribute("name");
                var article_url = fishNodes[i].getAttribute("article_url");
                var icon_url = fishNodes[i].getAttribute("icon_url");
                var icon_width = fishNodes[i].getAttribute("icon_width");
                var icon_height = fishNodes[i].getAttribute("icon_height");
                var weight_avg = fishNodes[i].getAttribute("weight_avg") ? new Number(fishNodes[i].getAttribute("weight_avg")) : "- ";
                var weight_max = fishNodes[i].getAttribute("weight_max") ? new Number(fishNodes[i].getAttribute("weight_max")) : "- ";
                var amount = fishNodes[i].getAttribute("amount") ? fishNodes[i].getAttribute("amount") : "-";
                if (article_url){
                    html += "<a href='"+article_url+"' >";
                }
                html += "<img class='fish_icon' style='width:"+icon_width+
                    "px; height:"+icon_height+
                    "px' src='"+icon_url+
                    "' alt='"+fish_name+
                    "' title='"+fish_name+
                        ", середня вага: "+weight_avg+
                        "гр, максимальна "+weight_max+
                        "гр, кльов "+amount+"/10"+
                    "' />";
                if (article_url){
                    html += "</a>";
                }
                // Add score image if necessary
                var score = parseInt(amount);
                if (score) {
                html += "<img class='fish_score' style='width:3px; height:28px'"+
                    " src='"+fish_scores[score-1]+
                    "' alt='"+score+
                    "' />";
                }
                /*
                var brief_info = "<span class='brief-info'>C:"+
                    (weight_avg == '- ') ? '- ' : (weight_avg/1000).toFixed(1)+"гр,M:"+
                    (weight_max == '- ') ? '- ' : (weight_max/1000).toFixed(1)+"гр,"+
                    amount+"/10</span>";
                html += "<br/>" + brief_info;
                */
            }
            html += "</td></tr>";
        }

        html += "<tr>"+
        "<td><i>Оплата: </i>"+ payment + "</td>"+
        "</tr>"+
        "<tr>"+
        "<td><i>Контакт: </i>"+ contact + "</td>"+
        "</tr>";

        if (photo1 || photo2 || photo2 || photo4) {
            html += "<tr><td>";
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
            html += "</td></tr>";
        }
        if (url_suffix) {
            html += "<tr><td>";
            html += "<a title='Прочитати статтю про цю точку, переглянути/додати коментарі'"+
                     " href='"+siteUrl+url_suffix+"'>Деталі/Коментарі &gt;&gt;&gt;</a>";
            html += "</td></tr>";
        }
        html += "</table>";

        openInfoWindow(marker, html);
    }); // End downloadUrl()
}

function scaled_url(str) {
    /* Create PicasaWeb URL with scale 's53' */
    var clear_str = str.replace(/\/s\d{1,4}\//,'/'); // remove '/s1200/' scale mark if exist
    if (clear_str)
        str = clear_str;
    var scale = str.substr(str.lastIndexOf('/')); // e.g. /Bochanitsa_I.JPG
    res = str.replace(scale, '/s53'+scale);
    return res
}

function countSideBar() {
    var cnt = sideBar.childNodes.length; /*without Rivne*/
    var label = document.createElement("p");
    label.style.fontStyle = "italic";
    label.innerHTML = "Всього водойм:&nbsp;" + cnt;
    sideBarTotal.appendChild(label);
  }

function addToSideBar(marker, caption) {
    var label = document.createElement("a");
    if (caption) {
      label.innerHTML = caption;
    } else {
      label.innerHTML = marker.title;
    }
    label.href = "#";
    label.style.display = "block";
    label.style.textDecoration="none";
    label.title = "Клацніть двічі для центрування";
    label.onclick = function(){
        google.maps.event.trigger(marker, 'click');
        return false
        };
    label.ondblclick = function()
      {map.setCenter(marker.getPosition());return false;};

    /* START. Code that inserts LABEL to the sideBar in alphabetical order */
    var a = new Array();
    for(var i = 0; i < sideBar.childNodes.length; i++) {
      a[i] = sideBar.childNodes[i].innerHTML;
    }
    a.push(caption);
    a.sort();
    var j = 0;
    for(var i = 0; i < a.length; i++) {
        if(a[i] == caption) {j = i;break;}
    }

    if (sideBar.childNodes[j]) {
      sideBar.insertBefore(label, sideBar.childNodes[j]);
    } else {
      sideBar.appendChild(label);
    }
    /* END. Alphabetical code */
    /*sideBar.appendChild(label);*/

    google.maps.event.addListener(marker,"click",
      function() {
        label.focus();
        return false
      });
  }

jQuery(document).ready(function() {
    initialize();
});

function WeatherControl(controlDiv) {

  // Set CSS styles for the DIV containing the control
  // Setting padding to 5 px will offset the control
  // from the edge of the map
  controlDiv.style.padding = '5px';

  // Set CSS for the control border
  var controlUI = document.createElement('div');
  controlUI.style.backgroundColor = 'white';
  controlUI.style.borderStyle = 'solid';
  controlUI.style.borderWidth = '2px';
  controlUI.style.cursor = 'pointer';
  controlUI.style.textAlign = 'center';
  controlUI.title = 'Click to set the map to Home';
  controlDiv.appendChild(controlUI);

  // Set CSS for the control interior
  var controlText = document.createElement('div');
  controlText.style.fontFamily = 'Arial,sans-serif';
  controlText.style.fontSize = '12px';
  controlText.style.paddingLeft = '4px';
  controlText.style.paddingRight = '4px';
  controlText.innerHTML = '<b>Home</b>';
  controlUI.appendChild(controlText);

  // Setup the click event listeners: simply set the map to
  // Chicago
  google.maps.event.addDomListener(controlUI, 'click', function() {
    weatherLayer.setMap(map);
    cloudLayer.setMap(map);
  });

}

function setupWeather() {
    weatherLayer = new google.maps.weather.WeatherLayer({
        temperatureUnits: google.maps.weather.TemperatureUnit.CELSIUS,
        windSpeedUnits: google.maps.weather.WindSpeedUnit.METERS_PER_SECOND
    });
    // weatherLayer.setMap(map);

    cloudLayer = new google.maps.weather.CloudLayer();
    // cloudLayer.setMap(map);
    
    var weatherControlDiv = document.createElement('div');
    var weatherControl = new WeatherControl(weatherControlDiv);
    weatherControlDiv.index = 1;
    map.controls[google.maps.ControlPosition.TOP_RIGHT].push(weatherControlDiv);
}