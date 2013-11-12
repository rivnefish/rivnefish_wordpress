<?php

function add_fish_place_form($user_login, $fishes) {
    ?>

    <div id="wrapper" >
        <div id="waiting" >
            <div>Будь ласка, зачекайте, йде обробка...</div>
            <p>
                <img src="<?php echo plugins_url('images/ajax-loader.gif', __FILE__); ?>"
                      title="Waiting" alt="Waiting" />
            </p>
        </div>
        <div id="add_place_result" ></div>

        <form id="add_place_form" method="POST" action="<?php echo admin_url('admin-ajax.php');?>">
            <input type="hidden" name="action" value="fish_map_add_place_save" />
            <div id="form_params">
                <div class="controls">
                    <label for="column_marker_name">Назва водойми</label>
                    <input id="column_marker_name"
                           type="text"
                           name="column_marker_name"
                           value="" required="1" />
                    <small>(загально відома назва місця для рибалки, наприклад Біле Озеро, Бочаниця)</small>
                </div>

                <div class="controls">
                    <label for="column_marker_lat">Координати на мапі</label>
                    <input id="column_marker_lat" type="hidden" name="column_marker_lat" value="" size="31" required="1" />
                    <input id="column_marker_lng" type="hidden" name="column_marker_lng" value="" required="1" />

                    <div id="map_canvas" class="google-map" ></div>
                    <small>(клікніть на карті, щоб позначити водойму)</small>
                </div>

                <div class="controls">
                    <label for="column_marker_permit">Умови рибалки</label>

                    <select id="column_marker_permit" name="column_marker_permit">
                        <option value="unknown" selected>невідомо</option>
                        <option value="free">безкоштовно</option>
                        <option value="paid">платно</option>
                        <option value="prohibited">заборонено</option>
                    </select>
                </div>

                <div class="controls">
                    <label for="column_marker_contact">Контакт на водоймі</label>
                    <input id="column_marker_contact"
                           type="text"
                           name="column_marker_contact"
                           value="" />
                    <small>(телефон та ім'я чи т.п., щоб замовити місце, просто поговорити, розпитати)</small>
                </div>

                <div class="controls">
                    <label for="column_marker_paid_fish">Вартість риболовлі</label>
                    <input id="column_marker_paid_fish"
                           type="text"
                           name="column_marker_paid_fish"
                           value="" />
                    <small>(ціна, час доби коли можна рибалити, вага риби на виніс і т.п.)</small>
                </div>


                <div class="controls">
                    <label for="add_opts">
                        Ввести додаткову інформацію:
                        <input type="checkbox" id="add_opts"/>
                    </label>
                </div>

                <div id="additional_opts" >
                    <div class="controls">
                        <label for="fishes">
                            Виберіть рибу
                            <img src="<?php bloginfo('template_url'); ?>/images/info.gif"
                             id="fishes_tip"
                             alt="Info"
                             title="|На даній водоймі ловляться такі риби" />
                        </label>
                        <select id="fishes" size="10" multiple="multiple" name="fishes[]">
                            <?php foreach ($fishes as $fish) { ?>
                                <option value="<?php echo $fish['fish_id'] ?>"><?php echo $fish['name'] ?></option>
                            <?php }
                            ?>
                        </select>
                        <div>
                            <small>(виберіть кілька риб тримаючи Ctrl та клацаючи на рибі)</small>
                        </div>
                    </div>

                    <div class="controls">
                        <label for="marker_address">Адреса водойми</label>
                        <input id="marker_address" type="text" name="address" value="" />
                        <small>(наприклад "село Бочаниця, Гощанський район, Рівненська область")</small>
                    </div>

                    <div class="controls">
                        <label for="marker_content">Інформація про водойму</label>
                        <textarea id="marker_content"
                                  placeholder="Введіть тут всю можливу корисну інформацію про рибне місце"
                                  name="content" rows="6" maxlength="10000"></textarea>
                        <small>(наприклад: <i>Став був клубним і ловити було заборонено, лише обраним по запрошеннях. Проте тепер ставок відкритий для ловлі для всіх бажаючих. Отже, ставок знаходиться десь за 150-200 м справа від в'їзду в с.Півче зі сторони Мізоча (8 км від Мізоча). Ставок чистий, незарозший, з твердим дном</i>)</small>

                    </div>

                    <div class="controls">
                        <label for="marker_conveniences">Умови для відпочинку</label>
                        <textarea id="marker_conveniences"
                                  placeholder="Введіть тут всю можливу корисну інформацію про відпочинок"
                                  name="conveniences" rows="6" cols="62" maxlength="10000"/></textarea>
                        <small>(чи є доступ до питної води, де ставити палатку, чи є мангали й столики і таке інше)</small>
                    </div>

                    <div class="controls">
                        <label for="marker_area">Площа водойми</label>
                        <input id="marker_area" type="text" name="area" value="" class="input-sm" />
                        <small>(в арах, 100 ар = 1 Га)</small>
                    </div>

                    <div class="controls">
                        <div class="col col-4">
                            <label for="marker_max_depth">Максимальна глибина</label>
                            <input id="marker_max_depth" type="text" name="max_depth" value=""
                                   class="input-sm">
                            <small>(в метрах)</small>
                        </div>
                        <div class="col col-4">
                            <label for="marker_average_depth">Середня глибина</label>
                            <input id="marker_average_depth" type="text" name="average_depth" value=""
                                   class="input-sm" />
                            <small>(в метрах)</small>
                        </div>
                    </div>

                    <div class="controls">
                        <div class="col col-4">
                            <label id="marker_24h_price" for="marker_24h_price">Варість ловлі за добу
                            </label>
                            <input type="text" name="24h_price" value=""
                                   class="input-sm" />
                            <small>(в гривнях)</small>
                        </div>
                        <div class="col col-4">
                            <label for="marker_dayhour_price">Варість ловлі за світловий день</label>
                            <input id="marker_dayhour_price" type="text" name="dayhour_price" value=""
                                   class="input-sm" />
                            <small>(в гривнях)</small>
                        </div>
                    </div>

                    <div class="controls">
                        <label for="marker_boat_usage">
                            Чи можна користуватись човном?
                            <input id="marker_boat_usage" type="checkbox" name="boat_usage" value=""/>
                        </label>
                        <small>(якщо поставите галочку, то мабуть можна)</small>
                    </div>

                    <div class="controls">
                        <label>Коли можна рибалити</label>
                        <select>
                            <option value="24h">цілодобово</option>
                            <option value="daylight">лише світловий день</option>
                        </select>
                    </div>

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
