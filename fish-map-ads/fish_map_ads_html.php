<?php

/*
 * AJAX controller for Show Next Ad button
 *
 * echo only advertisement form (with no parent wrapper)
 *
 */

require_once 'fish_map_ads_model.php';
require_once 'fish_map_ads_views.php';

$ad = get_ad_for_view($_POST);
$return_form = ($ad) ? show_ad_form($ad): show_ad_empty();
echo $return_form;

exit;