<?php

require_once 'fish_map_query_model.php';
require_once 'fish_map_table_views.php';
require_once 'fish_map_query_exception.php';

try {
    $markers = get_markers($_GET);
    $rows = fish_map_result_table($markers);
    $response = array(
        'error' => false,
        'count' => count($markers),
        'rows' => $rows
    );
} catch (IDException $exc) {
    $response = array(
        'error' => true,
        'msg' => $exc->getMessage(),
        'id' => $exc->getId()
    );
}

header('Content-Type: application/json');
echo json_encode($response);

exit;