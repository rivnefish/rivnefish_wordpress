<?php
/*
 * Transliteration:
 * iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text)
 */

function fish_map_query_form($fishes) {
    ?>
    <div id="wrapper">
        <div id="waiting" style="display: none;">
            <p>Будь ласка, зачекайте, йде пошук...</p>
            <p>
                <img src="<?php echo plugins_url('images/ajax-loader.gif', __FILE__) ?>"
                     title="Loader" alt="Loader" />
            </p>
        </div>
        <center>
            <form id="fish_map_form" name="fish_map_form" method="POST">
                <h4 title="Заховати/показати форму пошуку"
                    onclick="$('#form_params').toggle('fast');"
                    style="cursor:pointer;">Пошукова форма</h4>
                <div id="form_params">
                    <table id="form_params_table">
                        <tr>
                            <td>
                                <label for="name">Назва водойми:</label>
                                <img src="<?php bloginfo('template_url'); ?>/images/info.gif"
                                     id="name_tip"
                                     alt="Info"
                                     title="|Введіть хоча б кілька літер з назви водойми" />
                            </td>
                            <td>
                                <input type="text" name="name" id="name" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="permit_all">Всі водойми:</label>
                                <img src="<?php bloginfo('template_url'); ?>/images/info.gif"
                                     id="permit_all_tip"
                                     alt="Info"
                                     title="|Шукати всі водойми" />
                            </td>
                            <td>
                                <input type="radio" name="permit" id="permit_all" value="all"
                                       checked="checked"
                                       onchange="$('#paid_params').hide('fast');"/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="permit_free">Безкоштовна водойма:</label>
                                <img src="<?php bloginfo('template_url'); ?>/images/info.gif"
                                     id="permit_free_tip"
                                     alt="Info"
                                     title="|Шукати лише безкоштовні водойми" />
                            </td>
                            <td>
                                <input type="radio" name="permit" id="permit_free" value="free"
                                       onchange="$('#paid_params').hide('fast');"/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="permit_paid">Платна водойма:</label>
                            </td>
                            <td>
                                <input type="radio" name="permit" id="permit_paid" value="paid"
                                       onchange="$('#paid_params').toggle('fast');"/>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <div id="paid_params" style="display:none">
                                    <table>
                                        <tr>
                                            <td>
                                                <label for="price">вартість &le;:</label>
                                                <img src="<?php bloginfo('template_url'); ?>/images/info.gif"
                                                     id="price_tip"
                                                     alt="Info"
                                                     title="|Ціна рибалки не перевищує заданої" />
                                            </td>
                                            <td>
                                                <input type="text" name="price" id="price" value="" />
                                                <label for="price">грн.</label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label for="boat">дозволено користуватись човном:</label>
                                            </td>
                                            <td>
                                                <input type="checkbox" name="boat" id="boat" value="1" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label for="at_night">ловля вночі:</label>
                                            </td>
                                            <td>
                                                <input type="checkbox" name="at_night" id="at_night" value="24h" />
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="distance">Відстань від Рівного &le;:</label>
                                <img src="<?php bloginfo('template_url'); ?>/images/info.gif"
                                     id="distance_tip"
                                     alt="Info"
                                     title="|Відстань від Рівного до водойми не перевищує заданої" />
                            </td>
                            <td>
                                <input type="text" name="distance" id="distance" value="" />
                                <label for="distance">км.</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="fishes">Виберіть рибу:</label>
                                <img src="<?php bloginfo('template_url'); ?>/images/info.gif"
                                     id="fishes_tip"
                                     alt="Info"
                                     title="|Шукати водойми, де є хоча б ОДНА з вибраних риб" />
                            </td>
                            <td>
                                <select id="fishes" size="10" multiple="multiple" name="fishes[]">
                                    <?php foreach ($fishes as $fish) { ?>
                                        <option value="<?php echo $fish['fish_id'] ?>"><?php echo $fish['name'] ?></option>
                                        <?php
                                    } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="fish_weight">вага риби &ge;:</label>
                                <img src="<?php bloginfo('template_url'); ?>/images/info.gif"
                                     id="fish_weight_tip"
                                     alt="Info"
                                     title="|Вага риби більша або рівна за задану" />
                            </td>
                            <td>
                                <input type="text" name="fish_weight" id="fish_weight" value="" />
                                <label for="fish_weight">гр.</label>
                            </td>
                        </tr>
     <!--                    <tr>
                            <td>
                                <label for="add_opts">Застосувати додаткові параметри:</label>
                            </td>
                            <td>
                                <input type="checkbox" name="add_opts" id="add_opts" value="true"
                                       onchange="$('#additional_opts').toggle('fast');"/>
                            </td>
                        </tr> -->
                    </table>
                    <div id="additional_opts" style="display:none">
                        <table id="form_add_params_table">
                            <tr>
                                <td>
                                    <label for="fish_weight">вага риби &ge;:</label>
                                    <img src="<?php bloginfo('template_url'); ?>/images/info.gif"
                                         id="fish_weight_tip"
                                         alt="Info"
                                         title="|Вага риби більша або рівна за задану" />
                                </td>
                                <td>
                                    <input type="text" name="fish_weight" id="fish_weight" value="" />
                                    <label for="fish_weight">гр.</label>
                                </td>
                            </tr>
                            <!--tr>
                                <td>
                                    <label for="only_all_fishes">наявність всіх вибраних риб</label>
                                    <img src="<?php bloginfo('template_url'); ?>/images/info.gif"
                                         id="only_all_fishes_tip"
                                         alt="Info"
                                         title="|Шукати водойми, де є ВСІ вибрані риби" />
                                </td>
                                <td>
                                    <input type="checkbox" name="only_all_fishes" id="only_all_fishes" value="true" />
                                </td>
                            </tr-->
                        </table>
                    </div>
                    <p>
                        <input type="submit" name="search_submit" id="search_submit" class="art-button"
                               value="Пошук" />
                    </p>
                </div>
            </form>
        </center>
        <div id="fontsizer" style="display: none;"></div>
        <div id="fish_map_table_count" style="display: none;"></div>
        <div id="fish_map_table" style="display: none;"></div>
    </div>

    <?php
}
