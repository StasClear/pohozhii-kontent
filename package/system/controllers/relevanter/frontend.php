<?php

class relevanter extends cmsFrontend {

    public function renderRelevantsList($relevant, $data = array(), $request = false) {

        $template = cmsTemplate::getInstance();

        if (empty($data['tpl']['tpl_file'])) {

            $tpl_file = $template->getTemplateFileName("controllers/relevanter/tpl_" . $data['ctype']['name'] . "_" . $relevant['name'], true);

            if (!$tpl_file) {
                $tpl_file = $template->getTemplateFileName("controllers/relevanter/tpl_default_" . $relevant['name'], true);
            }
        } else {
            $tpl_file = $template->getTemplateFileName("controllers/relevanter/" . $data['tpl']['tpl_file'], true);
        }

        if (!$tpl_file) {
            $tpl_file = $template->getTemplateFileName("controllers/relevanter/tpl_default", true);
        }

        if (!$request) {
            $request = $this->controller->request;
        }

        return $template->processRender($tpl_file, $data, $request);

    }

    // Очистка текста от незначащих частей
    public function canonizeSearch($text, $rules = array()) {

        $except_word = isset($rules['except_word']) ? explode(",", str_replace(', ', ',', $rules['except_word'])) : array();
        $strtrim = false;
        $stopwords = array();

        // Приводим все символы к нижнему регистру
        $text = mb_strtolower($text);

        // Замена плохих слов
        $text = preg_replace("/[^\w\x7F-\xFF\s]/", " ", $text);

        if ($rules['clean_search']) {
            // Стоп-символы
            $stop_symbols = array(
                "1", "2", "3", "4", "5", "6", "7", "8", "9", "0",
                "~", "`", "!", "@", "\"", "#", "№", "$", ";", "%",
                "^", ":", "&", "?", "*", "(", ")", "-", "_", "+",
                "=", "\t", "{", "[", "]", "}", "\r", "\n", "\\", "/",
                "|", ",", ".");

            // Убираем стоп-символы
            $text = str_replace($stop_symbols, '', $text);
        }
        // 64 символа пользователю будет достаточно для поиска
        if (!empty($rules['search_lenght'])) {
            if (strlen($text) > $rules['search_lenght']) {
                $text = string_short($text, $rules['search_lenght']);
                $strtrim = true;
            }
        }

        $text = array_unique(preg_split("/[\s,]+/", $text));

        // Убираем последнее, усечённое слово
        if ($strtrim && count($text) > 1) {
            array_pop($text);
        }

        if ($rules['except_word_list']) {
            $stopwords = string_get_stopwords(cmsConfig::get('language'));
        }

        // Искать только по словам, которые длиннее четырёх букв
        if ($rules['word_lenght'] > 0 || $rules['except_word_list'] || $except_word) {
            foreach ($text as $k => $v) {
                if ($rules['word_lenght'] > 0 && (mb_strlen($v) < $rules['word_lenght'])) {
                    unset($text[$k]);
                    continue;
                }
                if ($rules['except_word_list'] && in_array($v, $stopwords)) {
                    unset($text[$k]);
                    continue;
                }
                if (in_array($v, $except_word)) {
                    unset($text[$k]);
                    continue;
                }
            }
        }

        // Упорядочиваем ключи
        $text = array_values($text);

        // Добавляем релевантность по первым трём словам
        for ($x = 0; $x < 3; $x++) {
            if (isset($text[$x])) {
                $text[$x] = '>' . $text[$x];
            }
        }

        return implode(', ', $text);

    }

    // Для администраторов выводим информацию для размышления, для остальных выводим "ничего"
    public function getEmptyInfo($relevant_id, $debug = false, $search = '') {

        if (cmsUser::isAdmin() && $debug) {
            return "<div class='relevants_not_found'><div>" . LANG_RELEVANTER_NOT_FOUND . "</div><div>" . $search . "</div><div class='text-right'><a href='" . href_to('admin', 'controllers/edit/relevanter/relevant_edit', $relevant_id) . "' target='_blank'>" . LANG_RELEVANTER_EDIT . "</a></div></div>";
        }

        return false;

    }

}
