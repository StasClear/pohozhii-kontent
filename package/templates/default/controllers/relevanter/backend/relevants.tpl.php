<?php

$this->setPageTitle(LANG_RELEVANTER_CP_RELEVANT);

$this->addToolButton(array(
    'class' => 'add',
    'title' => LANG_RELEVANTER_CP_RELEVANT_ADD,
    'href' => $this->href_to('relevant_add')
));

$this->renderGrid($this->href_to('list_relevants_ajax'), $grid);
