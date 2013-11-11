<?php
/*
 * Transliteration:
 * iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text)
 */

function add_fish_place_form($user_login, $fishes) {
    ?>

    <div id="wrapper" >
        <div id="waiting" >
            <div>Будь ласка, зачекайте, йде обробка...</div>
            <p>
                <img src="<?php echo plugins_url('images/ajax-loader.gif', __FILE__) ?>"
                      title="Waiting" alt="Waiting" />
            </p>
        </div>
        <div id="add_place_result" ></div>

        <form id="add_place_form" method="POST" action="<?php echo admin_url('admin-ajax.php');?>">
            <input type="hidden" name="action" value="fish_map_add_place_save" />
            <h4 title="Додати водойму на мапу">Додати водойму на мапу</h4>
            <div id="form_params">
                <div >
                    <strong>Користувач</strong>
                    <small>(логін користувача отримується автоматично)</small>
                    <br/>
                    <input type="text"
                           id="column_user_login"
                           name="column_user_login" value="<?php echo $user_login ?>" readonly="1" />
                    <br/><br/>
                </div>
                <label for="column_marker_name"><strong>Назва водойми</strong></label>
                <small>(загально відома назва місця для рибалки, наприклад Біле Озеро, Бочаниця)</small>
                <br/>
                <input id="column_marker_name"
                       type="text"
                       name="column_marker_name"
                       value="" required="1" />
                <br/><br/>
                <label for="column_marker_lat"><strong>Координати на мапі</strong></label>
                <small>(широта та довгота в одиницях Google Maps)</small>
                <br/>
                <input id="column_marker_lat" type="text" name="column_marker_lat" value="" size="31" required="1" />
                <input id="column_marker_lng" type="text" name="column_marker_lng" value="" required="1" />
                <br/>
                <div id="map_canvas" class="google-map" ></div>
                <br/>
                <label for="column_marker_permit"><strong>Умови рибалки</strong></label>
                <small>(виберіть варіант зі списку)</small>

                <select id="column_marker_permit" name="column_marker_permit">
                    <option value="unknown" selected>невідомо</option>
                    <option value="free">безкоштовно</option>
                    <option value="paid">платно</option>
                    <option value="prohibited">заборонено</option>
                </select>
                <br/><br/>
                <label for="column_marker_contact"><strong>Контакт на водоймі</strong></label>
                <small>(телефон та ім'я чи т.п., щоб замовити місце, просто поговорити, розпитати)</small>
                <br/>
                <input id="column_marker_contact"
                       type="text"
                       name="column_marker_contact"
                       value="" />
                <br/><br/>
                <label for="column_marker_paid_fish"><strong>Вартість риболовлі</strong></label>
                <small>(ціна, час доби коли можна рибалити, вага риби на виніс і т.п.)</small>
                <br/>
                <input id="column_marker_paid_fish"
                       type="text"
                       name="column_marker_paid_fish"
                       value="" />
                <br/><br/>

                <label for="add_opts"><strong>Ввести додаткову інформацію:</strong></label>
                <input type="checkbox" name="add_opts" id="add_opts" value="true"
                       onchange="$('#additional_opts').toggle();"/>

                <div id="additional_opts" >
                    <div >
                        Дана форма на стідії розробки. Введені дані ігноруватимуться!</div>
                    <label for="fishes">Виберіть рибу</label>
                    <img src="<?php bloginfo('template_url'); ?>/images/info.gif"
                         id="fishes_tip"
                         alt="Info"
                         title="|На даній водоймі ловляться такі риби" />
                    <small>(виберіть кілька риб тримаючи Ctrl та клацаючи на рибі)</small>
                    <br/>
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
                    <input id="marker_address" type="text" name="address" value="" />
                    <br/><br/><br/>
                    <label for="marker_area"><strong>Площа водойми</strong></label>
                    <small>(в арах, 100 ар = 1 Га)</small>
                    <br/>
                    <input id="marker_area" type="text" name="area" value=""/>
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
                                <input id="marker_max_depth" type="text" name="max_depth" value="" ></td>
                            <td><label for="marker_average_depth"><strong>Середня глибина</strong></label>
                                <small>(в метрах)</small>
                                <br/>
                                <input id="marker_average_depth" type="text" name="average_depth" value="" /></td>
                        </tr>
                        <tr>
                            <td><label id="marker_24h_price" for="marker_24h_price"><strong>Варість ловлі за добу</strong></label>
                                <small>(в гривнях)</small>
                                <br/>
                                <input type="text" name="24h_price" value="" />    </td>
                            <td><label for="marker_dayhour_price"><strong>Варість ловлі за світловий день</strong></label>
                                <small>(в гривнях)</small>
                                <br/>
                                <input id="marker_dayhour_price" type="text" name="dayhour_price" value="" />    </td>
                        </tr>
                    </table>

                    <label for="marker_boat_usage"><strong>Чи можна користуватись човном?</strong></label>
                    <small>(якщо поставите галочку, то мабуть можна)</small>

                    <input id="marker_boat_usage" type="checkbox" name="boat_usage" value=""/>
                    <br/>
                    <br/>

                    <label for="marker_boat_usage"><strong>Коли можна рибалити</strong></label>
                    <small>(виберіть варіант зі списку)</small>

                    <select>
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
