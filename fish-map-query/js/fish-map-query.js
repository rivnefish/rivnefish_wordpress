/**
 * JavaScript code
 *
 **/

var queryUrlPrefix =
    'http://rivnefish.com/wp-content/plugins/fish-map-query/fish_map_query_json.php';
var queryUrlDetailsPrefix =
    'http://rivnefish.com/wp-content/plugins/fish-map-query/fish_map_query_html.php';
var fishMapForm = '#fish_map_form';

function init_query() {

	$('#search_submit').click(function() {

		$('#waiting').show('fast');
        clear_errors();
		$('#fish_map_table').hide('fast');
        $('#fish_map_table_count').hide('fast');
        $('#fontsizer').hide('fast');

		$.ajax({
			type : 'GET',
			url : queryUrlPrefix,
			dataType : 'json', // json in case error and html otherwise
			data: $(fishMapForm).serializeArray(),
			success : function(data, textStatus, jqXHR){
                if (data.error){
                    $('#fish_map_table').addClass('error');
                    $('#fish_map_table').html(data.msg).show('fast');
                    if (data.id) {
                        $('#'+data.id).addClass('error');
                    }
                } else {
                    $('#fish_map_table').html(data.rows).show('fast');
                    $('#fish_map_table_count').html(
                        "Знайдено водойм: " + data.count).show('fast');
                    $('#form_params').hide('fast');
                    $('#fontsizer').show('fast');
                    // Turn On sorting for the resulted table
                    window.setTimeout(function() {
                        $("#fish_table_results").tablesorter({
                            headers: {1: {sorter: false},
                                      3: {sorter: false},
                                      4: {sorter: false},
                                      5: {sorter: false},
                                      6: {sorter: false}},
                            widthFixed: true
                        });
                    }, 500);
                    // Turn On Truncate Content column
                    window.setTimeout(function() {
                        $(".column_content").jTruncate({
                            length: 250,
                            minTrail: 20,
                            moreText: "[показати]",
                            lessText: "[сховати]",
                            ellipsisText: "..."
                        });
                    }, 500);
                    // Init tooltips for Note image
                    window.setTimeout(function() {
                        $('img[id$="_note"][alt="Note"]').tooltip({
                            delay: 0,
                            track: false,
                            showURL: false,
                            showBody: '|'
                        });
                    }, 500);
                }
			},
			error : function(jqXHR, textStatus, errorThrown) {
				$('#fish_map_table').addClass('error')
					.text('Помилка пошуку рибних водойм.').show('fast');
			},
            complete : function(jqXHR, textStatus) {
                $('#waiting').hide('fast');
            }
		});

		return false;
	});
}

function clear_errors() {
    $('#fish_map_table').removeClass('error');
    // Clear main params form
    $('#form_params_table').find('input').removeClass('error');
    // Clear additional params form
    $('#form_add_params_table').find('input').removeClass('error');
}

function toggleFishDetails(id) {
    var row_element = '#fish_table_details_row_'+id;
    var element = '#fish_table_details_'+id;
    $(element).load(queryUrlDetailsPrefix, {'marker_id': id});
    $(row_element).toggle('slow');
}

function initRegions(country_obj) {
    if (country_obj.selected)
        $('#country_'+country_obj.value).show();
    else
        $('#country_'+country_obj.value).hide();
}
function initDistricts(region_obj) {
    if (region_obj.selected)
        $('#region_'+region_obj.value).show();
    else
        $('#region_'+region_obj.value).hide()
}

function addMarkersComment(name) {
    $('#comment').html("<b>Зауваження щодо водойми '" +name+ "':</b>");
    $('#comment').focus();
}

function initFormTooltips() {
    $('img[id$="_tip"]').tooltip({
        delay: 0,
        //fade: 250,
        track: false,
        //opacity: 1,
        showURL: false,
        showBody: '|'
    });
}

$(document).ready(function() {
    init_query();
    initFormTooltips();
    $('#fontsizer').jfontsizer({
        applyTo: '#fish_table_results',
        changesmall: '2',
        changelarge: '2',
        expire: 30});
});
