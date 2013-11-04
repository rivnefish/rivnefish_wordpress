<?php
/*
Plugin Name: Fish Map DB Query
Plugin URI:
Description: This plugin allow querying Markers DB.
             Integrates into the page/post by using shortcode [map-query].
Version: 0.1
Author: Yaroslav Hrabar
Author URI: http://rivnefish.com
License: BSD
Created: 7 November 2011
TODO: rewrite according to http://www.garyc40.com/2010/03/5-tips-for-using-ajax-in-wordpress/
*/

// Add the Google Maps API and JQuery headers
add_action('wp_enqueue_scripts', 'add_scripts_filter');
add_action('wp_print_styles', 'add_stylesheets_filter');

function add_scripts_filter() {
    if (is_page('Пошук рибних водойм') || is_page(673)) {
        /* !!! TODO: update Post's TITLE and ID in case changed*/
        wp_deregister_script('jquery');
        wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
        wp_enqueue_script('jquery');

        wp_deregister_script('jquery-ui');
        wp_register_script('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js');
        wp_enqueue_script('jquery-ui');

        wp_register_script('fish-map-query', plugins_url('js/fish-map-query.js', __FILE__));
        wp_enqueue_script('fish-map-query');
        wp_register_script('fish-map-tablesorter', plugins_url('js/jquery.tablesorter.min.js', __FILE__));
        wp_enqueue_script('fish-map-tablesorter');
        wp_register_script('fish-map-pager', plugins_url('js/jquery.tablesorter.pager.js', __FILE__));
        wp_enqueue_script('fish-map-pager');
        wp_register_script('fish-map-truncate', plugins_url('js/jquery.jtruncate.min.js', __FILE__));
        wp_enqueue_script('fish-map-truncate');
        wp_register_script('fish-map-fontsizer', plugins_url('js/jquery.jfontsizer.min.js', __FILE__));
        wp_enqueue_script('fish-map-fontsizer');
    }
}

function add_stylesheets_filter() {
    if (is_page('Пошук рибних водойм') || is_page(673)) {
        wp_register_style('fishQueryStyleSheet', plugins_url('css/fish_map_query.css', __FILE__));
        wp_enqueue_style('fishQueryStyleSheet');

        wp_register_style('greenQueryStyleSheet', plugins_url('css/tablesorter.css', __FILE__));
        wp_enqueue_style('greenQueryStyleSheet');

        wp_register_style('pagerStyleSheet', plugins_url('css/jquery.tablesorter.pager.css', __FILE__));
        wp_enqueue_style('pagerStyleSheet');

        wp_register_style('queryTableFontsizer', plugins_url('css/jquery.jfontsizer.css', __FILE__));
        wp_enqueue_style('queryTableFontsizer');
    }
}

function fish_map_query($attr) {

    // default atts
    $attr = shortcode_atts(array(
        'lat' => '0',
        'lon' => '0',
        'id' => 'map',
        'z' => '1',
        'w' => '400',
        'h' => '300',
        'maptype' => 'ROADMAP',
        'address' => '',
        'kml' => '',
        'kmlautofit' => 'yes',
        'marker' => '',
        'markerimage' => '',
        'traffic' => 'no',
        'bike' => 'no',
        'fusion' => '',
        'start' => '',
        'end' => '',
        'infowindow' => '',
        'infowindowdefault' => 'yes',
        'directions' => '',
        'hidecontrols' => 'false',
        'scale' => 'false',
        'scrollwheel' => 'true'
            ), $attr);

    require_once 'fish_map_query_model.php';
    require_once 'fish_map_query_views.php';

    $fishes = get_fishes_for_view();
    $countries = get_countries_for_view();
    $regions = get_regions_for_view();
    $districts = get_districts_for_view();
    $return_body = fish_map_query_form($fishes, $countries, $regions, $districts);
    return $return_body;
}

add_shortcode('filter', 'fish_map_query');