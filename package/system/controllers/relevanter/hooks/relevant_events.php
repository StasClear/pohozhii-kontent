<?php

class onRelevanterRelevantEvents extends cmsAction {

    public function run($opt) {

        if (!$opt) {
            return false;
        }

        $relevant_name = isset($opt['relevant_name']) ? $opt['relevant_name'] : false;

        if (!$relevant_name) {
            return false;
        }

        $relevant = $this->model->getRelevantByField($relevant_name, 'name');

        if (!$relevant || empty($relevant['is_visible'])) {
            return false;
        }

        $config = cmsConfig::getInstance();
        $core = cmsCore::getInstance();
        $user = cmsUser::getInstance();
        $template = cmsTemplate::getInstance();
        $content_model = $core->getModel('content');

        $template->addCSS($template->getStylesFileName('relevanter'));

        // определяем переменные
        $current_ctype = isset($opt['current_ctype']) ? $opt['current_ctype'] : array();
        $current_ctype_category = isset($opt['current_ctype_category']) ? $opt['current_ctype_category'] : array();
        $current_ctype_item = isset($opt['current_ctype_item']) ? $opt['current_ctype_item'] : array();
        $current_ctype_fields = isset($opt['current_ctype_fields']) ? $opt['current_ctype_fields'] : array();
        $is_item = isset($opt['is_item']) ? $opt['is_item'] : array();

        $content = $relevant['content'];
        $tpl = $relevant['template'];
        $fulltext = $relevant['fulltext'];
        $filters = $relevant['filters'];
        $sorting = $relevant['sorting'];
        $get_subcats = !empty($content['subcategory']) ? true : false;
        $items_all = array();
        $ctype = array();
        $category = array();
        $subcats = array();
        $current_cat_id = false;
        $datasets = array();
        $fields = array();
        $search = array();
        $fulltext_search = false;
        $tag_ids = array();
        $slug = $core->request->has('slug', '') ? $core->request->get('slug', '') : false;
        $ctype_name = $core->request->has('ctype_name', '') ? $core->request->get('ctype_name', '') : false;

        // Получаем название типа контента и сам тип из настроек релеванта
        if (!empty($current_ctype_item['ctype_name']) && $content['ctype_name'] == $current_ctype_item['ctype_name']) {
            $ctype = $current_ctype;
        }

        if (!$ctype) {
            $ctype = $content_model->getContentTypeByName($content['ctype_name']);
        }

        // если указанный тип контента уже не существует, возвращаем false
        if (!$ctype) {
            return false;
        }

        if (!$current_ctype_item && $ctype_name && $slug) {
            $current_ctype_item = $content_model->getContentItemBySLUG($ctype_name, $slug);
        }

        $hash_ctype = md5($ctype['name']);
        $hash_item = md5($ctype['name'] . $core->uri);
        $hash_cats = md5($ctype['name'] . (!empty($current_ctype_item['category_id']) ? $current_ctype_item['category_id'] : $slug));

        // Получаем поля для данного типа контента
        if (!empty($current_ctype_item['ctype_name']) && $content['ctype_name'] == $current_ctype_item['ctype_name']) {
            $fields = $current_ctype_fields;
        }

        if (!$fields) {
            $fields = $content_model->getContentFields($ctype['name']);
        }

        // Включаем кеширование, если есть кеш, используем его.
        if ($config->cache_enabled) {

            $cache_key = "relevants.{$relevant['name']}.{$hash_item}";
            $cache_key_cats = "relevants.{$relevant['name']}_cats.{$hash_cats}";
            $cache_key_subcats = "relevants.{$relevant['name']}_subcats.{$hash_cats}";
            $cache_key_datasets = "relevants.datasets.{$hash_ctype}";

            $cache = cmsCache::getInstance();

            $items_all = $cache->get($cache_key);
            $category = $cache->get($cache_key_cats);
            $subcats = $cache->get($cache_key_subcats);
            $datasets = $cache->get($cache_key_datasets);

        }

        if (!$items_all) {

            // список тегов
            if (!empty($content['tags_searcher']) && !empty($current_ctype_item['tags'])) {

                if (is_array($current_ctype_item['tags'])) {
                    $current_ctype_item['tags'] = implode(', ', array_values($current_ctype_item['tags']));
                }

                if ($user->is_admin && !empty($tpl['debug'])) {
                    $search[] = $current_ctype_item['tags'];
                }

                $tag_ids = $this->model->getTagsIds($current_ctype_item['tags']);

            }

            // Текущий выбранный набор
            $dataset = $content['dataset'] ? $content['dataset'] : false;

            if ($dataset) {

                // Получаем список наборов
                if (!$datasets) {
                    $datasets = $content_model->getContentDatasets($ctype['id'], true);
                }

            }

            if (empty($category['id'])) {

                // Получаем список подкатегорий для текущей
                if ((!empty($current_ctype_category['id']) || !empty($current_ctype_item['category_id'])) && !empty($content['this_category'])) {

                    $current_cat_id = $current_ctype_item['category_id'];

                    if (!$current_cat_id) {
                        $current_cat_id = $current_ctype_category['id'];
                    }

                    $category = $current_ctype_category;

                }

                if (!$current_cat_id && $slug != 'index') {

                    $category = $content_model->getCategoryBySLUG($ctype['name'], $slug);
                    $current_cat_id = $category['id'];

                }

                if (!$current_cat_id) {
                    $current_cat_id = $content['category_id'] ? $content['category_id'] : 1;
                }

                if ($ctype['is_cats']) {

                    if (!isset($category['id'])) {
                        $category = $content_model->getCategory($ctype['name'], $current_cat_id);
                    }

                    if (!$subcats && $get_subcats) {
                        $subcats = $content_model->getSubCategories($ctype['name'], $current_cat_id);
                    }

                    // Фильтр по категории
                    $content_model->filterCategory($ctype['name'], $category, $get_subcats);

                }

            }

            // Если есть наборы, применяем фильтры текущего
            // иначе будем сортировать по дате создания
            // Текущий выбранный набор
            $dataset = $content['dataset'] ? $content['dataset'] : false;

            if ($dataset) {

                // Если есть наборы, применяем их.
                if ($datasets && !empty($datasets[$dataset])) {
                    $content_model->applyDatasetFilters($datasets[$dataset]);
                }

            }

            // Применяем фильтры
            if ($filters && !empty($relevant['filters'])) {

                foreach ($relevant['filters'] as $filter) {

                    if (($filter['value'] === '') && !in_array($filter['condition'], array('nn', 'ni'))) {
                        continue;
                    }
                    if (empty($filter['condition'])) {
                        continue;
                    }

                    if ($filter['value'] !== '') {

                        $filter['value'] = string_replace_user_properties($filter['value']);

                        if ($is_item && !empty($current_ctype_item)) {

                            $filter['value'] = string_replace_keys_values($filter['value'], $current_ctype_item);

                            if (strpos($filter['value'], '+')) {
                                $val = explode('+', $filter['value']);
                                $filter['value'] = (int) $val[0] + (int) $val[1];
                            }

                            if (strpos($filter['value'], '-')) {
                                $val = explode('-', $filter['value']);
                                $filter['value'] = (int) $val[0] - (int) $val[1];
                            }

                        }

                    }

                    switch ($filter['condition']) {

                        // общие условия
                        case 'eq': $content_model->filterEqual($filter['field'], $filter['value']);
                            break;
                        case 'gt': $content_model->filterGt($filter['field'], $filter['value']);
                            break;
                        case 'lt': $content_model->filterLt($filter['field'], $filter['value']);
                            break;
                        case 'ge': $content_model->filterGtEqual($filter['field'], $filter['value']);
                            break;
                        case 'le': $content_model->filterLtEqual($filter['field'], $filter['value']);
                            break;
                        case 'nn': $content_model->filterNotNull($filter['field']);
                            break;
                        case 'ni': $content_model->filterIsNull($filter['field']);
                            break;

                        // строки
                        case 'lk': $content_model->filterLike($filter['field'], '%' . $filter['value'] . '%');
                            break;
                        case 'lb': $content_model->filterLike($filter['field'], $filter['value'] . '%');
                            break;
                        case 'lf': $content_model->filterLike($filter['field'], '%' . $filter['value']);
                            break;

                        // даты
                        case 'dy': $content_model->filterDateYounger($filter['field'], $filter['value']);
                            break;
                        case 'do': $content_model->filterDateOlder($filter['field'], $filter['value']);
                            break;
                    }
                }
            }

            // Применяем сортировку из компонента
            if ($sorting) {
                $content_model->orderByList($sorting);
            }

            // Скрываем записи из скрытых родителей (приватных групп и т.п.)
            $content_model->filterHiddenParents();
            // Скрываем непубликуемые записи
            $content_model->filterPublishedOnly();
            // Скрываем записи, не прошедшие модерацию
            $content_model->filterApprovedOnly();

            // поиск по тегам
            if (!empty($content['tags_searcher']) && !empty($current_ctype_item['tags'])) {

                $content_model->joinLeft('tags_bind', 't', "(t.target_id = i.id AND t.target_subject = '{$ctype['name']}' AND t.target_controller = 'content')");

                $content_model->filterStart();

                foreach ($tag_ids as $k => $tag_id) {

                    if ($k > 0) {
                        $content_model->filterOR();
                    }

                    $content_model->filterEqual('t.tag_id', $tag_id);

                }

                $content_model->filterEnd();

            } elseif (!empty($content['tags_searcher']) && empty($current_ctype_item['tags'])) {

                if ((!$fulltext['search1'] && !$fulltext['search2'] && !$fulltext['search3']) || (!$fulltext['title'] && !$fulltext['seo_keys'] && !$fulltext['content'] && !$fulltext['tags'])) {

                    $content_model->resetFilters();

                    return $this->getEmptyInfo($relevant['id'], $tpl['debug'], LANG_RELEVANTER_EMPTY_SEARCH_FRASE);

                }

            }

            // если это не категория и есть полнотекстовый поиск, то ничего не выводим
            if ($is_item && (($fulltext['search1'] || $fulltext['search2'] || $fulltext['search3']) && ($fulltext['title'] || $fulltext['seo_keys'] || $fulltext['content'] || $fulltext['tags']))) {

                // Запускаем полнотекстовый поиск
                if ($is_item && !empty($current_ctype_item) && ($fulltext['search1'] || $fulltext['search2'] || $fulltext['search3']) && ($fulltext['title'] || $fulltext['seo_keys'] || $fulltext['content'] || $fulltext['tags'])) {

                    $is_full_content = isset($fields['content']['options']['in_fulltext_search']) ? true : false;
                    $where = '';

                    if ($fulltext['title']) {
                        $where .= 'i.title, ';
                    }

                    if ($fulltext['seo_keys']) {
                        $where .= 'i.seo_keys, ';
                    }

                    if ($fulltext['content'] && $is_full_content) {
                        $where .= 'i.content, ';
                    }

                    if ($fulltext['tags']) {
                        $where .= 'i.tags, ';
                    }

                    $where = rtrim($where, ', ');

                    $search_text = array();

                    if ($fulltext['search1'] && isset($current_ctype_item[$fulltext['search1']])) {

                        if (is_array($current_ctype_item[$fulltext['search1']])) {
                            $search_text[] = implode(' ', $current_ctype_item[$fulltext['search1']]);
                        } else {
                            $search_text[] = $current_ctype_item[$fulltext['search1']];
                        }

                    }

                    if ($fulltext['search2'] && isset($current_ctype_item[$fulltext['search2']])) {

                        if (is_array($current_ctype_item[$fulltext['search2']])) {
                            $search_text[] = implode(' ', $current_ctype_item[$fulltext['search2']]);
                        } else {
                            $search_text[] = $current_ctype_item[$fulltext['search2']];
                        }

                    }

                    if ($fulltext['search3'] && isset($current_ctype_item[$fulltext['search3']])) {

                        if (is_array($current_ctype_item[$fulltext['search3']])) {
                            $search_text[] = implode(' ', $current_ctype_item[$fulltext['search3']]);
                        } else {
                            $search_text[] = $current_ctype_item[$fulltext['search3']];
                        }

                    }

                    $search_text = trim(implode(', ', $search_text));

                    if (!empty($search_text)) {

                        if (!empty($where) && !empty($search_text)) {

                            $rules_text = array(
                                'search_lenght' => isset($fulltext['search_lenght']) ? $fulltext['search_lenght'] : 80,
                                'word_lenght' => isset($fulltext['word_lenght']) ? $fulltext['word_lenght'] : false,
                                'clean_search' => isset($fulltext['clean_search']) ? $fulltext['clean_search'] : false,
                                'except_word' => isset($fulltext['except_word']) ? $fulltext['except_word'] : '',
                                'except_word_list' => isset($fulltext['except_word_list']) ? $fulltext['except_word_list'] : false
                            );

                            $fulltext_search = $this->canonizeSearch($search_text, $rules_text);

                            if ($user->is_admin && !empty($tpl['debug'])) {
                                $search[] = ' ' . preg_replace("/>/", " ", $fulltext_search);
                            }

                            $content_model->filter("MATCH ({$where}) AGAINST ('{$fulltext_search}' IN BOOLEAN MODE)");
                            $content_model->select("MATCH ({$where}) AGAINST ('{$fulltext_search}' IN BOOLEAN MODE)", "REL");
                            $content_model->order_by = 'REL DESC';

                        }

                    }

                }

            }

            $search = trim(implode(', ', $search));

            if (empty($search) && empty($tag_ids) && empty($fulltext_search)) {
                $content_model->resetFilters();
                return $this->getEmptyInfo($relevant['id'], $tpl['debug'], LANG_RELEVANTER_EMPTY_SEARCH_FRASE);
            }

            // убираем запись из самой себя.
            if ((!empty($is_item) || $content['ctype_name'] == $ctype_name) && !empty($current_ctype_item['id'])) {
                $content_model->filterNotEqual('i.id', $current_ctype_item['id']);
            }

            // Получаем количество и список записей
            $total = $content_model->getContentItemsCount($ctype['name']);

            $limit = $tpl['random'] ? $tpl['limit'] + 3 : $tpl['limit'];

            if ($total >= $limit) {
                $items_all = $content_model->limit($limit)->getContentItems($ctype['name']);
            } else {

                // если ничего не найдено
                if ($total == 0) {
                    $content_model->resetFilters();
                    return $this->getEmptyInfo($relevant['id'], $tpl['debug'], $search);
                }

                // Если найдено меньше, чем нужно, берём все
                if ($total > 0 && $total < $limit) {
                    $items_all = $content_model->getContentItems($ctype['name']);
                }
            }

            if ($config->cache_enabled) {
                $cache->set($cache_key, $items_all);
                $cache->set($cache_key_cats, $category);
                $cache->set($cache_key_subcats, $subcats);
                $cache->set($cache_key_datasets, $datasets);
            }

        }

        $limit = min(count($items_all), $tpl['limit']);

        if ($limit > 0 && $limit < $tpl['number_cols']) {
            $tpl['number_cols'] = $limit;
        }

        if ($limit > 1) {

            if ($tpl['random']) {
                shuffle($items_all);
            }

            $items = array_slice($items_all, 0, $limit, true);

        } elseif ($limit == 1) {
            $items = $items_all;
        } else {
            $content_model->resetFilters();
            return false;
        }

        // Рейтинг
        if ($ctype['is_rating'] && count($items) > 0 && !empty($tpl['show_details']) && !empty($tpl['show_rating'])) {

            $rating_controller = cmsCore::getController('rating', new cmsRequest(array(
                                'target_controller' => 'content',
                                'target_subject' => $ctype['name']
                                    ), cmsRequest::CTX_INTERNAL));

            $is_rating_allowed = cmsUser::isAllowed($ctype['name'], 'rate');

            foreach ($items as $id => $item) {
                $is_rating_enabled = $is_rating_allowed && ($item['user_id'] != $user->id);
                $items[$id]['rating_widget'] = $rating_controller->getWidget($item['id'], $item['rating'], $is_rating_enabled);
            }

        }

        $template->setContext($this);

        $html = $this->renderRelevantsList($relevant, array(
            'ctype' => $ctype,
            'fields' => $fields,
            'category' => $category,
            'subcats' => $subcats,
            'items' => $items,
            'tpl' => $tpl,
            'user' => $user
                ), new cmsRequest(array(), cmsRequest::CTX_INTERNAL));

        $template->restoreContext();

        return $template->renderInternal($this, 'relevants_view', array(
                    'items_list_html' => $html,
                    'search' => $search,
                    'relevant' => $relevant,
                    'user' => $user
                        ), $this->request);

    }

}
