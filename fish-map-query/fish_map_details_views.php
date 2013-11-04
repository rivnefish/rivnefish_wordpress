<?php
/*
 * VIEW for Fish Details table
 *
 */

function show_amount($amount) {
    if ($amount) {
        echo "<div>" . $amount . " / 10</div>";
    }
}

function show_weight($weight){
    if ($weight) {
        echo number_format($weight, 0, ',', ' ');
    }
}

function fish_map_details_table($fishes) {
?>
    <table class="fish_map_details">
        <thead>
            <tr>
                <th>Риба</th>
                <th>Середня вага, гр</th>
                <th>Максимальна вага, гр</th>
                <th>Кльов</th>
                <th>Примітка</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($fishes as $fish) { ?>
            <tr>
                <td style="text-align: left !important">
                    <img src="<?php echo $fish['icon_url'] ?>"
                         height="<?php echo $fish['icon_height'] ?>"
                         width="<?php echo $fish['icon_width'] ?>"
                         alt="Icon"/>
                    <a href="http://rivnefish.com/pages/fishes/"
                       title="Прочитати статтю про дану рибу"><?php echo $fish['name'] ?></a>
                </td>
                <td><?php show_weight($fish['weight_avg']); ?></td>
                <td><?php show_weight($fish['weight_max']); ?></td>
                <td style="font-weight: bold"><?php show_amount($fish['amount']); ?></td>
                <td style="text-align: left !important"><?php echo $fish['notes'] ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

<?php
    //return ob_get_clean();
}
