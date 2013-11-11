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

require_once 'add_fishing_place_install.php';
require_once 'add_fishing_place_model.php';
require_once 'add_fishing_place_views.php';

define('FISH_MAP_ADD_PLACE_PLUGIN_PATH', WP_PLUGIN_DIR . '/fish-map-add-place');

register_activation_hook(FISH_MAP_ADD_PLACE_PLUGIN_PATH . '/add_fishing_place.php', 'add_fishing_place_install');
register_deactivation_hook(FISH_MAP_ADD_PLACE_PLUGIN_PATH . '/add_fishing_place.php', 'add_fishing_place_uninstall');

add_action('wp_ajax_fish_map_add_place_save', 'fish_map_add_place_save');
add_shortcode('fish-map-add-place-from', 'fish_map_add_place_form');

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

    wp_register_script('add-fish-place', plugins_url(FISH_MAP_ADD_PLACE_PLUGIN_PATH . '/js/add_fish_place.js'));
    wp_enqueue_script('add-fish-place');
}

function add_stylesheets_fish_place() {
    /* !!! TODO: update Post's TITLE and ID in case changed*/
    wp_register_style('addFishPlaceStyleSheet', plugins_url(FISH_MAP_ADD_PLACE_PLUGIN_PATH . '/css/add_fish_place.css'));
    wp_enqueue_style('addFishPlaceStyleSheet');
}

function fish_map_add_place_form($attr) {
    global $user_login;
    add_scripts_fish_place();
    add_stylesheets_fish_place();

    $attr = shortcode_atts(array('id' => 'add-fish-place'), $attr);
    add_fish_place_form(
        $user_login,
        get_fishes_for_view()
    );
}

function fish_map_add_place_save() {
    try {
        $result = insert_marker($_GET);
        $response = json_encode(array(
            'error' => false,
            'result' => $result
        ));
    } catch (IDException $exc) {
        $response = json_encode(array(
            'error' => true,
            'msg' => $exc->getMessage(),
            'id' => $exc->getId()
        ));
    }
    echo $response;
    die();
}