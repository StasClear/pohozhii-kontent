<?php if ($field->title) { ?><label for="<?php echo $field->id; ?>"><?php echo $field->title; ?></label><?php } ?>

<?php if (isset($value)) {
    $value = is_array($value) ? $value : cmsModel::yamlToArray($value);
}

$relevants_list = $value ? array(0 => LANG_PARSER_FIELD_DELETE_RELEVANTER) : array(0 => LANG_PARSER_FIELD_SELECT_RELEVANTER);

echo html_select($field->element_name . "[relevant_name]", $relevants_list + $field->getRelevants(), isset($value['relevant_name']) ? $value['relevant_name'] : '0');
