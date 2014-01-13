<?php
/*
Plugin Name: Fish Map Add Fishing Place
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

        add_action('plugins_loaded', 'fish_map_update_check' );
        add_action('wp_ajax_fish_map_add_place_save', array($this, 'savePlace'));
        add_action('admin_post_save_photos', array($this, 'savePhotos'));
        add_action('init', array($this, 'create_post_type'));

        add_shortcode('fish-map-add-place-form', array($this, 'renderForm'));

        $this->_model = new MarkerModel();
    }


    public function create_post_type() {
        register_post_type('lakes', array(
            'labels' => array(
                'name' => __( 'Lakes' ),
                'singular_name' => __( 'Lake' )
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'can_export'         => true,
            'show_in_nav_menus'  => false,
            'query_var'          => true,
            'has_archive'        => true,
            'rewrite'            => apply_filters('fish_map_posttype_rewrite_args', array(
                'slug'       => 'lakes',
                'with_front' => false,
                'feeds'      => true
            )),
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'custom-fields' )
        ));
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

        /* plupload */
        wp_register_script('jquery.plupload', plugins_url('js/3p/plupload-2.0.0/plupload.full.min.js', __FILE__));
        wp_enqueue_script('jquery.plupload');
        wp_register_script('jquery.plupload.uk', plugins_url('js/3p/plupload-2.0.0/i18n/uk_UA.js', __FILE__));
        wp_enqueue_script('jquery.plupload.uk');

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
        if (is_user_logged_in()) {
            $this->addScripts();
            $this->addStylesheets();

            $fishes = $this->_model->getFishes();
            $showUploadPhotos = $this->_isUploaderInstalled();
            include 'tpls/add_place_form.phtml';
        } else {
            include 'tpls/add_place_login.phtml';
        }
    }

    public function savePlace()
    {
        if (!is_user_logged_in()) {
            die();
        }

        $validator = $this->_model->validator($_POST);
        if ($validator->validate()) {
            $result = $this->_model->insertMarker($_POST);
            $this->_model->sendEmailNotification($_REQUEST);
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

    private function _getUploaderClass()
    {
        return WP_PLUGIN_DIR . '/nextgen-public-uploader/inc/class.npu_uploader.php';
    }

    private function _isUploaderInstalled()
    {
        return file_exists($this->_getUploaderClass());
    }

    public function savePhotos()
    {
        if (!$this->_isUploaderInstalled()) {
            return;
        }
        require_once $this->_getUploaderClass();

        $_FILES['file']['name'] = $_REQUEST['name'];

        $uploader = new UploaderNggAdmin();
        $uploader->upload_images();
        $arrImageIds    = $uploader->arrImageIds;
        $strGalleryPath = $uploader->strGalleryPath;
        $arrImageNames  = $uploader->arrImageNames;
        echo json_encode(compact('messagetext', 'arrImageIds', 'strGalleryPath', 'arrImageNames'));
    }
}

$fishMapAddPlacePlugin = new FishMapAddPlacePlugin();