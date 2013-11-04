<?php
/*
 * Advertisement DB Management View
 */

function show_ads_header() {
    ?>
    <h2>Сторінка керування рекламними оголошеннями</h2>

    <?php
}

function show_add_button() {
    ?>
    <form action="<?php echo $PHP_SELF; ?>" method="get">
        <input type="hidden" name="page" value="fish-map-ads-handle">
        <input type="hidden" name="ads_db_action" value="edit">
        <input type="hidden" name="entry" value="new">
        <input class="button-primary" type="submit" value="Додати оголошення">
    </form>

    <?php
}

function show_ads_table($rows) {
    ?>
    <div id="ads_db_table_div">
        <table id="ads_db_table" class="widefat" border="1">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Користувач</th>
                    <th scope="col">Заголовок</th>
                    <th scope="col">Червоний текст</th>
                    <th scope="col">Текст</th>
                    <th scope="col">Дата створення</th>
                    <th scope="col">Тривалість (год.)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row) { ?>
                    <tr>
                        <td>
                            <b>
                                <a title="Редагувати/видалити оголошення"
                                   href="admin.php?page=fish-map-ads-handle&ads_db_action=edit&entry=<?php echo $row['ad_id'] ?>" >
                                       <?php echo $row['ad_id'] ?>
                                </a>
                            </b>
                        </td>
                        <td><?php echo $row['user_login'] ?></td>
                        <td><?php echo $row['caption'] ?></td>
                        <td><?php echo $row['text_red'] ?></td>
                        <td><?php echo $row['text_main'] ?></td>
                        <td><?php echo $row['create_date'] ?></td>
                        <td><?php echo $row['duration_hours'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div> <!-- close div ads_db_table_div -->

    <?php
}

function show_ads_insert_form($user_login, $editrow) {
    ?>
    <form action="<?php echo $PHP_SELF; ?>" method="post" class="widefat" style="padding:7px; width:80%;">
        <input type="hidden" name="id" value="<?php echo $editrow["ad_id"] ?>"/>

        <b>ID</b>
        <small> (автоматичний лічільник)</small>
        <br />
        &nbsp;&nbsp;&nbsp;
        <input type="text" name="column_ad_id" value="<?php echo $editrow["ad_id"] ?>" size ="20" disabled/>
        <br /><br />
        <b>Користувач</b>
        <small> (логін користувача отримується автоматично)</small>
        <br />
        &nbsp;&nbsp;&nbsp;
        <input type="text" name="column_user_login" value="<?php echo ($editrow["user_login"]) ? $editrow["user_login"] : $user_login ?>" size ="62" disabled/>
        <br /><br />
        <b>Заголовок</b>
        <small> (заголовок оголошення, наприклад "Увага! Акція!")</small>
        <br>
        &nbsp;&nbsp;&nbsp;
        <input type="text" name="column_caption" value="<?php echo $editrow["caption"] ?>" size ="62" />
        <br /><br />
        <b>Червоний текст</b>
        <small> (текст червоного кольору, наприклад "Тільки сьогодні знижка 50%")</small>
        <br />
        &nbsp;&nbsp;&nbsp;
        <input type="text" name="column_text_red" value="<?php echo $editrow["text_red"] ?>" size ="62" />
        <br /><br />
        <b>Текст</b>
        <small> (текст оголошення, наприклад "На всі мормишки вітчизняного виробництва")</small>
        <br />
        &nbsp;&nbsp;&nbsp;
        <textarea name="column_text_main" rows=4 cols=60><?php echo $editrow["text_main"] ?></textarea>
        <br /><br />
        <b>Термін дії</b>
        <small> (тривалість показу оголошення в годинах)</small>
        <br />
        &nbsp;&nbsp;&nbsp;
        <input type="text" name="column_duration_hours" value="<?php echo $editrow["duration_hours"] ?>" size ="62" />
        <br /><br />

        <?php if (isset($editrow['ad_id'])) { ?>
            <input type="hidden" name="ads_db_action" value="edit">
            <input class="button" type="submit" name="submit" value="Оновити">
            <input class="button" type="submit" name="delete" value="Видалити">
            <input type='button' class='button' value='Відмінити'
                   onclick='window.location="admin.php?page=fish-map-ads-handle"'/>
               <?php } else { ?>
            <input class="button" type="submit" name="submit" value="Додати">
            <input type='button' class='button' value='Відмінити'
                   onclick='window.location="admin.php?page=fish-map-ads-handle"'/>
               <?php } ?>
    </form>
    <?php
}

function show_ads_insert_errors($errors) {
    ?>
    <div class="insert_error">На формі виникли наступні помилки:</div>
    <ul>
        <?php foreach ($errors as $error) { ?>
            <li><?php echo $error ?></li>
        <?php } ?>
    </ul>
    <input type="button" class="button" value="Повернутися" onclick="history.back(-1)" />

    <?php
}

function show_ads_ins_upd_del_result($msg) {
    ?>
    <?php if ($msg == 'updated') { ?>
        <div class="insert_success">Зроблено! Оголошення оновлено.</div>
    <?php } elseif ($msg == 'inserted') { ?>
        <div class="insert_success">Зроблено! Нове оголошення додано.</div>
    <?php } elseif ($msg == 'deleted') { ?>
        <div class="insert_success">Зроблено! Оголошення видалено.</div>
    <?php } ?>
    <form method="get">
        <input type="hidden" name="page" value="fish-map-ads-handle">
        <input class="button" type="submit" value="Повернутися">
    </form>
    <?php
}

function show_ads_delete_confirmation() {
    ?>
    <div class="insert_error">Увага! Ви точно хочете видалити рекламне оголошення?
        Тицьніть 'Так' щоб все-таки видалити, або 'Ні' щоб повернутися.</div>
    <form action="<?php echo $PHP_SELF; ?>" method="post">
        <input type="hidden" name="ads_db_action" value="edit">
        <input class="button" type="submit" name="delete" value="Так">
        <input type="button" class="button" value="Ні" onclick="history.back(-1)" />
    </form>
    <?php
}