<?php

// Init WordPress environment
require_once( dirname( dirname( __FILE__ ) ) . '/../../wp-load.php' );

require_once 'fish_map_query_config.php';
require_once 'fish_map_query_exception.php';

// Opens a connection to a MySQL server
$connection = mysql_connect(EXT_DB_HOST, EXT_DB_USER, EXT_DB_PASSWORD);
if (!$connection) {
    die("Not connected : " . mysql_error());
}

mysql_set_charset(EXT_DB_CHARSET);

// Set the active mySQL database
$db_selected = mysql_select_db(EXT_DB_NAME, $connection);
if (!$db_selected) {
    die("Can\'t use db : " . mysql_error());
}

function log_markers_query($query) {
    /*
     * Log Merkers select queries
     */
    $conn = mysql_connect(EXT_DB_HOST, EXT_DB_USER_RW, EXT_DB_PASSWORD_RW);
    if (!$conn) {
        die("Not connected: " . mysql_error());
    }
    mysql_set_charset(EXT_DB_CHARSET);
    $db_sel = mysql_select_db(EXT_DB_NAME, $conn);
    if (!$db_sel) {
        die("Can\'t use db: " . mysql_error());
    }
    // Obtain user info
    $current_user = wp_get_current_user();
    //$user_info = "";
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
    // mysql_close($conn);
}

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

function get_markers($args) {
    if ($args['permit'] == 'paid' && !empty($args['price']) && !is_numeric($args['price'])) {
        throw new IDException('Введіть ціну рибалки в гривнях, наприклад 50!', 'price');
    } elseif ($args['permit'] == 'paid' && floatval($args['price']) < 0) {
        throw new IDException('Введіть ціну рибалки в гривнях як додатнє число, наприклад 45!', 'price');
    }

    if (!empty($args['distance']) && !is_numeric($args['distance'])) {
        throw new IDException('Введіть відстань від Рівного в кілометрах як ціле число, наприклад 53!', 'distance');
    } elseif (intval(floatval($args['distance']) * 1000) < 0) {
        throw new IDException('Введіть відстань від Рівного в метрах як ціле додатнє число, наприклад 47!', 'distance');
    }

    if (!empty($args['add_opts']) && !empty($args['fish_weight']) && !is_numeric($args['fish_weight'])) {
        throw new IDException('Введіть вагу риби в грамах як ціле число, наприклад 2300!', 'fish_weight');
    } elseif (!empty($args['add_opts']) && floatval($args['fish_weight']) < 0) {
        throw new IDException('Введіть вагу риби в грамах як ціле додатнє число, наприклад 4750!', 'fish_weight');
    }

    $query = "SELECT
            markers.marker_id,
            markers.name,
            GROUP_CONCAT(fishes.name SEPARATOR ', ') as fishes,
            markers.contact,
            markers.paid_fish,
            markers.average_depth,
            markers.max_depth,
            markers.area,
            markers.note2,
            markers.content,
            markers.modify_date
        FROM markers
        INNER JOIN markers_fishes USING (marker_id)
        INNER JOIN fishes USING (fish_id)
        WHERE approval = 'approved'";

    if (!empty($args['permit'])) {

        if ($args['permit'] == 'paid') {
            $query .= " AND markers.permit = 'paid'";

            if (!empty($args['price'])) {
                $price_cond = sprintf(" AND (markers.24h_price <= %s OR markers.dayhour_price <= %s)",
                        abs(floatval($args['price'])),
                        abs(floatval($args['price'])));
                $query .= $price_cond;
            }

            if (!empty($args['boat'])) {
                $query .= " AND ( markers.boat_usage = '1' OR markers.boat_usage is NULL)";
            }

            if (!empty($args['at_night'])) {
                $query .= " AND markers.time_to_fish = '24h'";
            }
        } else if ($args['permit'] == 'free') {
            $query .= " AND markers.permit = 'free'";
        }
    }

    if (!empty($args['distance'])) {
        $query .= " AND markers.distance_to_Rivne <= " . abs(intval(floatval($args['distance']) * 1000));
    }

    if (!empty($args['fishes']) && is_array($args['fishes'])) {
        if (!empty($args['add_opts']) && !empty($args['only_all_fishes'])) {
            foreach ($args['fishes'] as $fish_id) {
                $query .= " AND fish_id = " . $fish_id;
            }
        } else {
            $query .= " AND fish_id IN (" . join(',', $args['fishes']) . ")";
        }
    }

    if (!empty($args['name'])) {
        $query .= " AND markers.name LIKE '%" . mysql_real_escape_string($args['name']) . "%'";
    }

    /* BEGIN Additional criteria: countries, regions, districts*/
    if (!empty($args['add_opts'])) {
        if (!empty($args['fish_weight'])) {
            $weight_cond = sprintf(" AND (weight_avg >= %s OR weight_max >= %s)",
                        abs(floatval($args['fish_weight'])),
                        abs(floatval($args['fish_weight'])));
            $query .= $weight_cond;
        }
        if (!empty($args['countries']) && is_array($args['countries'])) {
            $query .= " AND country IN (" . join(',', $args['countries']) . ")";
        }
        if (!empty($args['regions']) && is_array($args['regions'])) {
            $query .= " AND region IN (" . join(',', $args['regions']) . ")";
        }
        if (!empty($args['districts']) && is_array($args['districts'])) {
            $query .= " AND district IN (" . join(',', $args['districts']) . ")";
        }
    }
    /* END Additional criteria: countries, regions, districts*/

    $query .= "
        GROUP BY markers.marker_id
        ORDER BY markers.name";

    /*print_r($query);*/
    log_markers_query($query);

    $markers = mysql_query($query);
    if (!$markers) {
        die("Invalid query: " . mysql_error());
    }

    $rows = array();
    while ($row = mysql_fetch_assoc($markers)) {
        $rows[] = $row;
    }

    return $rows;
}

function get_fishes($marker_id) {
    $query = sprintf("SELECT name, icon_url, icon_width,
            icon_height, weight_avg, weight_max, amount, notes
        FROM markers_fishes
        INNER JOIN fishes USING (fish_id)
        WHERE marker_id = '%s'
        ORDER BY amount DESC",
    mysql_real_escape_string($marker_id));


    $fishes = mysql_query($query);
    if (!$fishes) {
        die("Invalid query: " . mysql_error());
    }

    $rows = array();
    while ($row = mysql_fetch_assoc($fishes)) {
        $rows[] = $row;
    }

    return $rows;
}

// mysql_close($connection);
// mysql_close($conn);