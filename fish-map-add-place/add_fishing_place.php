<?php
/*
Plugin Name: Add Fishing Place
Plugin URI:
Description: This plugin allows adding fishing places to the map.
             Integrates into the page/post by using shortcode [add-fish-place].
Version: 0.1
Author: Yaroslav Hrabar
Author URI: http://rivnefish.com
License: BSD
Created: 29 January 2013
*/

require_once 'add_fishing_place_model.php';
require_once 'add_fishing_place_views.php';

function add_scripts_fish_place() {
    wp_deregister_script('google-map');
    wp_register_script('google-map', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCByg67-8HjM_17CVdq9iOiN95Nhz7izCw&sensor=false&language=uk');
    wp_enqueue_script('google-map');

    wp_deregister_script('jquery');
    wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');
    wp_enqueue_script('jquery');

    wp_deregister_script('jquery-ui');
    wp_register_script('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js');
    wp_enqueue_script('jquery-ui');

    wp_register_script('add-fish-place', plugins_url('fish-map-add-place/js/add_fish_place.js'));
    wp_enqueue_script('add-fish-place');
}

function add_stylesheets_fish_place() {
    /* !!! TODO: update Post's TITLE and ID in case changed*/
    wp_register_style('addFishPlaceStyleSheet', plugins_url('fish-map-add-place/css/add_fish_place.css'));
    wp_enqueue_style('addFishPlaceStyleSheet');
}

function add_fish_place($attr) {
    global $user_login;
    add_scripts_fish_place();
    add_stylesheets_fish_place();

    $attr = shortcode_atts(array('id' => 'add-fish-place'), $attr);
    add_fish_place_form(
        get_last_marker_id(),
        $user_login,
        get_fishes_for_view(),
        get_countries_for_view(),
        get_regions_for_view(),
        get_districts_for_view()
    );
}

add_shortcode('add-fish-place', 'add_fish_place');