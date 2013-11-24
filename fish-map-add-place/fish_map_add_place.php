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

require_once 'includes/fish_map_install.php';
require_once 'includes/marker_model.php';

class FishMapAddPlacePlugin
{
    private $_model;

    public function __construct()
    {
        $path = WP_PLUGIN_DIR . '/fish-map-add-place';
        register_activation_hook($path . '/fish_map_add_place.php', 'fish_map_install');
        register_deactivation_hook($path . '/fish_map_add_place.php', 'fish_map_uninstall');

        add_action( 'plugins_loaded', 'fish_map_update_check' );

        add_action('wp_ajax_fish_map_add_place_save', array($this, 'savePlace'));
        add_shortcode('fish-map-add-place-from', array($this, 'renderForm'));

        $this->_model = new MarkerModel();
    }

    public function addScripts()
    {
        wp_deregister_script('google-map');
        wp_register_script('google-map', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCByg67-8HjM_17CVdq9iOiN95Nhz7izCw&sensor=false&language=uk');
        wp_enqueue_script('google-map');

        wp_register_script('jquery.blockui', plugins_url('js/3p/jquery.blockUI.js', __FILE__));
        wp_enqueue_script('jquery.blockui');

        wp_register_script('jquery.scrollTo', plugins_url('js/3p/jquery.scrollTo.js', __FILE__));
        wp_enqueue_script('jquery.scrollTo');

        wp_register_script('add-fish-place', plugins_url('js/add_fish_place.js', __FILE__));
        wp_enqueue_script('add-fish-place');
    }

    public function addStylesheets()
    {
        /* !!! TODO: update Post's TITLE and ID in case changed*/
        wp_register_style('addFishPlaceStyleSheet', plugins_url('css/add_fish_place.css', __FILE__));
        wp_enqueue_style('addFishPlaceStyleSheet');
    }

    public function renderForm($attr)
    {
        $this->addScripts();
        $this->addStylesheets();

        $fishes = $this->_model->getFishes();
        include 'tpls/add_place_form.phtml';
    }

    public function savePlace()
    {
        $validator = $this->_model->validator($_POST);
        if ($validator->validate()) {
            $result = $this->_model->insertMarker($_POST);
            $this->_model->sendEmailNotification('INSERT', $_POST, $_GET);
            $response = array('error' => false);
        } else {
            $response = array(
                'error' => true,
                'errors' => $validator->errors()
            );
        }
        echo json_encode($response);
        die();
    }
}

$fishMapAddPlacePlugin = new FishMapAddPlacePlugin();