<?php

/*
 * Advertisement DB management
 */
require_once 'fish_map_ads_model.php';
require_once 'fish_map_ads_db_views.php';

function ads_db_manage_page($user_login, $can_see_all = 0) {
    show_ads_header();

    //Show Ads table as default action
    if (!isset($_GET['ads_db_action']) && (!isset($_POST['ads_db_action']))) {
        show_add_button();

        $rows = get_ads_by_user($user_login, $can_see_all);
        show_ads_table($rows);
    }  //end show ads table default
    // if action = edit
    if ($_GET["ads_db_action"] == "edit") {
        if (!isset($_POST["submit"]) && !isset($_POST['delete'])) {
            $id = $_GET['entry'];
            if ($id != 'new') {
                $editrow = get_ad_by_id($id);
            }
            show_ads_insert_form($user_login, $editrow);
        } //end edit form

        if ($_POST['submit']) {

            $result = insert_ad($user_login, $_POST, $_GET);
            $errors = $result['errors'];
            $msg = $result['msg'];

            if (count($errors) > 0) {
                show_ads_insert_errors($errors);
            }
            if ($msg) {
                show_ads_ins_upd_del_result($msg); // Successful update or insert
                // Send e-mail to ygrabar@rivnefish.com
                send_email_notification($msg, $_POST, $_GET);
            }
        }  // end if ($_POST['submit'])

        if ($_POST['delete'] == "Видалити") {  //begin the delete function
            show_ads_delete_confirmation();
        }
        if ($_POST['delete'] == "Так") {
            $id = $_GET["entry"];
            delete_ad($id);
            show_ads_ins_upd_del_result("deleted");
            // Send e-mail to ygrabar@rivnefish.com
            send_email_notification('deleted', $_POST, $_GET);
        }
    } //end if ($_GET["ads_db_action"] == "edit")
}

function send_email_notification($action, $post_params, $get_params) {
    /*
     * $action      - one of 'updated', 'inserted', 'deleted'
     * $post_params - values
     * $get_params  - 'new' etc
     */
    $to = TO_EMAIL;
    $from = FROM_EMAIL;
    $subject = '[Рибні місця Рівненщини] Advertisement Notification';

    $message = 'Оновлення рекламних оголошень: ' . date("d M Y H:i:s") . "\r\n\r\n" .
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