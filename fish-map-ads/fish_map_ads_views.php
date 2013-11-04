<?php
/*
 * Advertisement Form View
 *
 * Shows Ad_Form inside the wrapper to be able to
 * refresh with wrapper with the new advertisement
 * e.g. $('#ads_wrapper').html(new_data);
 */
function show_ad($ad, $attr) {
    ?>
    <div id="ads_wrapper" style="width:<?php echo $attr['width']?>px">
        <?php echo ($ad) ? show_ad_form($ad): show_ad_empty(); ?>
    </div>

    <?php
}

function show_ad_form($ad) {
    ?>
        <form id="ad_form" name="ad_form" method="POST">
            <h4 class="ad-caption"><?php echo $ad['caption'] ?></h4>
            <div class="ad-red-text"><?php echo $ad['text_red'] ?></div>
            <div class="ad-main-text"><?php echo $ad['text_main'] ?></div>
            <div class="ad-next">
                <a id="ad-next-button" title="Наступне оголошення"
                   href="javascript:void(0)" onclick="showNextAd(<?php echo $ad['ad_id'] ?>)">&#x25B6;</a>
            </div>
        </form>

    <?php
}

function show_ad_empty() {
    ?>
        <form id="ad_form" name="ad_form" method="POST">
            <h4 class="ad-caption">Увага! Увага!</h4>
            <div class="ad-red-text">Тут можуть бути Ваші оголошення.</div>
            <div class="ad-main-text">Якщо хочете, щоб повідомлення про знижку, завезення товару,
                розпродаж і т.п. було опубліковане на головній сторінці сайту rivnefish.com -
                звертайтеся до адміністраторів сайту за адресою info@rivnefish.com</div>
            <div class="ad-next">
                <a id="ad-next-button" title="Наступне оголошення"
                   href="javascript:void(0)" onclick="showNextAd()">&#x25B6;</a>
            </div>
        </form>

    <?php
}