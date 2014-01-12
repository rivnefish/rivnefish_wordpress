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

function add_scripts_filter() {
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

function add_stylesheets_filter() {
    wp_register_style('fishQueryStyleSheet', plugins_url('css/fish_map_query.css', __FILE__));
    wp_enqueue_style('fishQueryStyleSheet');

    wp_register_style('greenQueryStyleSheet', plugins_url('css/tablesorter.css', __FILE__));
    wp_enqueue_style('greenQueryStyleSheet');

    wp_register_style('pagerStyleSheet', plugins_url('css/jquery.tablesorter.pager.css', __FILE__));
    wp_enqueue_style('pagerStyleSheet');

    wp_register_style('queryTableFontsizer', plugins_url('css/jquery.jfontsizer.css', __FILE__));
    wp_enqueue_style('queryTableFontsizer');
}

function fish_map_query($attr) {
    require_once 'fish_map_query_model.php';
    require_once 'fish_map_query_views.php';

    add_scripts_filter();
    add_stylesheets_filter();

    $fishes = get_fishes_for_view();
    fish_map_query_form($fishes);
}

add_shortcode('filter', 'fish_map_query');