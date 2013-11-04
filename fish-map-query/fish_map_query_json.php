<?php

require_once 'fish_map_query_model.php';
require_once 'fish_map_table_views.php';
require_once 'fish_map_query_exception.php';

//$result = array('error' => false, 'msg' => '', 'rows' => array());

try {
    $markers = get_markers($_GET);
    $rows = fish_map_result_table($markers);
    $response = json_encode(array(
            'error' => false,
            'count' => count($markers),
            'rows' => $rows
        ));
} catch (IDException $exc) {
    $response = json_encode(array(
            'error' => true,
            'msg' => $exc->getMessage(),
            'id' => $exc->getId()
        ));
}

header('Content-Type: application/json');
echo $response;

exit;