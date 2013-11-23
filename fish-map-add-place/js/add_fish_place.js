var map = null;
var marker = null;

function initMap() {
    // Стилі для карти. Можна виділяти кольором водойми, парки, траси і т.п.
    var emphasizeLakesStyles = [{
        featureType: "water",
        stylers: [
            {lightness: -30},
            {saturation: 41}
        ]
    }];

    var RivneLatLng = new google.maps.LatLng(50.619616, 26.251379);
    map = new google.maps.Map(document.getElementById("map_canvas"), {
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
    });

    var fishIcon = new google.maps.MarkerImage(
        "https://lh4.googleusercontent.com/_AlLFR-j5gDI/TXeEWPwQfkI/AAAAAAAABEQ/C1wZSANaCeg/s800/float_fish_16x47_new.png",
        new google.maps.Size(16,47),
        new google.maps.Point(0,0),
        new google.maps.Point(0,47));
    var fishIconShadow = new google.maps.MarkerImage(
        "https://lh6.googleusercontent.com/_AlLFR-j5gDI/TXeEddrdM9I/AAAAAAAABEY/oipNS7GnUb0/s800/float_fish_shadow_56x47_new.png",
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
    $('input[name=lat]').val(position.lat());
    $('input[name=lng]').val(position.lng());
}

function savePlace() {
    clear_errors();
    $('#add_place_result').hide('fast');

    var form = $(this);
    form.block({ message: 'Збереження...' })
    $.ajax({
        type : 'POST',
        url : form.attr('action'),
        dataType : 'json',
        data : form.serialize(),
        success : function (data) {
            form.unblock();
            if (data.error){
                var i = 0;
                $.each(data.errors, function (field, messages) {
                    var field = $('#marker_' + field + ', .marker_' + field);
                    field.addClass('error');
                    var divMessages = $("<div></div>").addClass('error_message')
                        .html(messages.join("<br/>"));
                    field.after(divMessages);
                    if (i++ == 0) {
                        $('body').scrollTo(field, 200, {offset: -50});
                    }
                });
            } else {
                $('#add_place_result').html(data.result).show('fast');
                $('#add_place_result').addClass('success');
                form.hide();
                form[0].reset();
                marker.setMap(null);
            }
        },
        error : function () {
            form.unblock();
            alert('Помилка додавання рибної точки.');
        }
    });

    return false;
}

function clear_errors() {
    $('.error').removeClass('error');
    $('.error_message').remove();
}

jQuery(document).ready(function($) {
    initMap();
    $('#add_opts').click(function () {
        $('#additional_opts').toggle();
    });
    $('#add_place_form').submit(savePlace);
});