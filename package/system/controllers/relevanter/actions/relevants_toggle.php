<?php

class actionRelevanterRelevantsToggle extends cmsAction {

    public function run($relevant_id) {

        if (!$relevant_id) {
            cmsTemplate::getInstance()->renderJSON(array(
                'error' => true,
            ));
        }

        $relevanter_model = cmsCore::getModel('relevanter');

        $relevant = $relevanter_model->getRelevantByField($relevant_id);

        if (!$relevant) {
            cmsTemplate::getInstance()->renderJSON(array(
                'error' => true,
            ));
        }

        $is_visible = $relevant['is_visible'] ? false : true;

        $relevanter_model->toggleRelevantsVisibility($relevant_id, $is_visible);

        cmsTemplate::getInstance()->renderJSON(array(
            'error' => false,
            'is_on' => $is_visible
        ));

    }

}
