<?php

$set_view = $field->getOption('relevant_set_view');

if (empty($set_view)) {
    echo '<label>' . html_checkbox($field->element_name, !isset($value) ? true : $value) . ' ' . LANG_RELEVANTS_IS_VISIBLE . '</label>';
}

?>