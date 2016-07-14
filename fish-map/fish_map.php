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
require_once 'includes/gallery_model.php';
require_once 'includes/marker_info.php';
require_once 'includes/markers_cache.php';
require_once 'fish_map_views.php';
require_once 'fish_map_add_place.php';
require_once 'fish_map_report_post.php';

add_shortcode('map', 'fish_map');
add_shortcode('fish_map_elegant', 'fish_map_elegant');
add_action('wp_enqueue_scripts', 'add_scripts_map');
add_action('wp_print_styles', 'add_stylesheets_map');

add_action('wp_ajax_nopriv_fish_map_markers', 'fish_map_markers');
add_action('wp_ajax_fish_map_markers', 'fish_map_markers');

add_action('wp_ajax_nopriv_fish_map_markers_fullinfo', 'fish_map_markers_fullinfo');
add_action('wp_ajax_fish_map_markers_fullinfo', 'fish_map_markers_fullinfo');

add_action('wp_ajax_nopriv_fish_map_markers_search', 'fish_map_markers_search');
add_action('wp_ajax_fish_map_markers_search', 'fish_map_markers_search');

add_action('wp_ajax_nopriv_fish_map_marker_info', 'fish_map_marker_info');
add_action('wp_ajax_fish_map_marker_info', 'fish_map_marker_info');

add_action('wp_ajax_nopriv_fish_map_marker_post', 'fish_map_marker_post');
add_action('wp_ajax_fish_map_marker_post', 'fish_map_marker_post');

add_action('wp_ajax_nopriv_fish_map_fishes', 'fish_map_fishes');
add_action('wp_ajax_fish_map_fishes', 'fish_map_fishes');

add_action('wp_ajax_fish_map_update_position', 'fish_map_update_position');


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
    wp_enqueue_script('jquery-ui');
    
    wp_deregister_script('fish_map_functions');
    wp_register_script('fish_map_functions', plugins_url('js/functions.js', __FILE__));
    wp_enqueue_script('fish_map_functions');

    if (is_page('Мапа') || is_page(2) || $_GET['debug']) {
        /* !!! TODO: update Post's TITLE and ID in case changed*/
        //wp_register_script('google-map', 'http://maps.google.com/maps/api/js?sensor=false&language=uk');
        wp_register_script('google-map', 'http://maps.googleapis.com/maps/api/js?key=AIzaSyCByg67-8HjM_17CVdq9iOiN95Nhz7izCw&sensor=false&language=uk&libraries=weather');
        # BACKUP wp_register_script('google-map', 'http://maps.googleapis.com/maps/api/js?key=AIzaSyCByg67-8HjM_17CVdq9iOiN95Nhz7izCw&sensor=false&language=uk&libraries=weather');
        wp_enqueue_script('google-map');

        wp_register_script('fish-map', plugins_url('js/fish-map.js?v=2', __FILE__));
        wp_enqueue_script('fish-map');

        // Load MarkerClusterer
        wp_deregister_script('markerclusterer');
        // URL: http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/src/markerclusterer_compiled.js
        wp_register_script('markerclusterer', plugins_url('js/markerclusterer.min.js', __FILE__));
        wp_enqueue_script('markerclusterer');

        wp_register_script('jquery.noty', plugins_url('js/3p/jquery.noty.packaged.min.js', __FILE__));
        wp_enqueue_script('jquery.noty');
    }

}

function add_stylesheets_map() {
    wp_register_style('fishmap-styles', plugins_url('css/styles.css', __FILE__));
    wp_enqueue_style('fishmap-styles');
    if (is_page('Мапа')) {
        wp_register_style('fishStyleSheet', plugins_url('css/fish_map.css', __FILE__));
        wp_enqueue_style('fishStyleSheet');
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
    echo json_encode($markers, JSON_UNESCAPED_UNICODE);
    die();
}

function fish_map_markers_fullinfo() {
    $modified_after = isset($_GET['modified_after']) ? $_GET['modified_after'] : false;

    $markerModel = new MarkerModel();
    $markers = $markerModel->getModifiedAfter($modified_after);

    $result = array();
    $cache = new MarkersCache();
    foreach ($markers as $marker) {
        $result[] = $cache->getMarkerInfo($marker);
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    die();
}

function fish_map_markers_search() {
    $markerModel = new MarkerModel();
    $markers = $markerModel->getInRadius($_GET["radius"], $_GET["lat"], $_GET["lng"]);
    echo json_encode($markers, JSON_UNESCAPED_UNICODE);
    die();
}

function fish_map_marker_info() {
    $marker_id = $_GET['marker_id'];

    $markerModel = new MarkerModel();
    $marker_row = $markerModel->getById($marker_id);

    $cache = new MarkersCache();
    $info = $cache->getMarkerInfo($marker_row);

    echo json_encode($info, JSON_UNESCAPED_UNICODE);
    die();
}

function get_post_content_by_id( $post_id=0, $more_link_text = null, $stripteaser = false ) {
    ob_start();
    global $post;
    $post = get_post($post_id);
    setup_postdata($post, $more_link_text, $stripteaser);
    the_content();
    wp_reset_postdata($post);
    return ob_get_clean();
}

function fish_map_marker_post() {
    if (!isset($_GET['import'])) {
        die();
    }
    $marker_id = $_GET['marker_id'];

    $markerModel = new MarkerModel();
    $marker_row = $markerModel->getById($marker_id);

    if ($marker_row['post_id']) {
        $marker_post = get_post($marker_row['post_id'], ARRAY_A);
        $marker_post['rendered_content'] = get_post_content_by_id($marker_row['post_id']);
        $marker_post['featured_image'] = wp_get_attachment_url( get_post_thumbnail_id($marker_row['post_id']) );
        $marker_post['_yoast_wpseo_title'] = get_post_meta($marker_row['post_id'], '_yoast_wpseo_title', true);
        $marker_post['_yoast_wpseo_metadesc'] = get_post_meta($marker_row['post_id'], '_yoast_wpseo_metadesc', true);
    } else {
        $marker_post = array();
    }
    echo json_encode($marker_post, JSON_UNESCAPED_UNICODE);
    die();
}

function fish_map_lake_map_by_post($post_id) {
    $markerModel = new MarkerModel();
    $marker = $markerModel->getByPost($post_id);

    if ($marker) {
        wp_register_script('google-map', 'http://maps.googleapis.com/maps/api/js?key=AIzaSyCByg67-8HjM_17CVdq9iOiN95Nhz7izCw&sensor=false&language=uk&libraries=weather');
        # BACKUP wp_register_script('google-map', 'http://maps.googleapis.com/maps/api/js?key=AIzaSyCByg67-8HjM_17CVdq9iOiN95Nhz7izCw&sensor=false&language=uk&libraries=weather');
        wp_enqueue_script('google-map');

        wp_register_script('lake-map', plugins_url('js/lake-map.js', __FILE__));
        wp_enqueue_script('lake-map');
    }

    include 'tpls/fish_map_lake_map.phtml';
}

function fish_map_fishes() {
    $fishModel = new FishModel();
    echo json_encode($fishModel->getAll(), JSON_UNESCAPED_UNICODE);
    die();
}

function fish_map_update_position() {
    if (!current_user_can('manage_options')) die();

    $marker_id = $_POST['marker_id'];
    $markerModel = new MarkerModel();
    $markerModel->updatePosition($marker_id, $_POST['lat'], $_POST['lng']);
    echo json_encode($markerModel->getById($marker_id));
    die();
}
