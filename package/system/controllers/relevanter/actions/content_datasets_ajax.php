<?php

class actionRelevanterContentDatasetsAjax extends cmsAction {

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

        $content_model = $this->cms_core->getModel('content');

        $ctype = $content_model->getContentTypeByName($ctype_name);

        if (!$ctype) {
            cmsTemplate::getInstance()->renderJSON($list);
        }

        $datasets = $content_model->getContentDatasets($ctype['id']);

        if ($datasets) {
            $list = array('' => '') + array_collection_to_list($datasets, 'name', 'title');
        }

        cmsTemplate::getInstance()->renderJSON($list);

    }

}
