/**
 * JavaScript code
 *
 **/

var addPlaceForm = '#add_place_form';

/* BEGIN Init Maps */
var map = null;
var RivneLatLng = new google.maps.LatLng(50.619616, 26.251379);
var fishIcon = null;
var marker = null;

function initialize() {
    // Стилі для карти. Можна виділяти кольором водойми, парки, траси і т.п.
    var emphasizeLakesStyles = [
        {
            featureType: "water",
            stylers: [
                {lightness: -30},
                {saturation: 41}
            ]
        }
    ];

    var myOptions = {
        zoom: 10,
        center: RivneLatLng, // Center map at Rivne
        panControl: true,
        scaleControl: true,
        zoomControl: true,
        mapTypeControl: true,
        mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
        streetViewControl: false,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: emphasizeLakesStyles
    };
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

    fishIcon = new google.maps.MarkerImage("https://lh4.googleusercontent.com/_AlLFR-j5gDI/TXeEWPwQfkI/AAAAAAAABEQ/C1wZSANaCeg/s800/float_fish_16x47_new.png",
        new google.maps.Size(16,47),
        new google.maps.Point(0,0),
        new google.maps.Point(0,47));
    var fishIconShadow = new google.maps.MarkerImage("https://lh6.googleusercontent.com/_AlLFR-j5gDI/TXeEddrdM9I/AAAAAAAABEY/oipNS7GnUb0/s800/float_fish_shadow_56x47_new.png",
        new google.maps.Size(56,47),
        new google.maps.Point(0,0),
        new google.maps.Point(0,47));

    marker = new google.maps.Marker({
        icon: fishIcon,
        shadow: fishIconShadow
    });

    google.maps.event.addListener(map, 'click', function(e) {
        placeMarker(e.latLng, map);
    });
}

function placeMarker(position, map) {
  marker.setPosition(position);
  marker.setMap(map);
  // Fill inputs for latitude and longitude
  $('#column_marker_lat').val(position.lat());
  $('#column_marker_lng').val(position.lng());
}
/* END Init Maps */

function init_add_place_ajax() {
    $('#add_place_form').submit(function() {
        $('#waiting').show('fast');

        clear_errors();
        $('#add_place_result').hide('fast');
        var form = $(this);

        $.ajax({
            type : 'GET',
            url : form.attr('action'),
            dataType : 'json',
            data : form.serialize(),
            success : function(data, textStatus, jqXHR){
                if (data.error){
                    $('#add_place_result').addClass('error');
                    $('#add_place_result').html(data.msg).show('fast');
                    if (data.id) {
                        $('#'+data.id).addClass('error');
                    }
                } else {
                    $('#add_place_result').html(data.result).show('fast');
                    $('#add_place_result').addClass('success');
                }
            },
            error : function(jqXHR, textStatus, errorThrown) {
                $('#add_place_result').addClass('error')
                    .text('Помилка додавання рибної точки.').show('fast');
            },
            complete : function(jqXHR, textStatus) {
                $('#waiting').hide('fast');
            }
        });

        return false;
    });
}

function toggleFishDetails(id) {
    var row_element = '#fish_table_details_row_'+id;
    var element = '#fish_table_details_'+id;
    $(element).load(queryUrlDetailsPrefix, {'marker_id': id});
    $(row_element).toggle('slow');
}

function initRegions(country_obj) {
    $("optgroup[id^='country_'][id!='country_"+country_obj.value+"']").hide();
    $("optgroup[id='country_"+country_obj.value+"']").show();
    //if (country_obj.selected)
    //    $('#country_'+country_obj.value).show();
    //else
    //    $('#country_'+country_obj.value).hide();
}

function hideRegions(country_obj) {
    $("optgroup[id^='country_']").hide();
    $("optgroup[id^='region_']").hide();
}

function initDistricts(region_obj) {
    $("optgroup[id^='region_'][id!='region_"+region_obj.value+"']").hide();
    $("optgroup[id='region_"+region_obj.value+"']").show();
    //if (region_obj.selected)
    //    $('#region_'+region_obj.value).show();
    //else
    //    $('#region_'+region_obj.value).hide()
}

function hideDistrict(region_obj) {
    $("optgroup[id^='region_']").hide();
}

function initFormTooltips() {
    $('img[id$="_tip"]').tooltip({
        delay: 0,
        track: false,
        showURL: false,
        showBody: '|'
    });
}

function clear_errors() {
    $('#add_place_result').removeClass('error');
    $('#form_params').find('input').removeClass('error');
}

$(document).ready(function() {
    initialize();
    init_add_place_ajax();
    initFormTooltips();
    $('#add_opts').change(function () {
        $('#additional_opts').toggle(this.checked);
    })
});