<?php

class actionRelevanterRelevantDelete extends cmsAction {

    public function run($relevant_id) {

        if (!$relevant_id) {
            cmsCore::error404();
        }

        cmsCore::getModel('relevanter')->deleteRelevant($relevant_id);

        $this->redirectBack();

    }

}
