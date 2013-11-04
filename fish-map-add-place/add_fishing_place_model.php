<?php

// Init WordPress environment
require_once( dirname( dirname( __FILE__ ) ) . '/../../wp-load.php' );

require_once 'add_fishing_place_config.php';
require_once 'add_fishing_place_exception.php';
global $user_login;

// Opens a connection to a MySQL server
$connection = mysql_connect(EXT_DB_HOST, EXT_DB_USER_RW, EXT_DB_PASSWORD_RW);
if (!$connection) {
    die("Not connected : " . mysql_error());
}

mysql_set_charset(EXT_DB_CHARSET);

// Set the active mySQL database
$db_selected = mysql_select_db(EXT_DB_NAME, $connection);
if (!$db_selected) {
    die("Can\'t use db : " . mysql_error());
}

/* BEGIN Utility Functions:
 * * send_email_notification
 * * log_insert_marker
 */

function log_insert_marker($query) {
    /* 
     * Log adding marker
     */
    // Obtain user info
    $current_user = wp_get_current_user();
    $user_info = sprintf("ID:%s;LOGIN:%s;EMAIL:%s;IP:%s",
            $current_user->ID,
            $current_user->user_login,
            $current_user->user_email,
            $_SERVER['REMOTE_ADDR']);

    // Insert INFO
    $ins_query = sprintf("INSERT INTO markers_log (log_text, user_info)
                            VALUES ('%s', '%s')",
        mysql_real_escape_string($query),
        mysql_real_escape_string($user_info));

    $results = mysql_query($ins_query);
    if (!($results)) {
        die('ERROR: Can not insert the record:' . mysql_error());
    }
}

function send_email_notification($action, $post_params, $get_params) {
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
            'GET параметри:'."\r\n" . array_implode("=", "\r\n", $get_params) . "\r\n\r\n" .
            'POST параметри:'."\r\n" . array_implode("=", "\r\n", $post_params) . "\r\n\r\n";

    $headers = 'From: ' . $from . "\r\n" .
            'Reply-To: ' . $to . "\r\n" .
            'Content-type: text/plain; charset=utf-8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

    mail($to, $subject, $message, $headers);
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
function array_implode($glue, $separator, $array) {
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

function get_last_marker_id() {
    /* Get last marker id to show on Add Fishing Place form */
    $query = "SELECT COUNT(marker_id) FROM markers";
    
    $result = mysql_query($query);
    if (!$result) {
        die("Invalid query: " . mysql_error());
    }
    $row = mysql_fetch_row($result);
    return $row[0];
}
/* END Utility Functions */

// Get pairs (fish_id, name) to init Fish Chooser
function get_fishes_for_view() {
    $query = "SELECT fish_id, name
        FROM fishes
        ORDER BY name";

    $result = mysql_query($query);
    if (!$result) {
        die("Invalid query: " . mysql_error());
    }

    $rows = array();
    while ($row = mysql_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// Get pairs (country_id, name) to init Countries Chooser
function get_countries_for_view() {
    $query = "SELECT country_id, name
        FROM countries
        ORDER BY name";

    $result = mysql_query($query);
    if (!$result) {
        die("Invalid query: " . mysql_error());
    }

    $rows = array();
    while ($row = mysql_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}
// Get pairs (region_id, name) to init Regions Chooser
function get_regions_for_view() {
    $query = "SELECT regions.region_id, regions.name, countries.country_id, countries.name as country
        FROM regions
        INNER JOIN countries USING(country_id)
        ORDER BY countries.name, regions.name";

    $result = mysql_query($query);
    if (!$result) {
        die("Invalid query: " . mysql_error());
    }

    $rows = array();
    while ($row = mysql_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}
// Get pairs (district_id, name) to init Districts Chooser
function get_districts_for_view() {
    $query = "SELECT districts.district_id, districts.name, regions.region_id, regions.name as region
        FROM districts
        INNER JOIN regions USING(region_id)
        ORDER BY regions.name, districts.name";

    $result = mysql_query($query);
    if (!$result) {
        die("Invalid query: " . mysql_error());
    }

    $rows = array();
    while ($row = mysql_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
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
function insert_marker($args) {
    if (empty($args['column_user_login'])) {
        throw new IDException('Ви не залогінилися! Будь ласка, залогіньтеся чи зареєструйтеся.',
                'column_user_login');
    }
    
    $marker_name = NULL;
    $marker_lat = NULL;
    $marker_lng = NULL;
    $marker_permit = NULL;
    $marker_contact = NULL;
    $marker_paid_fish = NULL;
    
    
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

    $query = "INSERT INTO markers(name, lat, lng, permit, contact, paid_fish)
              VALUES(
                    '$marker_name',
                    '$marker_lat',
                    '$marker_lng',
                    '$marker_permit',
                    '$marker_contact',
                    '$marker_paid_fish'
                  )";

    //print_r($query);
    
    send_email_notification('INSERT', $_POST, $_GET);
    log_insert_marker($query);

    $add_result = mysql_query($query);
    if (!$add_result) {
        die("Invalid query: " . mysql_error());
    }

    return 'Додано рибне місце "' . $marker_name . '".';
}