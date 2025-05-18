<?php

class actionRelevanterContentCatsAjax extends cmsAction {

    public function run() {

        if (!$this->request->isAjax()) {
            cmsCore::error404();
        }
        if (!cmsUser::isAdmin()) {
            cmsCore::error404();
        }

        $ctype_name = $this->request->get('value', '');

        $cats_list = array();

        if (!$ctype_name) {
            cmsTemplate::getInstance()->renderJSON($cats_list);
        }

        $cats = $this->cms_core->getModel('content')->getCategoriesTree($ctype_name);

        if ($cats) {

            foreach ($cats as $cat) {

                if ($cat['ns_level'] > 1) {
                    $cat['title'] = str_repeat('-', $cat['ns_level']) . ' ' . $cat['title'];
                }

                $cats_list[$cat['id']] = $cat['title'];

            }

        }

        cmsTemplate::getInstance()->renderJSON($cats_list);

    }

}
