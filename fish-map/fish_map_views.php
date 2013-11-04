<?php

function fish_map_main_form() {
    ?>
    <div id="fish_map_main_form">
        <div id="map_search" style="padding:4px 4px;">
            <div>
                <input type="text" id="addressInput" size="25" autocomplete="on" title="Введіть адресу, наприклад Бочаниця"/>
                <select id="radiusSelect" title="Допустимі відстані 25км, 50км, 100км, 200км">
                    <option value="25" selected>25 km</option>
                    <option value="50">50 km</option>
                    <option value="100">100 km</option>
                    <option value="200">200 km</option>
                </select>
                <input type="button" onclick="searchLocations()"
                       value="Шукати"
                       title="Всі рибні точки на відстані 25km, 50km,... від введеної адреси"/>
                <label style="font-size:x-small; font-style:italic; display: none">(всі рибні точки на відстані 25km,
                    50km,... від введеної адреси)</label>
                <input type="button" style="float:right" onclick="setupAllMarkers()"
                       value="Показати всі точки" title="Показати всі точки"/>
            </div>
            <div><select id="locationSelect" style="width:100%; display:none"></select></div>
        </div>

<!--        <div id="map_canvas" style="width: 100%; min-width:872px; height:800px"></div>-->
        <div id="map_canvas" style="width: 100%; height:800px"></div>

        <div id="map_triggers" style="padding:4px 4px;">
            <input type="checkbox" id="GoogleMapTypeTrigger" style="vertical-align:middle"
                   onchange="hide_map_controls(this.checked);" />
            <label for="GoogleMapTypeTrigger"
                   style="vertical-align:middle">сховати всі елементи керування</label>
            <div style="float:right">
                <label for="GoogleScaleTrigger"
                       style="margin-left:170px; vertical-align:middle;">показати маштабну лінійку</label>
                <select id="GoogleScaleTrigger" onchange="change_scale_position(this.value);">
                    <option value="TOP_LEFT" />Зверху зліва
                    <option value="TOP" />Зверху посередині
                    <option value="TOP_RIGHT" />Зверху справа
                    <option value="LEFT" />Зліва
                    <option value="RIGHT" />Справа
                    <option value="BOTTOM_LEFT" selected />Знизу зліва
                    <option value="BOTTOM" />Знизу посередині
                    <option value="BOTTOM_RIGHT" />Знизу справа
                </select>
            </div>
        </div>
    </div>
    <?php
}
