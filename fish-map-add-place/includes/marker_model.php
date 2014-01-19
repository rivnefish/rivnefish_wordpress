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
        $subject = '[Рибні місця Рівненщини] Додано нову водойму - будь ласка, санкціонуйте!';
        $message = 'Додано нову водойму.' . "\r\n\r\n"
                 . 'Дата:' . date("d M Y H:i:s") . "\r\n\r\n"
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

    public function getFishes()
    {
        return $this->db->get_results("SELECT fish_id, name FROM fishes ORDER BY name");
    }

    public function getFishIds()
    {
        return $this->db->get_col("SELECT fish_id FROM fishes");
    }

    public function insertMarkerFishes($markerId, $fishes)
    {
        $fishIds = $this->getFishIds();
        foreach ($fishes as $fishId) {
            $fishId = intval($fishId);
            if (in_array($fishId, $fishIds)) {
                $this->db->insert('markers_fishes', array(
                    'marker_id' => $markerId,
                    'fish_id' => $fishId
                ));
            }
        }
    }

    public function insertMarkerPictures($markerId, $pictures)
    {
        foreach ($pictures as $pictureId) {
            $pictureId = intval($pictureId);
            $this->db->insert('markers_pictures', array(
                'marker_id' => $markerId,
                'picture_id' => $pictureId
            ));
        }
    }

    public function insertMarkerPost($markerId, $data)
    {
        $post = array(
            'post_title'    => $data['name'],
            'post_content'  => $data['content'],
            'post_status'   => 'publish',
            'post_author'   => $data['user_id'],
            'post_type'     => 'lakes'
        );

        $postId = wp_insert_post($post);
        $this->assignPostToMarker($postId, $markerId);

    }

    public function assignPostToMarker($postId, $markerId)
    {
        $this->db->update('markers', array('post_id' => $postId), array('marker_id' => $markerId));
    }

    public function insertMarker($data)
    {
        $marker = array(
            'name' => $data['name'],
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'permit' => $data['permit'],
            'contact' => $data['contact'],
            'paid_fish' => $data['paid_fish'],

            // additional info
            'address' => $data['address'],
            'content' => $data['content'],
            'conveniences' => $data['conveniences'],
            'area' => $data['area'],
            'max_depth' => $data['max_depth'],
            'average_depth' => $data['average_depth'],
            '24h_price' => $data['24h_price'],
            'dayhour_price' => $data['dayhour_price'],
            'boat_usage' => $data['boat_usage'],
            'time_to_fish' => $data['time_to_fish'],
            'author_id' => $data['user_id']
        );
        $this->db->insert('markers', $marker);
        return $this->db->insert_id;
    }
}
