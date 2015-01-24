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

        this.pictures = $('#pictures');

        this.initMap();
        this.initPhotoUpload();
        this.initQTip();

        $('#permit').change(this.togglePermitInfo).trigger('change');
        $('.fishes input:checkbox').change(this.toggleFishAmount).trigger('change');
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

    initPhotoUpload : function () {
        if (this._shouldUseFlash() && !this._isFlashEnabled()) {
            $('#upload_container').text(
                'Завантаження не підтримується. Встановіть новішу версію переглядача ' +
                'або Flash plugin.'
            );
            return;
        }

        var uploadingCnt = 0;
        var uploader = new plupload.Uploader({
            runtimes : 'html5,flash,silverlight',
            browse_button : 'photo_upload',
            container : 'upload_container',
            max_file_size : '10mb',
            url : '/wp-admin/admin-ajax.php?action=save_photos',
            flash_swf_url : '/wp-content/plugins/fish-map/js/3p/plupload-2.1.1/Moxie.swf',
            silverlight_xap_url : '/wp-content/plugins/fish-map/js/3p/plupload-2.1.1/Moxie.xap',
            filters : [
                {title : "Малюнки", extensions : "jpg"}
            ],
            resize : {width : 1024, height : 1024, quality : 90}
        });

        uploader.bind('Init', function(up, params) {
            $('#filelist').html("<div>Current runtime: " + params.runtime + "</div>");
        });

        uploader.init();

        uploader.bind('FilesAdded', function(up, files) {
            uploader.start();
            up.refresh();
            $('#loading').show();
            uploadingCnt += files.length;
        });

        uploader.bind('Error', function(up, err) {
            alert("Помилка: " + err.message + (err.file ? ", Файл: " + err.file.name : ""));
            up.refresh();
        });

        uploader.bind('FileUploaded', $.proxy(function(up, file, info) {
            var response = JSON.parse(info.response),
                imageId = response.arrImageIds[0],
                imagePath = '/' + response.strGalleryPath + '/' + response.arrImageNames[0];
            this.pictures.append(
                $('<img />').attr({
                    'src' : imagePath,
                    'class' : 'photo'
                })
            ).append(
                $('<input />').attr({
                    'name' : 'pictures[]',
                    'type' : 'hidden',
                    'value' : imageId
                })
            );

            if (--uploadingCnt == 0) {
                $('#loading').hide();
            }
        }, this));
        this.uploader = uploader;
    },

    initQTip : function () {
        var position_right = {
                my: 'center left',
                at: 'center right'
            },
            position_bottom = {
                my: 'top center',
                at: 'bottom center'
            };
        $('.qtip-info').each(function(){
            $(this).qtip({
                content: { attr: 'data-qtip' },
                position: $(this).hasClass('qtip-bottom') ? position_bottom : position_right,
                hide: { fixed: true, delay: 200 },
                style: { classes: 'qtip-rounded' }
            })
        });
    },

    _shouldUseFlash : function () {
        return $.browser.msie && parseInt($.browser.version) < 10;
    },

    _isFlashEnabled: function () {
        var hasFlash = false;
        try {
            var fo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
            if(fo) hasFlash = true;
        } catch(e) {
            if (navigator.mimeTypes ["application/x-shockwave-flash"] != undefined) hasFlash = true;
        }
        return hasFlash;
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

    toggleFishAmount: function () {
        var amountInput = $(this).closest('.fish').find('.fish-amount');
        amountInput.toggle(this.checked).prop('disabled', !this.checked);
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
                    $('#add_place_result').show('fast');
                    $('#add_place_result').find('a#view_place').attr("href", data.permalink);
                    this.form.hide();
                    this.resetForm();
                    $('body').scrollTo('#content', 200, {offset: -50});
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
        this.pictures.html('');
        $('.fishes input:checkbox').trigger('change');
    }
};})(jQuery);

jQuery(document).ready(function($) {
    AddMarkerForm.init();
});
