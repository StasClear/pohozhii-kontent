<?php

class actionRelevanterRelevantEdit extends cmsAction {

    public function run($relevant_id) {

        if (!$relevant_id) {
            cmsCore::error404();
        }

        $relevanter_model = cmsCore::getModel('relevanter');

        $relevant = $relevanter_model->getRelevantByField($relevant_id);

        if (!$relevant) {
            cmsCore::error404();
        }

        $content_model = cmsCore::getModel('content');

        $ctype = $content_model->getContentTypeByName($relevant['content']['ctype_name']);

        if (!$ctype) {
            cmsCore::error404();
        }

        $form = $this->getForm('relevant', array('edit', $relevant));

        $fields = $content_model->getContentFields($ctype['name']);

        $is_submitted = $this->request->has('submit');

        if ($is_submitted) {

            $relevant = $form->parse($this->request, $is_submitted);

            $relevant['filters'] = $this->request->get('filters');
            $relevant['sorting'] = $this->request->get('sorting');

            $errors = $form->validate($this, $relevant);

            if (!$errors) {
                $relevanter_model->updateRelevant($relevant_id, $relevant);
                $this->redirectToAction('relevants');
            }

            if ($errors) {
                cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
            }

        }

        return cmsTemplate::getInstance()->render('backend/relevant', array(
                    'do' => 'edit',
                    'relevant' => $relevant,
                    'form' => $form,
                    'fields' => $fields,
                    'errors' => isset($errors) ? $errors : false
        ));

    }

}
