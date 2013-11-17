<?php

require_once 'add_fishing_place_config.php';
require_once 'add_fishing_place_exception.php';

class MarkerModel
{
    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function logInsertMarker($data)
    {
        /*
         * Log adding marker
         */
        $current_user = wp_get_current_user();
        $user_info = sprintf("ID:%s;LOGIN:%s;EMAIL:%s;IP:%s",
                $current_user->ID,
                $current_user->user_login,
                $current_user->user_email,
                $_SERVER['REMOTE_ADDR']);

        $this->db->insert('markers_log', array(
            'log_text' => print_r($data, 1),
            'user_info' => $user_info
        ));
    }

    public function sendEmailNotification($action, $post_params, $get_params)
    {
        /*
         * $action      - one of 'updated', 'inserted', 'deleted'
         * $post_params - values
         * $get_params  - 'new' etc
         */
        $to = TO_EMAIL;
        $from = FROM_EMAIL;
        $subject = '[Рибні місця Рівненщини] Додано нову водойму - будь ласка, санкціонуйте!';

        $message = 'Додано рибну водойму.
    Будь ласка, залогінься на
    http://rivnefish.com/phpMyAdmin-3.4.10.1-all-languages/index.php
    та зміни в таблиці `markers` для даного запису поле "approval" з "pending" на "approved",
    якщо дане рибне місце заслуговує на це...
    Або видали відповідний запис назавжди!' . "\r\n\r\n" .
                'Дата:' . date("d M Y H:i:s") . "\r\n\r\n" .
                'Дія: ' . $action . "\r\n\r\n" .
                'GET параметри:'."\r\n" . $this->array_implode("=", "\r\n", $get_params) . "\r\n\r\n" .
                'POST параметри:'."\r\n" . $this->array_implode("=", "\r\n", $post_params) . "\r\n\r\n";

        $headers = 'From: ' . $from . "\r\n" .
                'Reply-To: ' . $to . "\r\n" .
                'Content-type: text/plain; charset=utf-8' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

        // @TODO replace with wp_mail
        // mail($to, $subject, $message, $headers);
    }

    /**
     * Implode an array with the key and value pair giving
     * a glue, a separator between pairs and the array
     * to implode.
     * @param string $glue The glue between key and value
     * @param string $separator Separator between pairs
     * @param array $array The array to implode
     * @return string The imploded array
     */
    public function array_implode($glue, $separator, $array)
    {
        if (!is_array($array))
            return $array;
        $string = array();
        foreach ($array as $key => $val) {
            if (is_array($val))
                $val = implode(',', $val);
            $string[] = "{$key}{$glue}{$val}";
        }
        return implode($separator, $string);
    }

    // Get pairs (fish_id, name) to init Fish Chooser
    public function getFishes()
    {
        return $this->db->get_results("SELECT id, name FROM fishes ORDER BY name");
    }

    /* REMEMBER to assign "name" to elements e.g.
     * <select id="column_marker_permit" name="column_marker_permit" size="1">
     * GET:
    column_marker_id, column_user_login, column_marker_name
    column_marker_lat	50.730371
    column_marker_lng	26.156731
    [column_marker_permit] => paid
    [column_marker_contact] => 050 555 55 55 Іван Павлович
    [column_marker_paid_fish] => 80 з острова, 60 з берега
     */
    public function insertMarker($args)
    {
        if (!is_user_logged_in()) {
            throw new IDException('Ви не залогінилися! Будь ласка, залогіньтеся чи зареєструйтеся.',
                    'column_user_login');
        }

        if (empty($args['column_marker_name'])) {
            throw new IDException('Назва водойми є обов\'язковим полем!', 'column_marker_name');
        } elseif (!is_string($args['column_marker_name'])) {
            //TODO: properly verify Marker Name
            throw new IDException('Введіть назву водойми, наприклад "Закопане"!', 'column_marker_name');
        } else {
            $marker_name = $args['column_marker_name'];
        }

        if (empty($args['column_marker_lat'])) {
            throw new IDException('Широта водойми є обов\'язковим полем!', 'column_marker_lat');
        } elseif (!is_numeric($args['column_marker_lat'])) {
            throw new IDException('Введіть широту водойми в форматі Google Maps, наприклад "50.730371"!', 'column_marker_lat');
        } else {
            $marker_lat = $args['column_marker_lat'];
        }

        if (empty($args['column_marker_lng'])) {
            throw new IDException('Довгота водойми є обов\'язковим полем!', 'column_marker_lng');
        } elseif (!is_numeric($args['column_marker_lng'])) {
            throw new IDException('Введіть довготу водойми в форматі Google Maps, наприклад "26.156731"!', 'column_marker_lng');
        } else {
            $marker_lng = $args['column_marker_lng'];
        }

        if (!empty($args['column_marker_permit'])) {
            $marker_permit = $args['column_marker_permit'];
        }

        if (!empty($args['column_marker_contact'])) {
            $marker_contact = $args['column_marker_contact'];
        }

        if (!empty($args['column_marker_paid_fish'])) {
            $marker_paid_fish = $args['column_marker_paid_fish'];
        }

        $data = array(
            'name' => $marker_name,
            'lat' => $marker_lat,
            'lng' => $marker_lng,
            'permit' => $marker_permit,
            'contact' => $marker_contact,
            'paid_fish' => $marker_paid_fish
        );
        $this->db->insert('markers', $data);

        $this->sendEmailNotification('INSERT', $_POST, $_GET);
        $this->logInsertMarker($data);

        return 'Додано рибне місце "' . $marker_name . '".';
    }
}
