<?php

function install_package(){

    $core = cmsCore::getInstance();

    if(!$core->db->getRowsCount('controllers', "name = 'relevanter' AND `author` = 'Loadырь'")){

        $core->db->query("INSERT INTO `{#}controllers` (`title`, `name`, `is_enabled`, `options`, `author`, `url`, `version`, `is_backend`, `is_external`) VALUES ('Похожий контент', 'relevanter', 1, NULL, 'Loadырь', 'http://www.instantcms.ru/users/loadir', '2.0', 1, 1);");

        $core->db->query("INSERT INTO `{#}widgets` (`controller`, `name`, `title`, `author`, `url`, `version`) VALUES ('relevanter', 'relevants', 'Похожий контент', 'Loadырь', 'http://www.instantcms.ru/users/loadir', '2.0');");

        $core->db->query("DROP TABLE IF EXISTS `{#}relevants`;");
        $core->db->query("CREATE TABLE IF NOT EXISTS `{#}relevants` ("
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
                . ") ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Таблица c фильтрами похожего контента' AUTO_INCREMENT=1;");

    } else {

        $core->db->query("UPDATE `{#}controllers` SET `version` = '2.0' WHERE `name` = 'relevanter' AND `author` = 'Loadырь';");

        $core->db->query("UPDATE `{#}widgets` SET `version` = '2.0' WHERE `name` = 'relevants' AND `author` = 'Loadырь';");

    }

    return true;

}
