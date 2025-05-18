<?php

class widgetRelevanterRelevants extends cmsWidget {

    public $is_cacheable = false;

    public function run() {

        if (!cmsController::enabled('relevanter') || !$this->getOption('relevant_name')) {
            return false;
        }

        $current_ctype = cmsModel::getCachedResult('current_ctype');
        $current_ctype_category = cmsModel::getCachedResult('current_ctype_category');
        $current_ctype_item = cmsModel::getCachedResult('current_ctype_item');
        $current_ctype_fields = cmsModel::getCachedResult('current_ctype_fields');

        $core = cmsCore::getInstance();
        $config = cmsConfig::getInstance();

        $is_ctype_default = ($config->ctype_default && $config->ctype_default == $core->request->get('ctype_name', ''));

        preg_match($is_ctype_default ? '/^([a-zA-Z0-9\-\/]+).html$/i' : '/^([a-z0-9\-_]+)\/([a-zA-Z0-9\-\/]+).html$/i', $core->uri, $is_item);

        return array(
            'relevant_name' => $this->getOption('relevant_name'),
            'current_ctype' => $current_ctype,
            'current_ctype_category' => $current_ctype_category,
            'current_ctype_item' => $current_ctype_item,
            'current_ctype_fields' => $current_ctype_fields,
            'is_item' => $is_item
        );

    }

}
