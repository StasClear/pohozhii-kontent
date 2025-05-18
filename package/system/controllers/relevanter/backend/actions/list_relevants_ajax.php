<?php

class actionRelevanterListRelevantsAjax extends cmsAction {

    public function run() {

        if (!$this->request->isAjax()) {
            cmsCore::error404();
        }

        $grid = $this->loadDataGrid('relevants');

        $filter = array();

        $filter_str = $this->request->get('filter');

        if ($filter_str) {
            parse_str($filter_str, $filter);
            $this->model->applyGridFilter($grid, $filter);
        }

        $perpage = isset($filter['perpage']) ? $filter['perpage'] : admin::perpage;

        $this->model->setPerPage($perpage);

        $total = $this->model->getRelevantsCount();
        $pages = ceil($total / $perpage);

        $fields = $this->model->getRelevants();

        cmsTemplate::getInstance()->renderGridRowsJSON($grid, $fields, $total, $pages);

        $this->halt();

    }

}
