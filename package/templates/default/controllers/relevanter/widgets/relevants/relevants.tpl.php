<?php

echo cmsEventsManager::hook('relevant_events', array(
    'relevant_name' => $relevant_name,
    'current_ctype' => $current_ctype,
    'current_ctype_category' => $current_ctype_category,
    'current_ctype_item' => $current_ctype_item,
    'current_ctype_fields' => $current_ctype_fields,
    'is_item' => $is_item
));
