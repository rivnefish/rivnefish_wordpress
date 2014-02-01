<?php
/*
Plugin Name: Fish Map
Plugin URI:
Description: This plugin creates Fish Map which uses Google Maps API v3 and allows to add map into
             the page/post using shortcode [map].
Version: 0.1
Author: Yaroslav Hrabar
Author URI: http://rivnefish.com
License: BSD
Created: 27 October 2011
TODO:
 1. Interactive header image (header.php):
   a. <img src="http://path_to_images/header_<?php echo(rand(1,5)); ?>.jpg" width="image_width" height="image_height" alt="image_alt_text" />
   b.   <?php
        $result_random=rand(1, 99);
        if($result_random<=33){ ?>
            <div id="header" style="background:transparent url(images/header1.png) no-repeat top left;">
        <?php }
        elseif($result_random<=66){ ?>
            <div id="header" style="background:transparent url(images/header2.png) no-repeat top left;">
        <?php }
        elseif($result_random<=99){ ?>
            <div id="header" style="background:transparent url(images/header3.png) no-repeat top left;">
        <?php } ?>
        <!-- Header code goes here -->
        </div>

      2. Provide public API (with authorization, REST or simple Web Service) that can return different
         info from the markers database.
      3. Show on the front page: "This weekend you can go to the Bochanitsa..."

      - refactor code to init show all markers in separate function;
      - картинки мають бути з наперед заданими розмірами (хоча б висотою) щоб уникнути
        розширення ІнфоВікна по мірі завантаження малюнків вже після його відображення
      -
*/

/*
 * How to load Javascript on specific pages in WordPress
 * <?php if (is_page('home') || is_page('contact') || is_page('45')) { ?>
 * <script type='text/javascript' src="<?php bloginfo('template_directory'); ?>/Scripts/customJS.js"></script>
 * <?php } ?>
 *
 * To get post's ID or title use:
 * <?php get_the_ID(); ?>
 * <?php get_the_title(); ?>
 */

require_once 'includes/fish_map_install.php';
require_once 'includes/marker_model.php';
require_once 'includes/fish_model.php';
require_once 'fish_map_views.php';
require_once 'fish_map_add_place.php';

add_shortcode('map', 'fish_map');
add_shortcode('fish_map_elegant', 'fish_map_elegant');
add_action('wp_enqueue_scripts', 'add_scripts_map');
add_action('wp_print_styles', 'add_stylesheets_map');

add_action('wp_ajax_nopriv_fish_map_markers', 'fish_map_markers');
add_action('wp_ajax_fish_map_markers', 'fish_map_markers');

add_action('wp_ajax_nopriv_fish_map_markers_search', 'fish_map_markers_search');
add_action('wp_ajax_fish_map_markers_search', 'fish_map_markers_search');

add_action('wp_ajax_nopriv_fish_map_marker_info', 'fish_map_marker_info');
add_action('wp_ajax_fish_map_marker_info', 'fish_map_marker_info');


add_action('wp_ajax_fish_map_markers_search', 'fish_map_markers_search');

function add_scripts_map() {
        wp_deregister_script('jquery');
        wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js');
        # BACKUP wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
        wp_enqueue_script('jquery');

        wp_deregister_script('jquery-migrate');
        wp_register_script('jquery-migrate', 'http://code.jquery.com/jquery-migrate-1.0.0.min.js');
        wp_enqueue_script('jquery-migrate');

        wp_deregister_script('jquery-ui');
        wp_register_script('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js');
        # BACKUP wp_register_script('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js');
        wp_enqueue_script('jquery-ui');

    if (is_page('Мапа') || is_page(2) || $_GET['debug']) {
        /* !!! TODO: update Post's TITLE and ID in case changed*/
        //wp_register_script('google-map', 'http://maps.google.com/maps/api/js?sensor=false&language=uk');
        wp_register_script('google-map', 'http://maps.googleapis.com/maps/api/js?key=AIzaSyCByg67-8HjM_17CVdq9iOiN95Nhz7izCw&sensor=false&language=uk&libraries=weather');
        # BACKUP wp_register_script('google-map', 'http://maps.googleapis.com/maps/api/js?key=AIzaSyBmqWVqXVRZS4BXyoeOaUWTlok9KWzwZso&sensor=false&language=uk');
        wp_enqueue_script('google-map');

        wp_register_script('fish-map', plugins_url('js/fish-map.js', __FILE__));
        wp_enqueue_script('fish-map');

        // Load MarkerClusterer
        wp_deregister_script('markerclusterer');
        // URL: http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/src/markerclusterer_compiled.js
        wp_register_script('markerclusterer', plugins_url('js/markerclusterer.min.js', __FILE__));
        wp_enqueue_script('markerclusterer');
    }

}

function add_stylesheets_map() {
    wp_register_style('fishStyleSheet', plugins_url('css/fish_map.css', __FILE__));
    wp_enqueue_style('fishStyleSheet');
    if (is_page('Мапа')) {
        wp_register_style('jquery-ui-sheet', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/dark-hive/jquery-ui.css');
        wp_enqueue_style('jquery-ui-sheet');
    }
}

function fish_map($attr) {
    $return_body = fish_map_main_form();
    return $return_body;
}

function fish_map_elegant() {
    include 'tpls/fish_map_elegant.phtml';
}

/* AJAX Calls */
function fish_map_markers() {
    $markerModel = new MarkerModel();
    $markers = $markerModel->getListForMap();
    echo json_encode($markers);
    die();
}

function fish_map_markers_search() {
    $markerModel = new MarkerModel();
    $markers = $markerModel->getInRadius($_GET["radius"], $_GET["lat"], $_GET["lng"]);
    echo json_encode($markers);
    die();
}

function fish_map_marker_info() {
    global $nggdb;
    $marker_id = $_GET['marker_id'];
    $markerModel = new MarkerModel();
    $fishModel = new FishModel();

    $marker_row = $markerModel->getById($marker_id);

    $pageUrl = $markerModel->getPageUrl($marker_row);
    if ($pageUrl) {
        $marker_row['page_url'] = $pageUrl;
    }

    if ($marker_row['gallery_id']) {
        $photos = array();
        $gallery = $nggdb->get_gallery($marker_row['gallery_id'], 'sortorder', 'ASC', true, 4);
        foreach ($gallery as $image) {
            $photos[] = array(
                'thumbnail' => $image->thumbURL,
                'photo' => $image->imageURL
            );
        }
        $marker_row['photos'] = $photos;
    }

    $fishes = $fishModel->getByMarker($marker_id);

    $response = array(
        'marker' => $marker_row,
        'fishes' => $fishes
    );
    echo json_encode($response);
    die();
}
