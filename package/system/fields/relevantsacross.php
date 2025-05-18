<?php

class fieldRelevantsAcross extends cmsFormField {

    public $title = LANG_PARSER_FIELD_RELEVANTS_ACROSS;
    public $sql = "TINYINT( 1 ) NOT NULL DEFAULT '1'";

    public function __construct($request) {

        parent::__construct($request);
        cmsCore::loadControllerLanguage('relevanter');

    }

    public function getOptions() {

        return array(

            new fieldList('relevant_name', array(
                'title' => LANG_PARSER_FIELD_RELEVANTS_ACROSS_SELECT,
                'generator' => function () {
                    $relevants = cmsCore::getModel('relevanter')->getRelevants();
                    return array_collection_to_list($relevants, 'name', 'title');
                }
            )),

            new fieldCheckbox('relevant_set_view', array(
                'title' => LANG_PARSER_FIELD_RELEVANTS_SET_VIEW
            ))

        );

    }

    public function parse($value) {

        if (!$value || $value == 0) {
            return false;
        }

        if (!$this->is_in_list && !$this->is_in_item) {
            return false;
        }

        if (!cmsController::enabled('relevanter')) {
            return false;
        }

        $item = $this->item;

        $core = cmsCore::getInstance();
        $config = cmsConfig::getInstance();

        $is_ctype_default = ($config->ctype_default && $config->ctype_default == $this->item['ctype_name']);

        preg_match($is_ctype_default ? '/^([a-zA-Z0-9\-\/]+).html$/i' : '/^([a-z0-9\-_]+)\/([a-zA-Z0-9\-\/]+).html$/i', $core->uri, $is_item);

        return cmsEventsManager::hook('relevant_events', array(
            'relevant_name' => $this->getOption('relevant_name'),
            'current_ctype' => isset($item['ctype']) ? $item['ctype'] : array(),
            'current_ctype_category' => isset($item['category']) ? $item['category'] : array(),
            'current_ctype_item' => $item,
            'current_ctype_fields' => array(),
            'is_item' => $is_item
        ));

    }

    public function store($value, $is_submitted, $old_value = NULL) {

        $set_view = $this->getOption('relevant_set_view');

        if (!empty($set_view)) {
            return 1;
        }

        return $value ? 1 : 0;

    }

}
