<?php

class actionRelevanterContentFieldsAjax extends cmsAction {

    public function run() {

        if (!$this->request->isAjax()) {
            cmsCore::error404();
        }

        if (!cmsUser::isAdmin()) {
            cmsCore::error404();
        }

        $ctype_name = $this->request->get('value');

        $list = array();

        if (!$ctype_name) {
            cmsTemplate::getInstance()->renderJSON($list);
        }

        $fields = $this->cms_core->getModel('content')->getContentFields($ctype_name);

        if ($fields) {
            $list = array('' => '') + array_collection_to_list($fields, 'name', 'title');
        }

        cmsTemplate::getInstance()->renderJSON($list);

    }

}
