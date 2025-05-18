<?php

class backendRelevanter extends cmsBackend {

    public function actionIndex() {

        $this->redirectToAction('relevants');

    }

    public function validate_unique_relevant($value) {

        $result = cmsCore::getModel('relevanter')->isFieldUnique('relevants', 'name', $value);

        if (!$result) {
            return ERR_VALIDATE_UNIQUE;
        }

        return true;

    }

}
