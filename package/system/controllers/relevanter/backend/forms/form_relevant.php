<?php

class formRelevanterRelevant extends cmsForm {

    public $is_tabbed = false;

    public function init($do, $relevant = array()) {

        $cats_list = array();
        $datasets_list = array();
        $con_fields_list = array();

        $fields_list = array(
            'rating' => LANG_RATING,
            'comments' => LANG_COMMENTS,
            'hits_count' => LANG_HITS,
            'user_id' => LANG_AUTHOR,
            'tags' => LANG_TAGS
        );

        $template = cmsTemplate::getInstance();

        if ($do == 'edit') {

            $content_model = cmsCore::getModel('content');

            $ctype = $content_model->getContentTypeByName($relevant['content']['ctype_name']);

            if ($ctype) {

                // список категорий контента
                $cats = $content_model->getCategoriesTree($ctype['name']);

                if ($cats) {

                    foreach ($cats as $cat) {

                        if ($cat['ns_level'] > 1) {
                            $cat['title'] = str_repeat('-', $cat['ns_level']) . ' ' . $cat['title'];
                        }

                        $cats_list[$cat['id']] = $cat['title'];
                    }
                }

                // список наборов контента
                $datasets = $content_model->getContentDatasets($ctype['id']);

                if ($datasets) {
                    $datasets_list = array('' => '') + array_collection_to_list($datasets, 'name', 'title');
                }

                // список полей контента
                $con_fields = $content_model->getContentFields($ctype['name']);

                if ($con_fields) {
                    $con_fields_list = array('' => '') + array_collection_to_list($con_fields, 'name', 'title');
                }

                $fields = $content_model->getContentFields($ctype['name']);

                if ($fields) {
                    $fields_list = $fields_list + array_collection_to_list($fields, 'name', 'title');
                }

            }

        }

        $presets = cmsCore::getModel('images')->getPresetsList();
        $presets['original'] = LANG_PARSER_IMAGE_SIZE_ORIGINAL;

        return array(
            'basic' => array(
                'title' => LANG_BASIC_OPTIONS,
                'type' => 'fieldset',
                'childs' => array(
                    new fieldString('name', array(
                        'title' => LANG_SYSTEM_NAME,
                        'hint' => $do == 'edit' ? LANG_SYSTEM_EDIT_NOTICE : false,
                        'rules' => array(
                            array('required'),
                            array('max_length', 20),
                            array('sysname'),
                            $do == 'add' ? array('unique_relevant') : false
                        )
                            )),
                    new fieldString('title', array(
                        'title' => LANG_TITLE,
                        'rules' => array(
                            array('required'),
                            array('max_length', 100)
                        )
                            )),
                    new fieldString('description', array(
                        'title' => LANG_DESCRIPTION,
                        'rules' => array(
                            array('max_length', 255)
                        )
                            )),
                    new fieldCheckbox('is_visible', array(
                        'title' => LANG_RELEVANTS_CP_IS_VISIBLE,
                        'default' => true
                            )),
                )
            ),
            'content' => array(
                'title' => LANG_RELEVANTS_CP_CONTENT,
                'type' => 'fieldset',
                'childs' => array(
                    new fieldList('content:ctype_name', array(
                        'title' => LANG_CONTENT_TYPE,
                        'disable_array_key_rules' => true,
                        'generator' => function($item) {

                            $tree = cmsCore::getModel('content')->getContentTypes();

                            $items = array('0' => 'Выберите тип контента');

                            if ($tree) {
                                foreach ($tree as $item) {
                                    $items[$item['name']] = $item['title'];
                                }
                            }
                            return $items;
                        },
                            )),
                    new fieldCheckbox('content:this_category', array(
                        'title' => LANG_RELEVANTER_CONTENT_THIS_CATEGORY,
                        'default' => true
                            )),
                    new fieldList('content:category_id', array(
                        'title' => LANG_CATEGORY,
                        'disable_array_key_rules' => true,
                        'parent' => array(
                            'list' => 'content:ctype_name',
                            'url' => href_to('relevanter', 'content_cats_ajax')
                        ),
                        'items' => $cats_list
                            )),
                    new fieldCheckbox('content:subcategory', array(
                        'title' => LANG_RELEVANTER_SUBCATEGORY,
                        'default' => false
                            )),
                    new fieldList('content:dataset', array(
                        'title' => LANG_RELEVANTER_LIST_DATASET,
//						'native_tag' => true,
                        'disable_array_key_rules' => true,
                        'parent' => array(
                            'list' => 'content:ctype_name',
                            'url' => href_to('relevanter', 'content_datasets_ajax')
                        ),
                        'items' => $datasets_list
                            )),
                    new fieldCheckbox('content:tags_searcher', array(
                        'title' => LANG_RELEVANTER_TAGS_SEARCHER,
                        'default' => false
                            )),
                )
            ),
            'template' => array(
                'title' => LANG_RELEVANTS_CP_TEMPLATE,
                'type' => 'fieldset',
                'childs' => array(
                    new fieldList('template:tpl_file', array(
                        'title' => LANG_RELEVANTS_FILE_TEMPLATE,
                        'default' => 'tpl_default',
                        'disable_array_key_rules' => true,
                        'generator' => function() use($template) {
                            return $template->getAvailableTemplatesFiles('controllers/relevanter', 'tpl_*.tpl.php');
                        }
                            )),
                    new fieldCheckbox('template:show_title', array(
                        'title' => LANG_RELEVANTER_SHOW_TITLE,
                        'default' => true
                            )),
                    new fieldCheckbox('template:show_description', array(
                        'title' => LANG_RELEVANTER_SHOW_DESCRIPTION,
                        'default' => false
                            )),
                    new fieldList('template:teaser_field', array(
                        'title' => LANG_RELEVANTER_LIST_TEASER,
                        'hint' => LANG_RELEVANTER_LIST_TEASER_HINT,
                        'disable_array_key_rules' => true,
                        'parent' => array(
                            'list' => 'content:ctype_name',
                            'url' => href_to('relevanter', 'content_fields_ajax')
                        ),
                        'items' => $con_fields_list
                            )),
                    new fieldNumber('template:size_teaser', array(
                        'title' => LANG_RELEVANTER_LIST_SIZE_TEASER,
                        'default' => 350
                            )),
                    new fieldCheckbox('template:show_image', array(
                        'title' => LANG_RELEVANTER_SHOW_IMAGE,
                        'default' => false
                            )),
                    new fieldList('template:image_field', array(
                        'title' => LANG_RELEVANTER_LIST_IMAGE,
                        'hint' => LANG_RELEVANTER_LIST_IMAGE_HINT,
                        'disable_array_key_rules' => true,
                        'parent' => array(
                            'list' => 'content:ctype_name',
                            'url' => href_to('relevanter', 'content_fields_ajax')
                        ),
                        'items' => $con_fields_list
                            )),
                    new fieldList('template:image_size', array(
                        'title' => LANG_RELEVANTER_LIST_IMAGE_SIZE,
                        'default' => 'small',
//						'native_tag' => true,
                        'items' => $presets
                            )),
                    new fieldCheckbox('template:noimage', array(
                        'title' => LANG_RELEVANTER_NOIMAGE,
                        'hint' => LANG_RELEVANTER_NOIMAGE_HINT,
                        'default' => true
                            )),
                    new fieldCheckbox('template:show_category', array(
                        'title' => LANG_RELEVANTER_SHOW_CATEGORY,
                        'default' => false
                            )),
                    new fieldCheckbox('template:show_tags', array(
                        'title' => LANG_RELEVANTER_SHOW_TAGS,
                        'default' => false
                            )),
                    new fieldCheckbox('template:show_details', array(
                        'title' => LANG_RELEVANTER_LIST_DETAILS,
                        'default' => false
                            )),
                    new fieldCheckbox('template:show_rating', array(
                        'title' => LANG_RELEVANTER_SHOW_RATING,
                        'default' => false
                            )),
                    new fieldList('template:number_cols', array(
                        'title' => LANG_RELEVANTER_LIST_NUMBER_COLS,
                        'items' => array(
                            '1' => 1,
                            '2' => 2,
                            '3' => 3,
                            '4' => 4,
                            '6' => 6
                        )
                            )),
                    new fieldNumber('template:limit', array(
                        'title' => LANG_RELEVANTER_LIST_LIMIT,
                        'hint' => LANG_RELEVANTER_LIST_LIMIT_HINT,
                        'default' => 4,
                        'rules' => array(
                            array('required')
                        )
                            )),
                    new fieldCheckbox('template:random', array(
                        'title' => LANG_RELEVANTER_LIST_RANDOM
                            )),
                    new fieldCheckbox('template:debug', array(
                        'title' => LANG_RELEVANTER_DEBUG,
                        'hint' => LANG_RELEVANTER_DEBUG_HINT
                            )),
                )
            ),
            'fulltext' => array(
                'title' => LANG_RELEVANTER_FULLTEXT,
                'type' => 'fieldset',
                'childs' => array(
                    new fieldList('fulltext:search1', array(
                        'title' => LANG_RELEVANTER_FULLTEXT_SEARCH1,
                        'default' => 'title',
                        'items' => array(
                            '' => '',
                            'title' => LANG_TITLE,
                            'seo_keys' => LANG_SEO_KEYS,
                            'tags' => LANG_TAGS
                        )
                            )),
                    new fieldList('fulltext:search2', array(
                        'title' => LANG_RELEVANTER_FULLTEXT_SEARCH2,
                        'default' => '',
                        'items' => array(
                            '' => '',
                            'title' => LANG_TITLE,
                            'seo_keys' => LANG_SEO_KEYS,
                            'tags' => LANG_TAGS
                        )
                            )),
                    new fieldList('fulltext:search3', array(
                        'title' => LANG_RELEVANTER_FULLTEXT_SEARCH3,
                        'default' => '',
                        'items' => array(
                            '' => '',
                            'title' => LANG_TITLE,
                            'seo_keys' => LANG_SEO_KEYS,
                            'tags' => LANG_TAGS
                        )
                            )),
                    new fieldCheckbox('fulltext:title', array(
                        'title' => LANG_RELEVANTER_FULLTEXT_TITLE,
                        'default' => true
                            )),
                    new fieldCheckbox('fulltext:seo_keys', array(
                        'title' => LANG_RELEVANTER_FULLTEXT_SEO_KEYS,
                        'default' => false
                            )),
                    new fieldCheckbox('fulltext:tags', array(
                        'title' => LANG_RELEVANTER_FULLTEXT_TAGS,
                        'default' => false
                            )),
                    new fieldCheckbox('fulltext:content', array(
                        'title' => LANG_RELEVANTER_FULLTEXT_CONTENT,
                        'hint' => LANG_RELEVANTER_FULLTEXT_CONTENT_HINT,
                        'default' => false
                            )),
                    new fieldNumber('fulltext:search_lenght', array(
                        'title' => LANG_RELEVANTER_FULLTEXT_SEARCH_LENGHT,
                        'hint' => LANG_RELEVANTER_FULLTEXT_SEARCH_LENGHT_HINT,
                        'default' => 80,
                            )),
                    new fieldNumber('fulltext:word_lenght', array(
                        'title' => LANG_RELEVANTER_FULLTEXT_WORD_LENGHT,
                        'default' => 4,
                            )),
                    new fieldCheckbox('fulltext:clean_search', array(
                        'title' => LANG_RELEVANTER_FULLTEXT_CLEAN_SEARCH,
                            )),
                    new fieldCheckbox('fulltext:except_word_list', array(
                        'title' => LANG_RELEVANTER_FULLTEXT_EXCEPT_WORD_LIST,
                        'default' => false
                            )),
                    new fieldString('fulltext:except_word', array(
                        'title' => LANG_RELEVANTER_FULLTEXT_EXCEPT_WORD
                            ))
                )

            )

        );

    }

}
