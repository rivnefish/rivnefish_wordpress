<?php

class FishMapAddPlacePlugin
{
    private $_markerModel;
    private $_fishModel;

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
        add_shortcode('fish-map-marker-info', array($this, 'markerInfo'));

        $this->_markerModel = new MarkerModel();
        $this->_fishModel = new FishModel();
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
        wp_register_style('addFishPlaceStyleSheet', plugins_url('css/add_fish_place.css', __FILE__));
        wp_enqueue_style('addFishPlaceStyleSheet');
    }

    public function renderForm($attr)
    {
        if (is_user_logged_in()) {
            $this->addScripts();
            $this->addStylesheets();

            $fishes = $this->_fishModel->getNames();
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

        $validator = $this->_markerModel->validator($_POST);
        if ($validator->validate()) {
            $markerId = $this->saveMarker();

            if (isset($_POST['fishes'])) {
                $this->_fishModel->insertMarkerFishes($markerId, $_POST['fishes']);
            }
            $galleryId = $this->_markerModel->createMarkerGallery($markerId, strip_tags($_POST['name']), $_POST['pictures']);
            $this->_markerModel->createMarkerPost($markerId, strip_tags($_POST['name']), strip_tags($_POST['content']), $galleryId);
            $this->_markerModel->sendEmailNotification($_REQUEST);

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

    private function saveMarker()
    {
        $data = array(
            'name' => strip_tags($_POST['name']),
            'lat' => $_POST['lat'],
            'lng' => $_POST['lng'],
            'permit' => $_POST['permit'],
            'contact' => strip_tags($_POST['contact']),
            'paid_fish' => strip_tags($_POST['paid_fish']),

            // additional info
            'address' => strip_tags($_POST['address']),
            'content' => strip_tags($_POST['content']),
            'conveniences' => strip_tags($_POST['conveniences']),
            'area' => $_POST['area'],
            'max_depth' => $_POST['max_depth'],
            'average_depth' => $_POST['average_depth'],
            '24h_price' => $_POST['24h_price'],
            'dayhour_price' => $_POST['dayhour_price'],
            'boat_usage' => $_POST['boat_usage'],
            'time_to_fish' => $_POST['time_to_fish'],
            'author_id' => get_current_user_id()
        );
        return $this->_markerModel->insertMarker($data);
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

    public function markerInfo($attrs)
    {
        $marker = $this->_markerModel->getById($attrs['id']);
        $fishes = $this->_fishModel->getByMarker($attrs['id']);
        include 'tpls/marker_info.phtml';
    }

    /* Html Helpers */
    public function fishTitle($fish)
    {
        $weight_avg = $fish['weight_avg'] ? $fish['weight_avg'] : '-';
        $weight_max = $fish['weight_max'] ? $fish['weight_max'] : '-';
        $amount = $fish['amount'] ? $fish['amount'] : '-';

        return implode(array(
            $fish["name"],
            "середня вага: {$weight_avg}гр",
            "максимальна {$weight_max}гр",
            "кльов {$amount}/10"
        ), ", ");
    }

    public function amountImg($amount)
    {
        $amounts = array(
            "https://lh3.googleusercontent.com/-pA3e1NFvUm8/Trz_UZ8Fs-I/AAAAAAAABdM/aEK8mt1ZS_I/s800/score_01.png",
            "https://lh6.googleusercontent.com/-4DN2LTsUbG4/Trz_UYtEUtI/AAAAAAAABdI/YVTX3zrQTSo/s800/score_02.png",
            "https://lh6.googleusercontent.com/-ZMiSp_fH5OE/Trz_URksCkI/AAAAAAAABdQ/dfnXeIogSiM/s800/score_03.png",
            "https://lh4.googleusercontent.com/-3pJyfPwa85U/Trz_UsRN6YI/AAAAAAAABdY/Bai3V0RKzY8/s800/score_04.png",
            "https://lh4.googleusercontent.com/-upUuE-VV6WQ/Trz_VH03c0I/AAAAAAAABdc/YDxOsgTmC-U/s800/score_05.png",
            "https://lh5.googleusercontent.com/-cu-ov_hiSGc/Trz_VOgc8_I/AAAAAAAABdk/m3lw1UgdJ58/s800/score_06.png",
            "https://lh6.googleusercontent.com/-L0vacmk1T6I/Trz_VQJw6UI/AAAAAAAABdo/a-D6BUkTsek/s800/score_07.png",
            "https://lh3.googleusercontent.com/-nD79CO5CYYs/Trz_XhSMy4I/AAAAAAAABeM/5odbngZEYQc/s800/score_08.png",
            "https://lh6.googleusercontent.com/-BRSSsL8dsVk/Trz_V1FCwEI/AAAAAAAABds/wdhGbYRQjL4/s800/score_09.png",
            "https://lh5.googleusercontent.com/-gBXX50iC2uw/Trz_WGWwfSI/AAAAAAAABd4/RjzfJj0CbAQ/s800/score_10.png"
        );
        return $amounts[intval($amount) - 1];
    }
}

$fishMapAddPlacePlugin = new FishMapAddPlacePlugin();