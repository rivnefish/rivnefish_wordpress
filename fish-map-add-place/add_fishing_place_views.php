<?php
/*
 * Transliteration:
 * iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text)
 */

function add_fish_place_form($last_marker_id, $user_login, $fishes, $countries, $regions, $districts) {
    ?>

    <div id="wrapper" style="text-align:center">
        <div id="waiting" style="display: none;">
            <div>Будь ласка, зачекайте, йде обробка...</div>
            <p>
                <img src="<?php echo plugins_url('images/ajax-loader.gif', __FILE__) ?>"
                     style="border:0" title="Waiting" alt="Waiting" />
            </p>
        </div>
        <div id="add_place_result" style="display: none;"></div>

        <form id="add_place_form" name="add_place_form" method="POST">
            <h4 title="Додати водойму на мапу">Додати водойму на мапу</h4>
            <div id="form_params">

                    <div style="display: none">
                        <strong>ID</strong>
                        <small>(автоматичний лічільник)</small>
                        <br/>&nbsp;&nbsp;&nbsp;
                        <input type="text"
                               id="column_marker_id"
                               name="column_marker_id"
                               value="<?php echo $last_marker_id ?>" size="20" readonly/>
                        <br/><br/>
                        <strong>Користувач</strong>
                        <small>(логін користувача отримується автоматично)</small>
                        <br/>&nbsp;&nbsp;&nbsp;
                        <input type="text"
                               id="column_user_login"
                               name="column_user_login" value="<?php echo $user_login ?>" size ="62" readonly/>
                        <br/><br/>
                    </div>
                <label for="column_marker_name"><strong>Назва водойми</strong></label>
                <small>(загально відома назва місця для рибалки, наприклад Біле Озеро, Бочаниця)</small>
                <br/>&nbsp;&nbsp;&nbsp;
                <input id="column_marker_name"
                       type="text"
                       name="column_marker_name"
                       value="" size="62" required/>
                <br/><br/>
                <label for="column_marker_lat"><strong>Координати на мапі</strong></label>
                <small>(широта та довгота в одиницях Google Maps)</small>
                <br/>&nbsp;&nbsp;&nbsp;
                <input id="column_marker_lat" type="text" name="column_marker_lat" value="" size="31" required/>
                <input id="column_marker_lng" type="text" name="column_marker_lng" value="" size="31" required/>
                <br/>
                <div id="map_canvas" style="width: 100%; height:300px; margin: 5px 0px;"></div>
                <br/>
                <label for="column_marker_permit"><strong>Умови рибалки</strong></label>
                <small>(виберіть варіант зі списку)</small>
                &nbsp;&nbsp;&nbsp;
                <select id="column_marker_permit" name="column_marker_permit" size="1">
                    <option value="unknown" selected>невідомо</option>
                    <option value="free">безкоштовно</option>
                    <option value="paid">платно</option>
                    <option value="prohibited">заборонено</option>
                </select>
                <br/><br/>
                <label for="column_marker_contact"><strong>Контакт на водоймі</strong></label>
                <small>(телефон та ім'я чи т.п., щоб замовити місце, просто поговорити, розпитати)</small>
                <br/>&nbsp;&nbsp;&nbsp;
                <input id="column_marker_contact"
                       type="text"
                       name="column_marker_contact"
                       value="" style="width:35em"/>
                <br/><br/>
                <label for="column_marker_paid_fish"><strong>Вартість риболовлі</strong></label>
                <small>(ціна, час доби коли можна рибалити, вага риби на виніс і т.п.)</small>
                <br/>&nbsp;&nbsp;&nbsp;
                <input id="column_marker_paid_fish"
                       type="text"
                       name="column_marker_paid_fish"
                       value="" style="width:35em"/>
                <br/><br/>

                <label for="add_opts"><strong>Ввести додаткову інформацію:</strong></label>
                <input type="checkbox" name="add_opts" id="add_opts" value="true"
                       onchange="$('#additional_opts').toggle();"/>

                <div id="additional_opts" style="display:none">
                    <div style="color:red; font-weight: bold; text-align: center">
                        Дана форма на стідії розробки. Введені дані ігноруватимуться!</div>
                    <label for="fishes">Виберіть рибу</label>
                    <img src="<?php bloginfo('template_url'); ?>/images/info.gif"
                         id="fishes_tip"
                         alt="Info"
                         title="|На даній водоймі ловляться такі риби" />
                    <small>(виберіть кілька риб тримаючи Ctrl та клацаючи на рибі)</small>
                    <br/>&nbsp;&nbsp;&nbsp;
                    <select id="fishes" size="10" multiple="multiple" name="fishes[]">
                        <?php foreach ($fishes as $fish) { ?>
                            <option value="<?php echo $fish['fish_id'] ?>"><?php echo $fish['name'] ?></option>
                        <?php }
                        ?>
                    </select>
                    <br/><br/>
                    <label for="marker_address"><strong>Адреса водойми</strong></label>
                    <small>(наприклад "село Бочаниця, Гощанський район, Рівненська область")</small>
                    <br/>
                    <input id="marker_address" type="text" name="address" value="" size="62"/>
                    <br/><br/>
                    <table>
                        <tr>
                            <td><label for="countries">Країна:</label></td>
                            <td><label for="regions">Область:</label></td>
                            <td><label for="districts">Район:</label></td>
                        </tr>
                        <tr>
                            <td>
                                <select id="countries" size="4" name="countries[]" style="width:10em">
                                    <option value="None" onclick="hideRegions(this)">невідомо</option>
                                    <?php foreach ($countries as $country) { ?>
                                        <option value="<?php echo $country['country_id'] ?>"
                                                onclick="initRegions(this)">
                                                    <?php echo $country['name'] ?>
                                        </option>
                                    <?php }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <select id="regions" size="4" name="regions[]" style="width:17em">
                                    <option value="None" onclick="hideDistrict(this)">невідомо</option>
                                    <?php foreach ($countries as $country) { ?>
                                        <optgroup label="<?php echo $country['name'] ?>"
                                                  id="country_<?php echo $country['country_id'] ?>"
                                                  style="display: none">
                                                      <?php
                                                      foreach ($regions as $region) {
                                                          if ($region['country_id'] == $country['country_id']) {
                                                              ?>
                                                    <option value="<?php echo $region['region_id'] ?>"
                                                            onclick="initDistricts(this)">
                                                    <?php echo $region['name'] ?>
                                                    </option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </optgroup>
                                    <?php }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <select id="districts" size="10" name="districts[]" style="width:17em">
                                    <option value="None">невідомо</option>
    <?php foreach ($regions as $region) { ?>
                                        <optgroup label="<?php echo $region['name'] ?>"
                                                  id="region_<?php echo $region['region_id'] ?>"
                                                  style="display: none">
                                                      <?php
                                                      foreach ($districts as $district) {
                                                          if ($district['region_id'] == $region['region_id']) {
                                                              ?>
                                                    <option value="<?php echo $district['district_id'] ?>">
                                                    <?php echo $district['name'] ?>
                                                    </option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </optgroup>
    <?php }
    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <br/>
                    <br/>
                    <label for="marker_area"><strong>Площа водойми</strong></label>
                    <small>(в арах, 100 ар = 1 Га)</small>
                    <br/>
                    <input id="marker_area" type="text" name="area" value="" size="62"/>
                    <br/>
                    <br/>

                    <label for="marker_content"><strong>Інформація про водойму</strong></label>
                    <small>(наприклад: <i>Став був клубним і ловити було заборонено, лише обраним по запрошеннях. Проте тепер ставок відкритий для ловлі для всіх бажаючих. Отже, ставок знаходиться десь за 150-200 м справа від в'їзду в с.Півче зі сторони Мізоча (8 км від Мізоча). Ставок чистий, незарозший, з твердим дном</i>)</small>
                    <br/>
                    <textarea id="marker_content"  placeholder="Введіть тут всю можливу корисну інформацію про рибне місце" name="content" rows="6" cols="62" maxlength="10000"/> </textarea>
                    <br/>
                    <br/>

                    <label for="marker_conveniences"><strong>Умови для відпочинку</strong></label>
                    <small>(чи є доступ до питної води, де ставити палатку, чи є мангали й столики і таке інше)</small>
                    <br/>
                    <textarea id="marker_conveniences" placeholder="Введіть тут всю можливу корисну інформацію про відпочинок" name="conveniences" rows="6" cols="62" maxlength="10000"/> </textarea>
                    <br/>
                    <br/>


                    <table width="50%" border="0" cellspacing="0" cellpadding="4">
                        <tr>
                            <td><label for="marker_max_depth"><strong>Максимальна глибина</strong></label>
                                <small>(в метрах)</small>
                                <br/>
                                <input id="marker_max_depth" type="text" name="max_depth" value="" size="62"/></td>
                            <td><label for="marker_average_depth"><strong>Середня глибина</strong></label>
                                <small>(в метрах)</small>
                                <br/>
                                <input id="marker_average_depth" type="text" name="average_depth" value="" size="62"/></td>
                        </tr>
                        <tr>
                            <td><label id="marker_24h_price" for="marker_24h_price"><strong>Варість ловлі за добу</strong></label>
                                <small>(в гривнях)</small>
                                <br/>
                                <input type="text" name="24h_price" value="" size="62"/>    </td>
                            <td><label for="marker_dayhour_price"><strong>Варість ловлі за світловий день</strong></label>
                                <small>(в гривнях)</small>
                                <br/>
                                <input id="marker_dayhour_price" type="text" name="dayhour_price" value="" size="62"/>    </td>
                        </tr>
                    </table>

                    <label for="marker_boat_usage"><strong>Чи можна користуватись човном?</strong></label>
                    <small>(якщо поставите галочку, то мабуть можна)</small>
                    &nbsp;&nbsp;&nbsp;
                    <input id="marker_boat_usage" type="checkbox" name="boat_usage" value=""/>
                    <br/>
                    <br/>

                    <label for="marker_boat_usage"><strong>Коли можна рибалити</strong></label>
                    <small>(виберіть варіант зі списку)</small>
                    &nbsp;&nbsp;&nbsp;
                    <select size="1">
                            <option value="24h">цілодобово</option>
                        <option value="daylight">лише світловий день</option>
                    </select>
                    <br/>
                    <br/>

                </div>
                <p>
                    <input type="submit" name="add_submit" id="add_submit" class="art-button"
                           value="Додати" />
                </p>


            </div>
        </form>
    </div>

    <?php
}
