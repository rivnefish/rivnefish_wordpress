<?php

require_once 'fish_map_config.php';
require_once 'Valitron/Validator.php';

use Valitron\Validator;
Validator::langDir(__DIR__ . '/Valitron');

class MarkerModel
{
    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function sendEmailNotification($request)
    {
        // Obtain user info
        $current_user = wp_get_current_user();
        $user_info = sprintf("ID:%s;LOGIN:%s;EMAIL:%s;IP:%s",
           $current_user->ID,
           $current_user->user_login,
           $current_user->user_email,
           $_SERVER['REMOTE_ADDR']);

        $subject = '[Рибні місця Рівненщини] Додано нову водойму - будь ласка, санкціонуйте!';
        $message = 'Додано нову водойму.' . "\r\n\r\n"
                 . 'Дата:' . date("d M Y H:i:s") . "\r\n\r\n"
                 . 'Користувач:' . "\r\n" . print_r($user_info, 1) . "\r\n\r\n"
                 . '_REQUEST параметри:'."\r\n" . print_r($request, 1);
        $headers = 'From: ' . FROM_EMAIL;
        wp_mail(TO_EMAIL, $subject, $message, $headers);
    }

    public function validator($data)
    {
        $v = new Validator($data);
        $v->rule('required', 'name')
          ->message('Назва водойми є обов\'язковим полем!');
        $v->rule('required', array('lat', 'lng'))
          ->message('Відмітьте водойму на карті');
        $v->rule('numeric', array('lat', 'lng'))
          ->message('Некоректний формат координат');
        $v->rule('url', array('photo_url1', 'photo_url2', 'photo_url3', 'photo_url4'))
          ->message('Некоректний URL на фото');

        $v->rule('numeric', array(
                'area', 'max_depth', 'average_depth', '24h_price', 'dayhour_price'
            ))
          ->message('Некоректний формат');
        return $v;
    }

    public function getById($markerId)
    {
        $query_marker = $this->db->prepare("SELECT * FROM markers WHERE marker_id = %d LIMIT 1", $markerId);
        return $this->db->get_row($query_marker, ARRAY_A);
    }

    public function getPageUrlFromPassport($markerId)
    {
        $query_passport = $this->db->prepare("SELECT url_suffix FROM passports WHERE marker_id = %d", $markerId);
        return $this->db->get_var($query_passport);
    }

    public function getPageUrl($marker)
    {
        if ($marker['post_id']) {
            return get_permalink($marker['post_id']);
        } else {
            return $this->getPageUrlFromPassport($marker['marker_id']);
        }
    }

    public function getInRadius($radius, $lat, $lng)
    {
        $distance_formula = "( 6371 * acos( cos( radians('%s') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('%s') ) "
                          . "+ sin( radians('%s') ) * sin( radians( lat ) ) ) )";
        $query_markers = $this->db->prepare(
            "SELECT marker_id, name, address, lat, lng, $distance_formula AS distance
             FROM markers
             HAVING distance < '%s'
             ORDER BY distance", $lat, $lng, $lat, $radius);

        return $this->db->get_results($query_markers, ARRAY_A);
    }

    public function getListForMap()
    {
        return $this->db->get_results(
            'SELECT marker_id, name, address, lat, lng
            FROM markers WHERE approval IN ("approved","pending") order by name', ARRAY_A
        );
    }

    private function _getNggFunctionsPath()
    {
        return WP_PLUGIN_DIR . '/nextgen-gallery/products/photocrati_nextgen/modules/ngglegacy/admin/functions.php';
    }

    public function createMarkerGallery($markerId, $name, $imageIds = null)
    {
        require_once $this->_getNggFunctionsPath();
        global $ngg;

        $name = esc_attr($name);
        $defaultpath = $ngg->options['gallerypath'];
        $galleryId = nggAdmin::create_gallery($name, $defaultpath, false);

        $this->assignGalleryToMarker($galleryId, $markerId);

        if ($imageIds) {
            ob_start();
            $imageIds = array_map('intval', $imageIds);
            nggAdmin::move_images($imageIds, $galleryId);
            ob_get_clean();
        }

        return $galleryId;
    }

    public function assignGalleryToMarker($galleryId, $markerId)
    {
        $this->db->update('markers', array('gallery_id' => $galleryId), array('marker_id' => $markerId));
    }

    public function createMarkerPost($markerId, $name, $content, $galleryId = null)
    {
        $content = "[fish-map-marker-info id=$markerId]" . "\n" . $content;

        if ($galleryId) {
            $content .= "\n" . "[nggallery id={$galleryId}]";
        }

        $postId = wp_insert_post(array(
            'post_title'    => $name,
            'post_content'  => $content,
            'post_status'   => 'publish',
            'post_type'     => 'lakes'
        ));
        $this->assignPostToMarker($postId, $markerId);
        return $postId;
    }

    public function assignPostToMarker($postId, $markerId)
    {
        $this->db->update('markers', array('post_id' => $postId), array('marker_id' => $markerId));
    }

    public function insertMarker($data)
    {
        $this->db->insert('markers', $data);
        return $this->db->insert_id;
    }
}
