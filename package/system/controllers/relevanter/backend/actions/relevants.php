<?php

class actionRelevanterRelevants extends cmsAction {

    public function run() {

        $grid = $this->loadDataGrid('relevants');

        return cmsTemplate::getInstance()->render('backend/relevants', array(
                    'grid' => $grid
        ));

    }

}
