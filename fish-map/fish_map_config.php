<?php
$host = "localhost";
$username = "rivnefish_reader";
$database = "rivnefish_word";
$password = "torOvrewm%";
// dublicates
define('EXT_DB_HOST', 'localhost');             // You can connect to any DB you have access to
define('EXT_DB_NAME', 'rivnefish_word');        // The name of the database
define('EXT_DB_USER', 'rivnefish_reader');        // Your MySQL username
define('EXT_DB_PASSWORD', 'torOvrewm%');     // password
define('EXT_DB_CHARSET', 'utf8');               // charset (not necessary now, for future

// Конфігураційні змінні зверху можна використовувати, якщо БД хоститься
// довільно.
// Якщо таблиці з маркерами є шматком БД WordPress, то бреба використовувати $wpdb
//
// Механизм WordPress предоставляет несколько способов (методов) получения информации из БД:
// $wpdb->get_results($sql, $type); – получение всех строк результата запроса,
//   где $type может принимать следующие значения:
//     OBJECT – данные возвращаются в виде массива, где каждый элемент является
//              объектом, а его поля – это поля вашей таблицы в БД;
//     ARRAY_A – данные возвращаются в виде ассоциативного массива (хэша);
//     ARRAY_N – каждая строка данных представлена в виде нумерованного массива,
//               каждому полю будет присвоен числовой индекс (по порядку расположения полей в таблице БД).
// $wpdb->query($sql); – метод для выполнения так называемых “простых” запросов,
//                       применяется для обработки запросов INSERT, UPDATE, DELETE.
// $wpdb->get_row($sql, $type, $offset); – получение одной строки из всего результата запроса, где:
//     $type – см. выше;
//     $offset – номер строки, которая будет выбрана из всего результата
//               (хинт: можно генрировать $offset случайным образом и получать случайную строку из запроса).
// $wpdb->get_var($sql, $col_offset, $row_offset); – этот метод позволяет получить одно
//                                                   значение из всего результата запроса, соответственно:
//     $col_offset – см. выше;
//     $row_offset – номер столбца, из которого будет взят результат.

// Для витягання картинок використовувати
// $icon_url = plugins_url('img/markericon.png', __FILE__));
