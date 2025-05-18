<?php

class fieldRelevants extends cmsFormField {

    public $title = LANG_PARSER_FIELD_RELEVANTS;
    public $sql = 'varchar(255) NULL DEFAULT NULL';

    public function __construct($request) {

        parent::__construct($request);
        cmsCore::loadControllerLanguage('relevanter');

    }

    public function getRelevants() {

        $relevants = cmsCore::getModel('relevanter')->getRelevants();

        return array_collection_to_list($relevants, 'name', 'title');

    }

    public function parse($value) {

        $value = is_array($value) ? $value : cmsModel::yamlToArray($value);

        if (!$value) {
            return false;
        }

        if (!isset($value['relevant_name'])) {
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
            'relevant_name' => $value['relevant_name'],
            'current_ctype' => isset($item['ctype']) ? $item['ctype'] : array(),
            'current_ctype_category' => isset($item['category']) ? $item['category'] : array(),
            'current_ctype_item' => $item,
            'current_ctype_fields' => array(),
            'is_item' => $is_item
        ));

    }

    public function store($value, $is_submitted, $old_value = NULL) {
        return isset($value) && $value !== '0' ? $value : array();
    }

}
