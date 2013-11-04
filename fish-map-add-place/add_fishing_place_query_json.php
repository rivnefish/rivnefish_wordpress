<?php

require_once 'add_fishing_place_model.php';
require_once 'add_fishing_place_exception.php';

//$result = array('error' => false, 'msg' => '', 'result' =>'', id' => array());

try {
    $result = insert_marker($_GET);
    $response = json_encode(array(
            'error' => false,
            'result' => $result
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