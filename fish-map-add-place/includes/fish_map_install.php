<?php

define('FISH_MAP_DB_VER_OPTION', 'fish_map_db_ver');
define('FISH_MAP_DB_VER', '3');

function fish_map_install() {
    global $wpdb;
    $db_ver = get_option(FISH_MAP_DB_VER_OPTION, 0);
    if ($db_ver == 0) {
        $wpdb->query("
            CREATE TABLE `fishes` (
              `fish_id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `ukr_name` varchar(255) DEFAULT NULL,
              `eng_name` varchar(255) DEFAULT NULL,
              `icon_url` varchar(2083) NOT NULL COMMENT 'URL to the picture in Picasaweb',
              `icon_width` smallint(3) NOT NULL DEFAULT '45' COMMENT 'Ширина іконки риби (інакше InfoWindow буде плавати по розміру)',
              `icon_height` smallint(3) NOT NULL DEFAULT '28' COMMENT 'Висота іконки риби (інакше InfoWindow буде плавати по розміру)',
              `latin_name` varchar(255) DEFAULT NULL,
              `folk_name` varchar(255) DEFAULT NULL,
              `predator` enum('1','0') DEFAULT '0',
              `redbook` enum('1','0') DEFAULT NULL,
              `description` text,
              `article_url` varchar(200) DEFAULT NULL COMMENT 'Посилання на статтю з описом риби',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        $wpdb->query("
            CREATE TABLE `markers` (
              `marker_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(60) NOT NULL COMMENT 'Name of the marker',
              `address` varchar(100) DEFAULT NULL COMMENT 'Marler''s address (for geocoding)',
              `lat` float(10,6) NOT NULL COMMENT 'Latitude',
              `lng` float(10,6) NOT NULL COMMENT 'Longitude',
              `area` int(11) DEFAULT NULL COMMENT 'площа водойми в арах, 100 ар = 1 Га',
              `content` text COMMENT 'Content (propably in HTML form)',
              `conveniences` text COMMENT 'Наявність комфортабельних умови ',
              `contact` text COMMENT 'Контакт - і''мя, телефон',
              `max_depth` decimal(6,2) DEFAULT NULL COMMENT 'В метрах',
              `average_depth` decimal(6,2) DEFAULT NULL COMMENT 'В метрах',
              `distance_to_Rivne` int(11) DEFAULT NULL COMMENT 'В МЕТРАХ згідно google.maps.DistanceMatrixService',
              `permit` enum('free','paid','prohibited','unknown') NOT NULL DEFAULT 'free',
              `24h_price` decimal(6,2) unsigned DEFAULT NULL,
              `dayhour_price` decimal(6,2) unsigned DEFAULT NULL,
              `boat_usage` enum('1','0') DEFAULT NULL COMMENT 'Чи дозволено використання човна',
              `time_to_fish` enum('24h','daylight', 'unknown') DEFAULT '24h' COMMENT 'Дозволений час рибалки',
              `paid_fish` text COMMENT 'умови вилову риби',
              `note` text COMMENT 'примітка для мене',
              `note2` varchar(200) NOT NULL DEFAULT 'Немає інформації' COMMENT 'примітка для користувачів сайту',
              `photo_url1` varchar(2083) DEFAULT NULL,
              `photo_url2` varchar(2083) DEFAULT NULL,
              `photo_url3` varchar(2083) DEFAULT NULL,
              `photo_url4` varchar(2083) DEFAULT NULL,
              `approval` enum('approved','pending') NOT NULL DEFAULT 'pending' COMMENT 'Approval status of the marker',
              `create_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Datetime of the creation',
              `modify_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        $wpdb->query("
            CREATE TABLE `markers_fishes` (
              `marker_id` int(11) unsigned NOT NULL,
              `fish_id` smallint(4) unsigned NOT NULL,
              `weight_avg` smallint(5) DEFAULT NULL COMMENT 'Середня вага потенційного улову в грамах',
              `weight_max` smallint(5) DEFAULT NULL COMMENT 'Максимальна вага потенційного улову в грамах',
              `amount` tinyint(2) DEFAULT NULL COMMENT 'ймовірність впіймати рибу',
              `notes` text COMMENT 'примітка',
              KEY `fish_id` (`fish_id`),
              KEY `marker_id` (`marker_id`),
              CONSTRAINT `markers_fishes_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `markers_fishes_ibfk_2` FOREIGN KEY (`fish_id`) REFERENCES `fishes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        $db_ver = 1;
        add_option(FISH_MAP_DB_VER_OPTION, $db_ver);
    }

    if ($db_ver < 2) {
        $wpdb->query("
            ALTER TABLE `markers`
            CHANGE COLUMN `time_to_fish` `time_to_fish` ENUM('24h','daylight','unknown')
            DEFAULT '24h' COMMENT 'Дозволений час рибалки'");
    }

    if ($db_ver < 3) {
        $wpdb->query("
            CREATE  TABLE `markers_pictures` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
              `marker_id` INT UNSIGNED NOT NULL ,
              `picture_id` INT UNSIGNED NOT NULL ,
              PRIMARY KEY (`id`)
            )");
    }
    update_option(FISH_MAP_DB_VER_OPTION, FISH_MAP_DB_VER);
}

function fish_map_update_check() {
    if (get_option(FISH_MAP_DB_VER_OPTION, 0) != FISH_MAP_DB_VER) {
        fish_map_install();
    }
}

function fish_map_uninstall() {
}