<?php

// Init WordPress environment
require_once( dirname( dirname( __FILE__ ) ) . '/../../wp-load.php' );

require_once 'fish_map_query_exception.php';

// Get pairs (fish_id, name) to init Fish Chooser
function get_fishes_for_view() {
    global $wpdb;
    return $wpdb->get_results("SELECT fish_id, name FROM fishes ORDER BY name", ARRAY_A);
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
    global $wpdb;

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
        /*
        // Does not work
        if (!empty($args['add_opts']) && !empty($args['only_all_fishes'])) {
            foreach ($args['fishes'] as $fish_id) {
                $query .= " AND fish_id = " . $fish_id;
            }
        } else {
            $query .= " AND fish_id IN (" . join(',', $args['fishes']) . ")";
        }*/
        $fish_ids = array_map('intval', $args['fishes']);
        $query .= " AND fish_id IN (" . join(',', $fish_ids) . ")";
    }

    if (!empty($args['name'])) {
        $query .= " AND markers.name LIKE '%" . mysql_real_escape_string($args['name']) . "%'";
    }

    /* BEGIN Additional criteria: countries, regions, districts*/
    if (!empty($args['fish_weight'])) {
        $weight_cond = sprintf(" AND (weight_avg >= %s OR weight_max >= %s)",
                    abs(floatval($args['fish_weight'])),
                    abs(floatval($args['fish_weight'])));
        $query .= $weight_cond;
    }
    /* END Additional criteria: countries, regions, districts*/

    $query .= "
        GROUP BY markers.marker_id
        ORDER BY markers.name";

    return $wpdb->get_results($query, ARRAY_A);
}

function get_fishes($marker_id) {
    global $wpdb;

    $query = $wpdb->prepare("SELECT name, icon_url, icon_width,
            icon_height, weight_avg, weight_max, amount, notes
        FROM markers_fishes
        INNER JOIN fishes USING (fish_id)
        WHERE marker_id = '%s'
        ORDER BY amount DESC", $marker_id);

    return $wpdb->get_results($query, ARRAY_A);
}
