<?php

function install_package(){

    $core = cmsCore::getInstance();
    $db = $core->db; // Для краткости

    // Проверяем, существует ли уже контроллер
    $controller_exists = $db->getRowsCount('controllers', "name = 'relevanter' AND `author` = 'Loadырь'");

    if(!$controller_exists){

        // Добавление информации о контроллере
        $sql_controller = "INSERT INTO `{#}controllers` (`title`, `name`, `is_enabled`, `options`, `author`, `url`, `version`, `is_backend`, `is_external`) VALUES ('Похожий контент', 'relevanter', 1, NULL, 'Loadырь', 'http://www.instantcms.ru/users/loadir', '2.0', 1, 1);";
        if (!$db->query($sql_controller)) {
            cmsUser::addSessionMessage('Ошибка при добавлении записи в cms_controllers: ' . $db->error(), 'error');
            return false; // Ошибка при выполнении запроса
        }

        // Добавление информации о виджете
        $sql_widget = "INSERT INTO `{#}widgets` (`controller`, `name`, `title`, `author`, `url`, `version`) VALUES ('relevanter', 'relevants', 'Похожий контент', 'Loadырь', 'http://www.instantcms.ru/users/loadir', '2.0');";
        if (!$db->query($sql_widget)) {
            cmsUser::addSessionMessage('Ошибка при добавлении записи в cms_widgets: ' . $db->error(), 'error');
            // Здесь можно добавить логику отката предыдущей операции, если это критично
            return false; // Ошибка при выполнении запроса
        }

        // Удаление таблицы, если она существует (для чистой установки)
        // Ошибку DROP TABLE можно проигнорировать, если таблицы нет
        $db->query("DROP TABLE IF EXISTS `{#}relevants`;");

        // Создание основной таблицы для компонента
        $sql_create_table = "CREATE TABLE IF NOT EXISTS `{#}relevants` ("
                . "`id` int(11) NOT NULL AUTO_INCREMENT, "
                . "`name` varchar(20) DEFAULT NULL, "
                . "`title` varchar(100) DEFAULT NULL, "
                . "`description` varchar(255) DEFAULT NULL, "
                . "`is_visible` tinyint(1) DEFAULT NULL, "
                . "`content` text DEFAULT NULL, "
                . "`template` text DEFAULT NULL, "
                . "`fulltext` text DEFAULT NULL, "
                . "`filters` text DEFAULT NULL, "
                . "`sorting` text DEFAULT NULL, "
                . "PRIMARY KEY (`id`)"
                . ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблица c фильтрами похожего контента' AUTO_INCREMENT=1;";

        if (!$db->query($sql_create_table)) {
            cmsUser::addSessionMessage('Ошибка при создании таблицы cms_relevants: ' . $db->error(), 'error');
            // Здесь можно добавить логику отката предыдущих операций
            return false; // Ошибка при выполнении запроса
        }

        // Дополнительная проверка, создалась ли таблица
        if (!$db->isTableExists('relevants')) {
            cmsUser::addSessionMessage('Таблица cms_relevants не была создана, хотя запрос CREATE TABLE не вернул явной ошибки.', 'error');
            return false;
        }


    } else {
        // Если контроллер существует, обновляем его версию
        $sql_update_controller = "UPDATE `{#}controllers` SET `version` = '2.0' WHERE `name` = 'relevanter' AND `author` = 'Loadырь';";
        if (!$db->query($sql_update_controller)) {
            cmsUser::addSessionMessage('Ошибка при обновлении версии контроллера: ' . $db->error(), 'error');
            return false;
        }

        // Обновляем версию виджета
        $sql_update_widget = "UPDATE `{#}widgets` SET `version` = '2.0' WHERE `name` = 'relevants' AND `controller` = 'relevanter' AND `author` = 'Loadырь';";
        if (!$db->query($sql_update_widget)) {
            // Если виджета нет (например, был удален вручную), эта ошибка не критична для обновления контроллера,
            // но может быть полезна для информации. В идеале, здесь нужна логика добавления виджета, если он отсутствует.
            // Пока что просто выведем сообщение, но не будем прерывать установку, если обновление контроллера прошло.
            cmsUser::addSessionMessage('Предупреждение: Ошибка при обновлении версии виджета (возможно, он отсутствует): ' . $db->error(), 'warning');
        }
    }

    // Очистка кеша схемы таблиц, чтобы система "увидела" новую таблицу сразу
    $db->clearCache();
    cmsCache::getInstance()->clean('db_schema'); // Очистка кеша схемы таблиц InstantCMS

    cmsUser::addSessionMessage('Установка/обновление компонента "Похожий контент" успешно завершено (с учетом возможных предупреждений).', 'success');
    return true;

}

?>
