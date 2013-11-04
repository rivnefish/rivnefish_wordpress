<?php

require("fish_map_config.php");

// Get parameters from URL
$action = $_GET["action"];
$center_lat = $_GET["lat"];
$center_lng = $_GET["lng"];
$radius = $_GET["radius"];
$marker_id = $_GET["marker_id"];


// Start XML file, create parent node
$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);

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

// To use $wpdb for MySQL db access
// global $wpdb
// The haversine formula is an equation important in navigation, giving great-circle distances between two
// points on a sphere from their longitudes and latitudes. It is a special case of a more general formula
// in spherical trigonometry, the law of haversines, relating the sides and angles of spherical "triangles".
// Here's the SQL statement that will find the closest 20 locations that are within a radius of 25 miles to
// the 37, -122 coordinate. It calculates the distance based on the latitude/longitude of that row and the
// target latitude/longitude, and then asks for only rows where the distance value is less than 25, orders
// the whole query by distance, and limits it to 20 results. To search by kilometers instead of miles,
// replace 3959 with 6371.
// Search the rows in the markers table
if ($action == 'info') {
    // Select marker's info
    $query_marker = sprintf("SELECT name, paid_fish, contact, photo_url1, photo_url2, photo_url3, photo_url4
            FROM markers
            WHERE marker_id = %s
            LIMIT 1",
        mysql_real_escape_string($marker_id));

    $query_fish = sprintf("SELECT name, icon_url, icon_width, icon_height, weight_avg, weight_max, amount, article_url
            FROM markers_fishes
            INNER JOIN fishes USING (fish_id)
            WHERE marker_id = '%s'
            ORDER BY amount DESC",
        mysql_real_escape_string($marker_id));

    $query_passport = sprintf("SELECT url_suffix
            FROM passports
            WHERE marker_id = '%s'",
        mysql_real_escape_string($marker_id));
}
else if ($action == 'show') {
    $query = 'SELECT marker_id, name, address, lat, lng
        FROM markers WHERE approval IN ("approved","pending")';
}
else { // $action == 'search'
    $query = sprintf("SELECT marker_id, name, address, lat, lng,
        ( 6371 * acos( cos( radians('%s') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('%s') )
        + sin( radians('%s') ) * sin( radians( lat ) ) ) ) AS distance
        FROM markers
        HAVING distance < '%s'
        ORDER BY distance",
            mysql_real_escape_string($center_lat),
            mysql_real_escape_string($center_lng),
            mysql_real_escape_string($center_lat),
            mysql_real_escape_string($radius));
}

if ($action == 'info') {
    // Click the marker
    $result = mysql_query($query_marker);
    if (!$result) {
        die("Invalid query: " . mysql_error());
    }
    $passport = mysql_query($query_passport);
    if (!$passport) {
        die("Invalid query: " . mysql_error());
    }
    // For 1 marker, add XML node
    $marker_row = mysql_fetch_array($result);
    $passport_row = mysql_fetch_array($passport);
    $node = $dom->createElement("marker");
    $marker_node = $parnode->appendChild($node);
    $marker_node->setAttribute("name", $marker_row['name']);
    $marker_node->setAttribute("paid_fish", $marker_row['paid_fish']);
    $marker_node->setAttribute("contact", $marker_row['contact']);
    $marker_node->setAttribute("photo_url1", $marker_row['photo_url1']);
    $marker_node->setAttribute("photo_url2", $marker_row['photo_url2']);
    $marker_node->setAttribute("photo_url3", $marker_row['photo_url3']);
    $marker_node->setAttribute("photo_url4", $marker_row['photo_url4']);
    $marker_node->setAttribute("url_suffix", $passport_row['url_suffix']);

    $result = mysql_query($query_fish);
    if (!$result) {
        die("Invalid query: " . mysql_error());
    }
    // For the same marker, add info about fishes
    while ($row = @mysql_fetch_array($result)) {
        $node = $dom->createElement("fish");
        $fish_node = $parnode->appendChild($node);
        $fish_node->setAttribute("name", $row['name']);
        $fish_node->setAttribute("icon_url", $row['icon_url']);
        $fish_node->setAttribute("icon_width", $row['icon_width']);   // 40 - 101px
        $fish_node->setAttribute("icon_height", $row['icon_height']); // 28px
        $fish_node->setAttribute("weight_avg", $row['weight_avg']);
        $fish_node->setAttribute("weight_max", $row['weight_max']);
        $fish_node->setAttribute("amount", $row['amount']);
        $fish_node->setAttribute("article_url", $row['article_url']);
    }

}
else {
    // Search by distance or show all msrkers
    $result = mysql_query($query);
    if (!$result) {
        die("Invalid query: " . mysql_error());
    }
    // Iterate through the rows, adding XML nodes for each
    while ($row = @mysql_fetch_assoc($result)) {
        $node = $dom->createElement("marker");
        $newnode = $parnode->appendChild($node);
        $newnode->setAttribute("marker_id", $row['marker_id']);
        $newnode->setAttribute("name", $row['name']);
        $newnode->setAttribute("address", $row['address']);
        $newnode->setAttribute("lat", $row['lat']);
        $newnode->setAttribute("lng", $row['lng']);
        $newnode->setAttribute("distance", $row['distance']);
    }
}
mysql_close($connection);

header("Content-type: text/xml");
echo $dom->saveXML();

