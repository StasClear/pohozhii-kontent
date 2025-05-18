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
        $db = $core->db; // Get DB instance for escaping

        $template->addCSS($template->getStylesFileName('relevanter'));

        // определяем переменные
        $current_ctype = isset($opt['current_ctype']) ? $opt['current_ctype'] : array();
        $current_ctype_category = isset($opt['current_ctype_category']) ? $opt['current_ctype_category'] : array();
        $current_ctype_item = isset($opt['current_ctype_item']) ? $opt['current_ctype_item'] : array();
        $current_ctype_fields = isset($opt['current_ctype_fields']) ? $opt['current_ctype_fields'] : array();
        $is_item = isset($opt['is_item']) ? $opt['is_item'] : array();

        $content_settings = $relevant['content'];
        $tpl_settings = $relevant['template'];
        $fulltext_settings = $relevant['fulltext'];
        $filters = $relevant['filters'];
        $sorting_rules = $relevant['sorting'];

        $get_subcats = !empty($content_settings['subcategory']);
        $items_all = array();
        $ctype = array();
        $category = array();
        $subcats = array();
        $current_cat_id = false;
        $datasets = array();
        $fields = array();
        $search_debug_info = array();
        $fulltext_search_query = false;
        $tag_ids = array();
        $slug = $core->request->has('slug', '') ? $core->request->get('slug', '') : false;
        $ctype_name = $core->request->has('ctype_name', '') ? $core->request->get('ctype_name', '') : false;

        // Стандартный алиас для таблицы элементов контента в InstantCMS
        $table_alias = 'i';

        // Получаем название типа контента и сам тип из настроек релеванта
        if (!empty($current_ctype_item['ctype_name']) && $content_settings['ctype_name'] == $current_ctype_item['ctype_name']) {
            $ctype = $current_ctype;
        }

        if (!$ctype) {
            $ctype = $content_model->getContentTypeByName($content_settings['ctype_name']);
        }

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
        if (!empty($current_ctype_item['ctype_name']) && $content_settings['ctype_name'] == $current_ctype_item['ctype_name']) {
            $fields = $current_ctype_fields;
        }

        if (!$fields) {
            $fields = $content_model->getContentFields($ctype['name']);
        }
        
        // Включаем кеширование, если есть кеш, используем его.
        if ($config->cache_enabled) {
            $cache_key_prefix = "relevants.{$relevant['name']}";
            $cache_key = "{$cache_key_prefix}.items.{$hash_item}";
            $cache_key_cats = "{$cache_key_prefix}.cats.{$hash_cats}";
            $cache_key_subcats = "{$cache_key_prefix}.subcats.{$hash_cats}";
            $cache_key_datasets = "{$cache_key_prefix}.datasets.{$hash_ctype}";

            $cache = cmsCache::getInstance();

            $items_all = $cache->get($cache_key);
            if (!$items_all) {
                $category = $cache->get($cache_key_cats);
                $subcats = $cache->get($cache_key_subcats);
                $datasets = $cache->get($cache_key_datasets);
            }
        }

        if (!$items_all) {

            if (!empty($content_settings['tags_searcher']) && !empty($current_ctype_item['tags'])) {
                if (is_array($current_ctype_item['tags'])) {
                    $current_ctype_item['tags'] = implode(', ', array_values($current_ctype_item['tags']));
                }
                if ($user->is_admin && !empty($tpl_settings['debug'])) {
                    $search_debug_info[] = $current_ctype_item['tags'];
                }
                $tag_ids = $this->model->getTagsIds($current_ctype_item['tags']);
            }

            $dataset_name = $content_settings['dataset'] ? $content_settings['dataset'] : false;

            if ($dataset_name) {
                if (!$datasets) {
                    $datasets = $content_model->getContentDatasets($ctype['id'], true);
                }
            }

            if (empty($category['id'])) {
                if ((!empty($current_ctype_category['id']) || !empty($current_ctype_item['category_id'])) && !empty($content_settings['this_category'])) {
                    $current_cat_id = !empty($current_ctype_item['category_id']) ? $current_ctype_item['category_id'] : (!empty($current_ctype_category['id']) ? $current_ctype_category['id'] : false);
                    if (!empty($current_ctype_category['id']) && $current_cat_id == $current_ctype_category['id']){
                        $category = $current_ctype_category;
                    }
                }

                if (!$current_cat_id && $slug && $slug != 'index') {
                    $category_from_slug = $content_model->getCategoryBySLUG($ctype['name'], $slug);
                    if ($category_from_slug) {
                        $category = $category_from_slug;
                        $current_cat_id = $category['id'];
                    }
                }
                
                if (!$current_cat_id && !empty($content_settings['category_id'])) {
                     $current_cat_id = $content_settings['category_id'];
                }

                if ($ctype['is_cats']) {
                    if ($current_cat_id && empty($category['id'])) { 
                        $category_by_id = $content_model->getCategory($ctype['name'], $current_cat_id);
                        if($category_by_id) $category = $category_by_id;
                    }
                    if ($category && !empty($category['id'])) {
                        if (!$subcats && $get_subcats) {
                            $subcats = $content_model->getSubCategories($ctype['name'], $category['id']);
                        }
                        $content_model->filterCategory($ctype['name'], $category, $get_subcats);
                    }
                }
            }

            if ($dataset_name) {
                if ($datasets && !empty($datasets[$dataset_name])) {
                    $content_model->applyDatasetFilters($datasets[$dataset_name]);
                }
            }

            if ($filters && !empty($relevant['filters'])) {
                foreach ($relevant['filters'] as $filter) {
                    if (($filter['value'] === '') && !in_array($filter['condition'], array('nn', 'ni'))) {
                        continue;
                    }
                    if (empty($filter['condition']) || empty($filter['field'])) {
                        continue;
                    }

                    $filter_value = $filter['value'];

                    if ($filter_value !== '') {
                        $filter_value = string_replace_user_properties($filter_value);
                        if ($is_item && !empty($current_ctype_item)) {
                            $filter_value = string_replace_keys_values($filter_value, $current_ctype_item);
                            if (strpos($filter_value, '+')) {
                                $val_parts = explode('+', $filter_value);
                                if(count($val_parts) == 2 && is_numeric(trim($val_parts[0])) && is_numeric(trim($val_parts[1]))){
                                   $filter_value = (int) trim($val_parts[0]) + (int) trim($val_parts[1]);
                                }
                            } elseif (strpos($filter_value, '-')) {
                                $val_parts = explode('-', $filter_value);
                                 if(count($val_parts) == 2 && is_numeric(trim($val_parts[0])) && is_numeric(trim($val_parts[1]))){
                                   $filter_value = (int) trim($val_parts[0]) - (int) trim($val_parts[1]);
                                 }
                            }
                        }
                    }

                    switch ($filter['condition']) {
                        case 'eq': $content_model->filterEqual($filter['field'], $filter_value); break;
                        case 'gt': $content_model->filterGt($filter['field'], $filter_value); break;
                        case 'lt': $content_model->filterLt($filter['field'], $filter_value); break;
                        case 'ge': $content_model->filterGtEqual($filter['field'], $filter_value); break;
                        case 'le': $content_model->filterLtEqual($filter['field'], $filter_value); break;
                        case 'nn': $content_model->filterNotNull($filter['field']); break;
                        case 'ni': $content_model->filterIsNull($filter['field']); break;
                        case 'lk': $content_model->filterLike($filter['field'], '%' . $filter_value . '%'); break;
                        case 'lb': $content_model->filterLike($filter['field'], $filter_value . '%'); break;
                        case 'lf': $content_model->filterLike($filter['field'], '%' . $filter_value); break;
                        case 'dy': $content_model->filterDateYounger($filter['field'], $filter_value); break;
                        case 'do': $content_model->filterDateOlder($filter['field'], $filter_value); break;
                    }
                }
            }
            
            $content_model->filterHiddenParents();
            $content_model->filterPublishedOnly();
            $content_model->filterApprovedOnly();

            $is_explicit_sorting_applied = false;

            if (!empty($content_settings['tags_searcher']) && !empty($tag_ids)) { // Check $tag_ids not just $current_ctype_item['tags']
                $content_model->joinLeft('tags_bind', 't', "(t.target_id = {$table_alias}.id AND t.target_subject = '{$ctype['name']}' AND t.target_controller = 'content')");
                $content_model->filterStart();
                foreach ($tag_ids as $k => $tag_id) {
                    if ($k > 0) { $content_model->filterOR(); }
                    $content_model->filterEqual('t.tag_id', $tag_id);
                }
                $content_model->filterEnd();
            } elseif (!empty($content_settings['tags_searcher']) && empty($tag_ids) && !empty($current_ctype_item['tags'])) {
                 // Case: tags_searcher is on, current item has tags, but those tags didn't resolve to any tag_ids (e.g. new tags not in DB)
                 // This might mean no results if we strictly rely on found tag_ids.
                 // If the intent is to search for the text of these tags via fulltext if tag_ids are empty, that's a different logic.
                 // For now, if tag_ids is empty, this part of the query won't add filters.
                 // Let's check if we should return empty if tag searcher is on but no valid tags found.
                if ((!$fulltext_settings['search1'] && !$fulltext_settings['search2'] && !$fulltext_settings['search3']) || (!$fulltext_settings['title'] && !$fulltext_settings['seo_keys'] && !$fulltext_settings['content'] && !$fulltext_settings['tags'])) {
                    $content_model->resetFilters(); // Reset before returning
                    return $this->getEmptyInfo($relevant['id'], $tpl_settings['debug'], LANG_RELEVANTER_EMPTY_SEARCH_FRASE);
                }
            }


            if ($is_item && (($fulltext_settings['search1'] || $fulltext_settings['search2'] || $fulltext_settings['search3']) && ($fulltext_settings['title'] || $fulltext_settings['seo_keys'] || $fulltext_settings['content'] || $fulltext_settings['tags']))) {
                if (!empty($current_ctype_item)) {
                    // Check if 'content' field exists and is searchable
                    $is_full_content_searchable = false;
                    if (isset($fields['content']) && isset($fields['content']['options']['in_fulltext_search']) && $fields['content']['options']['in_fulltext_search']) {
                        $is_full_content_searchable = true;
                    }

                    $match_where_clauses = [];
                    if ($fulltext_settings['title']) { $match_where_clauses[] = "{$table_alias}.title"; }
                    if ($fulltext_settings['seo_keys']) { $match_where_clauses[] = "{$table_alias}.seo_keys"; }
                    if ($fulltext_settings['content'] && $is_full_content_searchable) { $match_where_clauses[] = "{$table_alias}.content"; }
                    if ($fulltext_settings['tags']) { $match_where_clauses[] = "{$table_alias}.tags"; }

                    $match_where_string = implode(', ', $match_where_clauses);
                    $search_text_sources = [];

                    if ($fulltext_settings['search1'] && isset($current_ctype_item[$fulltext_settings['search1']])) {
                        $search_text_sources[] = is_array($current_ctype_item[$fulltext_settings['search1']]) ? implode(' ', $current_ctype_item[$fulltext_settings['search1']]) : $current_ctype_item[$fulltext_settings['search1']];
                    }
                    if ($fulltext_settings['search2'] && isset($current_ctype_item[$fulltext_settings['search2']])) {
                        $search_text_sources[] = is_array($current_ctype_item[$fulltext_settings['search2']]) ? implode(' ', $current_ctype_item[$fulltext_settings['search2']]) : $current_ctype_item[$fulltext_settings['search2']];
                    }
                    if ($fulltext_settings['search3'] && isset($current_ctype_item[$fulltext_settings['search3']])) {
                        $search_text_sources[] = is_array($current_ctype_item[$fulltext_settings['search3']]) ? implode(' ', $current_ctype_item[$fulltext_settings['search3']]) : $current_ctype_item[$fulltext_settings['search3']];
                    }
                    
                    $raw_search_text = trim(implode(' ', $search_text_sources));

                    if (!empty($match_where_string) && !empty($raw_search_text)) {
                        $rules_text = array(
                            'search_lenght' => isset($fulltext_settings['search_lenght']) ? $fulltext_settings['search_lenght'] : 80,
                            'word_lenght' => isset($fulltext_settings['word_lenght']) ? $fulltext_settings['word_lenght'] : false,
                            'clean_search' => isset($fulltext_settings['clean_search']) ? $fulltext_settings['clean_search'] : false,
                            'except_word' => isset($fulltext_settings['except_word']) ? $fulltext_settings['except_word'] : '',
                            'except_word_list' => isset($fulltext_settings['except_word_list']) ? $fulltext_settings['except_word_list'] : false
                        );
                        $fulltext_search_query = $this->canonizeSearch($raw_search_text, $rules_text);

                        if ($user->is_admin && !empty($tpl_settings['debug'])) {
                            $search_debug_info[] = 'FT: ' . preg_replace("/>/", " ", $fulltext_search_query);
                        }
                        if ($fulltext_search_query){
                            $content_model->filter("MATCH ({$match_where_string}) AGAINST ('{$db->escape($fulltext_search_query)}' IN BOOLEAN MODE)");
                            $content_model->select("MATCH ({$match_where_string}) AGAINST ('{$db->escape($fulltext_search_query)}' IN BOOLEAN MODE)", "REL");
                            $content_model->orderBy('REL', 'DESC');
                            $is_explicit_sorting_applied = true;
                        }
                    }
                }
            }
            
            $final_search_debug_string = trim(implode(', ', $search_debug_info));
            
            // убираем запись из самой себя.
            if ((!empty($is_item) || $content_settings['ctype_name'] == $ctype_name) && !empty($current_ctype_item['id'])) {
                $content_model->filterNotEqual($table_alias . '.id', $current_ctype_item['id']);
            }

            // Применяем сортировку из настроек релеванта (UI)
            if ($sorting_rules) {
                $content_model->orderByList($sorting_rules); 
                $is_explicit_sorting_applied = true;
            }

            if (!$is_explicit_sorting_applied) {
                if (array_key_exists('date_pub', $fields) && !empty($fields['date_pub']['is_enabled'])) {
                    $content_model->orderBy($table_alias . '.date_pub', 'DESC');
                } else {
                    $content_model->orderBy($table_alias . '.id', 'DESC');
                }
            }
            
            $total = $content_model->getContentItemsCount($ctype['name']);
            $limit_to_fetch = !empty($tpl_settings['random']) ? (int)$tpl_settings['limit'] + 3 : (int)$tpl_settings['limit'];

            if ($total > 0) {
                 if ($total >= $limit_to_fetch && $limit_to_fetch > 0) { // ensure limit is positive
                    $items_all = $content_model->limit($limit_to_fetch)->getContentItems($ctype['name']);
                } else {
                    $items_all = $content_model->getContentItems($ctype['name']);
                }
            } else {
                 $content_model->resetFilters();
                 return $this->getEmptyInfo($relevant['id'], !empty($tpl_settings['debug']), $final_search_debug_string ?: LANG_RELEVANTER_NOT_FOUND);
            }

            if ($config->cache_enabled && $items_all) {
                $cache->set($cache_key, $items_all, $relevant_name);
                if (!empty($category)) $cache->set($cache_key_cats, $category, $relevant_name);
                if (!empty($subcats)) $cache->set($cache_key_subcats, $subcats, $relevant_name);
                if (!empty($datasets)) $cache->set($cache_key_datasets, $datasets, $relevant_name);
            }
        }

        if (!$items_all) { 
             return $this->getEmptyInfo($relevant['id'], !empty($tpl_settings['debug']), LANG_RELEVANTER_NOT_FOUND);
        }

        $display_limit = min(count($items_all), (int)$tpl_settings['limit']);

        if ($display_limit > 0 && $display_limit < (int)$tpl_settings['number_cols']) {
            $tpl_settings['number_cols'] = $display_limit;
        }
        
        $items_to_display = array();

        if ($display_limit > 0) {
            if (!empty($tpl_settings['random']) && count($items_all) > $display_limit) { 
                shuffle($items_all);
            }
            $items_to_display = array_slice($items_all, 0, $display_limit, true);
        } else {
            return $this->getEmptyInfo($relevant['id'], !empty($tpl_settings['debug']), LANG_RELEVANTER_NOT_FOUND);
        }

        if ($ctype['is_rating'] && count($items_to_display) > 0 && !empty($tpl_settings['show_details']) && !empty($tpl_settings['show_rating'])) {
            $rating_controller = cmsCore::getController('rating', new cmsRequest(array(
                'target_controller' => 'content',
                'target_subject' => $ctype['name']
            ), cmsRequest::CTX_INTERNAL));
            $is_rating_allowed = cmsUser::isAllowed($ctype['name'], 'rate');
            foreach ($items_to_display as $id => $item) {
                $is_rating_enabled = $is_rating_allowed && (!isset($item['user_id']) || $item['user_id'] != $user->id);
                $items_to_display[$id]['rating_widget'] = $rating_controller->getWidget($item['id'], $item['rating'], $is_rating_enabled);
            }
        }

        $template->setContext($this);
        $html = $this->renderRelevantsList($relevant, array(
            'ctype' => $ctype,
            'fields' => $fields,
            'category' => $category,
            'subcats' => $subcats,
            'items' => $items_to_display,
            'tpl' => $tpl_settings,
            'user' => $user
        ), new cmsRequest(array(), cmsRequest::CTX_INTERNAL));
        $template->restoreContext();

        return $template->renderInternal($this, 'relevants_view', array(
            'items_list_html' => $html,
            'search' => isset($final_search_debug_string) ? $final_search_debug_string : '',
            'relevant' => $relevant,
            'user' => $user
        ), $this->request);
    }
}
