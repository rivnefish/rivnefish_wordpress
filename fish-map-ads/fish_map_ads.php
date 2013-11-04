<?php

/*
  Plugin Name: Fish Map Advertisement
  Plugin URI:
  Description: Advertisement Plugin. Provides *shortcode* [advertisement] that can be inserted into
      page, post, widget etc. Provides *Page* for inserting/editing/deleting advertisements.
      Shortcode usage [advertisement] or [advertisement width=260]
  Version: 0.1
  Author: Yaroslav Hrabar
  Author URI: http://rivnefish.com
  License: BSD
  Created: 23 February 2012
 */

add_action('wp_enqueue_scripts', 'add_scripts_ads');
add_action('wp_print_styles', 'add_stylesheets_ads');
// Hook for adding admin menu: visible for ADMIN and EDITOR users
add_action('admin_menu', 'add_ads_menu');
// Shortcode
add_shortcode('advertisement', 'ads_page_shortcode');
// Load ads CSS for Advertisement page
add_action('admin_head', 'ads_admin_head');

function add_scripts_ads() {
    // Load jQuery if not loaded yet
    wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
    wp_enqueue_script('jquery');

    wp_register_script('fish-map-ads', plugins_url('js/fish-map-ads.js', __FILE__));
    wp_enqueue_script('fish-map-ads');
}

function add_stylesheets_ads() {
    wp_register_style('adsStyleSheet', plugins_url('css/fish_map_ads.css', __FILE__));
    wp_enqueue_style('adsStyleSheet');
}

function ads_admin_head() {
    echo '<link rel="stylesheet" type="text/css" href="' . plugins_url('css/fish_map_ads.css', __FILE__). '">';
}

function add_ads_menu() {
    // Show 'Оголошення' menu only for
    //
    // Old: Advertisement menu: capability='publish_pages' means for EDITOR user
    // http://codex.wordpress.org/Roles_and_Capabilities
    //
    // New: user='subscriber', capability='edit_advertisement'
    // With aids of User Role Editor plug-in
    add_menu_page('Оголошення', 'Оголошення', 'edit_advertisement', 'fish-map-ads-handle', 'ads_page_insert',
            $icon_url = plugins_url('images/adsicon.png', __FILE__));
}

// Display content of the Insert Advertisement page
function ads_page_insert() {
    require_once 'fish_map_ads_db_manage.php';
    global $user_login;
    global $current_user;
    $can_see_all = user_can($current_user, 'edit_users');

    ads_db_manage_page($user_login, $can_see_all);
}

function ads_page_shortcode($attr) {
    // Default attribute width=260
    $attr = shortcode_atts(array(
        'width' => 260
            ), $attr);

    require_once 'fish_map_ads_model.php';
    require_once 'fish_map_ads_views.php';

    $ad = get_ad_for_view(NULL);
    $return_body = show_ad($ad, $attr);
    return $return_body;
}