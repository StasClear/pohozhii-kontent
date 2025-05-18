<?php

class formWidgetRelevanterRelevantsOptions extends cmsForm {

    public function init() {

        return array(

            array(
                'type' => 'fieldset',
                'title' => LANG_OPTIONS,
                'childs' => array(

                    new fieldList('options:relevant_name', array(
                        'title' => LANG_WD_RELEVANTER_SELECT_RELEVANT,
                        'generator' => function() {

                            $relevants_list = array();

                            $relevants = cmsCore::getModel('relevanter')->getRelevants();

                            if ($relevants) {
                                foreach ($relevants as $key => $value) {
                                    $relevants_list[$value['name']] = $value['title'];
                                }
                            }

                            return $relevants_list;
                        }
                    ))

                )
            )

        );

    }

}
