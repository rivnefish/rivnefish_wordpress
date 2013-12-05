var AddMarkerForm = (function ($) {
    return {
    init : function () {
        $('#add_opts').click(function () {
            $('#additional_opts').slideToggle();
            $(this).toggleClass('active');
        });
        $('#add_more').click($.proxy(this.addMore, this));

        this.form = $('#add_place_form');
        this.form.submit($.proxy(this.savePlace, this));

        this.initMap();

        $('#permit').change(this.togglePermitInfo).trigger('change');
    },

    initMap : function () {
        // Стилі для карти. Можна виділяти кольором водойми, парки, траси і т.п.
        var emphasizeLakesStyles = [{
            featureType: "water",
            stylers: [
                {lightness: -30},
                {saturation: 41}
            ]
        }];

        var RivneLatLng = new google.maps.LatLng(50.619616, 26.251379);
        this.map = new google.maps.Map(document.getElementById("map_canvas"), {
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

        this.marker = new google.maps.Marker({
            icon: fishIcon,
            shadow: fishIconShadow
        });

        google.maps.event.addListener(this.map, 'click', $.proxy(function(e) {
            this.placeMarker(e.latLng, this.map);
        }, this));
    },

    togglePermitInfo : function () {
        var contactControl = $('#marker_contact').parent(),
            paidControls = $('#marker_paid_fish, #time_to_fish, #marker_boat_usage').closest('.controls');

        if (this.value == 'paid') {
            paidControls.slideDown('fast');
        } else {
            paidControls.slideUp('fast');
        }

        if (this.value == 'paid' || this.value == 'prohibited') {
            contactControl.slideDown('fast');
        } else {
            contactControl.slideUp('fast');
        }
    },

    addMore : function (e) {
        $('#add_place_result').hide();
        this.form.show();
        return false;
    },

    placeMarker : function (position, map) {
        this.marker.setPosition(position);
        this.marker.setMap(map);
        $('input[name=lat]').val(position.lat());
        $('input[name=lng]').val(position.lng());
    },

    savePlace : function (e) {
        this.clearErrors();
        // $('#add_place_result').hide('fast');

        var form = $(e.target);
        this.form.block({ message: 'Збереження...' })
        $.ajax({
            type : 'POST',
            url : form.attr('action'),
            dataType : 'json',
            data : form.serialize(),
            context: this,
            success : function (data) {
                this.form.unblock();
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
                    this.form.hide();
                    this.resetForm();
                }
            },
            error : function () {
                form.unblock();
                alert('Помилка додавання рибної точки.');
            }
        });

        return false;
    },

    clearErrors : function () {
        $('.error').removeClass('error');
        $('.error_message').remove();
    },

    resetForm : function () {
        this.form[0].reset();
        this.marker.setMap(null);
        this.form.find('input[name=lat]').val('');
        this.form.find('input[name=lng]').val('');
    }
};})(jQuery);

jQuery(document).ready(function($) {
    AddMarkerForm.init();
});