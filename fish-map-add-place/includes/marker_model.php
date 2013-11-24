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
        /*
         * $action      - one of 'updated', 'inserted', 'deleted'
         * $post_params - values
         * $get_params  - 'new' etc
         */
        $subject = '[Рибні місця Рівненщини] Додано нову водойму - будь ласка, санкціонуйте!';
        $message = 'Додано рибну водойму.
    Будь ласка, залогінься на
    http://rivnefish.com/phpMyAdmin-3.4.10.1-all-languages/index.php
    та зміни в таблиці `markers` для даного запису поле "approval" з "pending" на "approved",
    якщо дане рибне місце заслуговує на це...
    Або видали відповідний запис назавжди!' . "\r\n\r\n" .
                'Дата:' . date("d M Y H:i:s") . "\r\n\r\n" .
                'REQUEST параметри:'."\r\n" . print_r($request, 1) . "\r\n\r\n";
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

        $v->rule('numeric', array(
                'area', 'max_depth', 'average_depth', '24h_price', 'dayhour_price'
            ))
          ->message('Некоректний формат');
        return $v;
    }

    public function getFishes()
    {
        return $this->db->get_results("SELECT id, name FROM fishes ORDER BY name");
    }

    public function getFishIds()
    {
        return $this->db->get_col("SELECT id FROM fishes");
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
            'time_to_fish' => $data['time_to_fish']
        );
        $this->db->insert('markers', $marker);
        $markerId = $this->db->insert_id;

        if (isset($data['fishes'])) {
            $this->insertMarkerFishes($markerId, $data['fishes']);
        }

        return $markerId;
    }
}
