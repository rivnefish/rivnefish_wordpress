<?php

// Init WordPress environment to be able to call wp_get_current_user() etc.
// require_once( dirname(dirname(__FILE__)) . '/../../wp-load.php' );

require_once 'fish_map_ads_config.php';

// Opens READ-ONLY connection to a MySQL server
$connection = mysql_connect(EXT_DB_HOST, EXT_DB_USER, EXT_DB_PASSWORD);
if (!$connection) {
    die("Not connected : " . mysql_error());
}
mysql_set_charset(EXT_DB_CHARSET);
$db_selected = mysql_select_db(EXT_DB_NAME, $connection);
if (!$db_selected) {
    die("Can't use db : " . mysql_error());
}

/* Consider different algorithms
 * 1. From http://dev.mysql.com/doc/refman/5.6/en/mathematical-functions.html#function_rand
  To obtain a random integer R in the range i <= R < j, use the expression FLOOR(i + RAND() * (j – i)).
  $query_0 = "SELECT ad_id, caption, text_red, text_main FROM `advertisement`
  WHERE ad_id = (SELECT FLOOR( MIN(ad_id) + RAND() * (MAX(ad_id) - MIN(ad_id))) FROM `advertisement`)
  AND TIMESTAMPDIFF(HOUR, create_date, NOW()) <= duration_hours AND approval = 1
  ORDER BY ad_id LIMIT 1";
 * 2. ORDER BY RAND() algorithm
 */

function get_ad_for_view($args) {
    // Use ORDER BY RAND() algorithm
    $query = "SELECT ad_id, caption, text_red, text_main
        FROM `advertisement`
        WHERE TIMESTAMPDIFF(HOUR, create_date, NOW()) <= duration_hours
        AND approval = 1";

    /* Exclude given AD_ID from the results */
    if (!empty($args['ad_id'])) {
        $query .= " AND ad_id != " . $args['ad_id'];
    }

    $query .= " ORDER BY RAND() LIMIT 1";

    $result = mysql_query($query);
    if (!$result) {
        die("Invalid query: " . mysql_error());
    }

    $row = mysql_fetch_assoc($result);
    return $row;
}

function get_ads_by_user($user_login, $can_see_all = 0) {
    if ($can_see_all) {
        $query = sprintf("SELECT ad_id, user_login,
            caption, text_red, text_main,
            create_date, duration_hours
            FROM advertisement");
    } else {
        $query = sprintf("SELECT ad_id, user_login,
            caption, text_red, text_main,
            create_date, duration_hours
            FROM advertisement
            WHERE user_login = '%s'", mysql_real_escape_string($user_login));
    }

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

function get_ad_by_id($id) {
    $query = sprintf("SELECT ad_id, user_login,
        caption, text_red, text_main,
        create_date, duration_hours
        FROM advertisement
        WHERE ad_id = %s", mysql_real_escape_string($id));

    $result = mysql_query($query);
    if (!$result) {
        die("Invalid query: " . mysql_error());
    }

    $row = mysql_fetch_assoc($result);
    return $row;
}

function insert_ad($user_login, $args, $get_args) {
    $conn = mysql_connect(EXT_DB_HOST, EXT_DB_USER_RW, EXT_DB_PASSWORD_RW);
    if (!$conn) {
        die("Not connected: " . mysql_error());
    }
    mysql_set_charset(EXT_DB_CHARSET);
    $db_sel = mysql_select_db(EXT_DB_NAME, $conn);
    if (!$db_sel) {
        die("Can't use db: " . mysql_error());
    }

    $id = $args["id"];

    $results = array(
        'msg' => NULL,
        'errors' => array());

    $column_caption = $args["column_caption"];
    if (empty($column_caption)) {
        $column_caption = 'NULL';
        $results['errors'][] = "Поле 'Заголовок' є обов'язковим.";
    }
    else
        $column_caption = mysql_real_escape_string($column_caption);

    $column_text_red = $args["column_text_red"];
    if (empty($column_text_red)) {
        $column_text_red = 'NULL';
        $results['errors'][] = "Поле 'Червоний текст' є обов'язковим.";
    }
    else
        $column_text_red = mysql_real_escape_string($column_text_red);

    $column_text_main = $args["column_text_main"];
    if (empty($column_text_main)) {
        $column_text_main = 'NULL';
        $results['errors'][] = "Поле 'Текст' є обов'язковим.";
    }
    else
        $column_text_main = mysql_real_escape_string($column_text_main);

    $column_duration_hours = $args["column_duration_hours"];
    if (empty($column_duration_hours)) {
        $column_duration_hours = 'NULL';
        $results['errors'][] = "Поле 'Термін дії' є обов'язковим.";
    } elseif (!is_numeric($column_duration_hours)) {
        $column_duration_hours = 'NULL';
        $results['errors'][] = "Поле 'Термін дії' повинно бути додатнім цілим числом.";
    } elseif (intval($column_duration_hours) <= 0) {
        $column_duration_hours = 'NULL';
        $results['errors'][] = "Поле 'Термін дії' повинно бути додатнім цілим числом.";
    } else
        $column_duration_hours = intval($column_duration_hours);

    if (!$results['errors']) { // Form validation is passed
        if ($get_args['entry'] != 'new') { // Update the record
            $sqlupdate = "UPDATE advertisement
            SET caption='$column_caption',
                text_red='$column_text_red',
                text_main='$column_text_main',
                duration_hours='$column_duration_hours'
            WHERE ad_id='$id'
            LIMIT 1";
            $res = mysql_query($sqlupdate);
            if (!($res)) {
                die("ERROR: Can't update the record (" . mysql_error() .
                        ") Please revise form fields and try again.");
            }

            $results['msg'] = "updated";
        } else {  // Insert new record
            $sqlinsert = "INSERT INTO advertisement
            (user_login, caption, text_red, text_main, duration_hours)
            VALUES
                ('$user_login',
                 '$column_caption', '$column_text_red', '$column_text_main',
                 '$column_duration_hours')";
            $res = mysql_query($sqlinsert);
            if (!($res)) {
                die("ERROR: Can't insert the record (" . mysql_error() .
                        ") Please revise form fields and try again.");
            }

            $results['msg'] = "inserted";
        }
    }

    return $results;
}

function delete_ad($ad_id) {
    $conn = mysql_connect(EXT_DB_HOST, EXT_DB_USER_RW, EXT_DB_PASSWORD_RW);
    if (!$conn) {
        die("Not connected: " . mysql_error());
    }
    mysql_set_charset(EXT_DB_CHARSET);
    $db_sel = mysql_select_db(EXT_DB_NAME, $conn);
    if (!$db_sel) {
        die("Can't use db: " . mysql_error());
    }

    $sqldelete = "DELETE FROM advertisement WHERE ad_id='$ad_id' LIMIT 1";
    $res = mysql_query($sqldelete);
    if (!($res)) {
        die("ERROR: Can't delete the record (" . mysql_error() .
                ") Please try again.");
    }
}