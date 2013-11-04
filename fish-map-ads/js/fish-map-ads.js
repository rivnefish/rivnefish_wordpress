/**
 * JavaScript code
 *
 **/

var showNextAdPrefix =
'http://rivnefish.com/wp-content/plugins/fish-map-ads/fish_map_ads_html.php';
var adsFormWrapper = "#ads_wrapper";

function showNextAd(id) {

    $.ajax({
        url : showNextAdPrefix,
        type: "POST",
        data: {'ad_id' : id},
        dataType: "html",
        success : function(data){
            $(adsFormWrapper).html(data);
        }
    });

    // Simple alternative
    // $(adsFormWrapper).load(showNextAdPrefix, {'ad_id': id});
}