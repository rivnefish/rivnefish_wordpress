<?php

function show_depth_area($avg_d, $max_d, $area) {
    if($avg_d) {
        echo "<div>Сер.: " . $avg_d . "м</div>";
    }
    if($max_d) {
        echo "<div>Макс.: " . $max_d . "м</div>";
    }
    if($area) {
        // $area / 100 перетворює ари в гектари
        $area_str = number_format($area/100, 2, ",", " ");
        $area_str = str_replace(" ", html_entity_decode("&nbsp;", ENT_COMPAT, "UTF-8"), $area_str);
        echo "<div>Площа: " . $area_str . "га</div>";
    }
}

function fish_map_result_table($markers) {
    ob_start();
?>
    <table id="fish_table_results" class="tablesorter">
        <thead>
            <tr>
                <th style="width: 5%" title="Сортувати">Водойма</th>
                <th style="width: 5%">Риба</th>                        <!-- non-sort -->
                <th style="width: 5%" title="Сортувати">Контакт</th>
                <th style="width: 5%">Умови вилову риби</th>           <!-- non-sort -->
                <th style="width: 5%">Глибини, площа</th>              <!-- non-sort -->
                <th>Інформація</th>                                    <!-- non-sor -->
                <th style="width: 1%">
                    <img src="http://rivnefish.com/wp-content/themes/seamore/images/info.gif"
                                   title="Примітки"
                                   alt="Notes"
                                   style="cursor:pointer; border:0 none; padding: 0;" />
                    <img src="http://rivnefish.com/wp-content/themes/seamore/images/comment.png"
                             title="Додавання коментаря"
                             alt="Comment"
                             style="cursor: pointer; border:0 none; padding: 0;" />
                </th>
            </tr>
        </thead>
        <tbody>
    <?php foreach ($markers as $marker) { ?>
                <tr>
                    <td><a href="http://rivnefish.com/?s=<?php echo $marker['name'] ?>&search=Search"
                           title="Шукати інформацію про дану водойму, звіти, дописи">
                            <?php echo $marker['name'] ?>
                        </a>
                    </td>
                    <td style="cursor: pointer;"
                        title="Показати/сховати деталі про рибу"
                        onclick="toggleFishDetails(<?php echo $marker['marker_id'] ?>)">
                        <a href="javascript:void(0)"><?php echo $marker['fishes'] ?></a>
                    </td>
                    <td><?php echo $marker['contact'] ?></td>
                    <td><?php echo $marker['paid_fish'] ?></td>
                    <td><?php show_depth_area($marker['average_depth'],
                                              $marker['max_depth'],
                                              $marker['area']); ?></td>

                    <td class="column_content"><?php echo $marker['content'] ?></td>
                    <td>
                        <img src="http://rivnefish.com/wp-content/themes/seamore/images/info.gif"
                             id="<?php echo $marker['marker_id'] ?>_note"
                             title="Оновлено: <?php echo $marker['modify_date'] ?>|Примітка: <?php echo $marker['note2'] ?>"
                             alt="Note"
                             style="cursor:pointer" />
                        <img src="http://rivnefish.com/wp-content/themes/seamore/images/comment.png"
                             id="add_comment_<?php echo $marker['marker_id'] ?>_tip"
                             title="Додати зауваження стосовно даної водойми"
                             alt="Comment"
                             style="cursor: pointer;"
                             onclick="addMarkersComment('<?php echo $marker['name'] ?>');"/>
                    </td>
                </tr>
                <tr id="fish_table_details_row_<?php echo $marker['marker_id'] ?>"
                    style="display:none">
                    <td colspan="8"
                        id="fish_table_details_<?php echo $marker['marker_id'] ?>">
                    </td>
                </tr>
    <?php } ?>
        </tbody>
    </table>

<?php
    return ob_get_clean();
}
