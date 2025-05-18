<?php

class actionRelevanterRelevantAdd extends cmsAction {

    public function run() {

        $relevanter_model = cmsCore::getModel('relevanter');

        $relevant = array();

        $form = $this->getForm('relevant', array('add', $relevant));

        $is_submitted = $this->request->has('submit');

        if ($is_submitted) {

            $relevant = $form->parse($this->request, $is_submitted);

            $relevant['filters'] = $this->request->get('filters');
            $relevant['sorting'] = $this->request->get('sorting');

            $errors = $form->validate($this, $relevant);

            if (!$errors) {

                $result = $relevanter_model->addRelevant($relevant);

                if ($result) {
                    cmsUser::addSessionMessage(sprintf(LANG_RELEVANTS_CP_RELEVANT_CREATED, $relevant['title']), 'success');
                }

                $this->redirectToAction('relevants');

            }

            if ($errors) {
                cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
            }

        }

        return cmsTemplate::getInstance()->render('backend/relevant', array(
                    'do' => 'add',
                    'relevant' => $relevant,
                    'form' => $form,
                    'fields' => array(),
                    'errors' => isset($errors) ? $errors : false
        ));

    }

}
