<?php

function grid_relevants($controller) {

    $options = array(
        'is_auto_init' => true,
        'is_sortable' => true,
        'is_filter' => true,
        'is_pagination' => true,
        'is_draggable' => false,
        'is_selectable' => false,
        'order_by' => 'id',
        'order_to' => 'asc',
        'show_id' => true
    );

    $columns = array(
        'id' => array(
            'title' => 'id',
            'width' => 30,
            'filter' => 'exact'
        ),
        'title' => array(
            'title' => LANG_TITLE,
            'href' => href_to($controller->root_url, 'relevant_edit', '{id}'),
            'filter' => 'like'
        ),
        'name' => array(
            'title' => LANG_SYSTEM_NAME,
            'width' => 150,
            'filter' => 'like'
        ),
        'description' => array(
            'title' => LANG_DESCRIPTION,
            'filter' => 'like'
        ),
        'is_visible' => array(
            'title' => LANG_PUBLICATION,
            'flag' => true,
            'flag_toggle' => href_to($controller->name, 'relevants_toggle', '{id}'),
            'width' => 90,
            'filter' => 'exact'
        )
    );

    $actions = array(
        array(
            'title' => LANG_EDIT,
            'class' => 'edit',
            'href' => href_to($controller->root_url, 'relevant_edit', '{id}'),
        ),
        array(
            'title' => LANG_DELETE,
            'class' => 'delete',
            'href' => href_to($controller->root_url, 'relevant_delete', array('{id}')),
            'confirm' => LANG_RELEVANTS_CP_RELEVANT_DELETE_CONFIRM
        )
    );

    return array(
        'options' => $options,
        'columns' => $columns,
        'actions' => $actions
    );

}
