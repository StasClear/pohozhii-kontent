<?php

class actionRelevanterFieldsAjax extends cmsAction {

    public function run() {

        if (!$this->request->isAjax()) {
            cmsCore::error404();
        }
        if (!cmsUser::isAdmin()) {
            cmsCore::error404();
        }

        $ctype_name = $this->request->get('value');

        if (!$ctype_name) {
            cmsTemplate::getInstance()->renderJSON(array());
        }

        $fields = $this->cms_core->getModel('content')->getContentFields($ctype_name);

        cmsTemplate::getInstance()->renderJSON($fields);

    }

}
