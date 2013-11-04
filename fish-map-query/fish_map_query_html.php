<?php
/*
 * AJAX controller for Fish Details table
 *
 */

require_once 'fish_map_query_model.php';
require_once 'fish_map_details_views.php';

if (!empty($_POST['marker_id'])) {
    $fishes = get_fishes($_POST['marker_id']);
    $response = fish_map_details_table($fishes);
    echo $response;
} else {
    echo "<div style='font-style:italic'>Інформація невідома.</div>";
}

exit;