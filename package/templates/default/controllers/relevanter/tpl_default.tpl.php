<?php if ($items) { ?>

    <div class="relevants <?php echo $ctype['name']; ?>_list">
        <?php foreach ($items as $item) { ?>
            <div class="relevants_list_item cols_<?php echo $tpl['number_cols']; ?> row-in <?php echo $ctype['name']; ?>_list_item<?php if (!empty($item['is_vip'])) { ?> is_vip<?php } ?>">

                <?php if (!empty($tpl['show_image'])) { ?>

                    <?php if (!empty($tpl['image_field']) && !empty($item[$tpl['image_field']])) { ?>
                        <div class="photo">
                            <a href="<?php echo href_to($ctype['name'], $item['slug'] . '.html'); ?>">
                                <?php echo html_image($item[$tpl['image_field']], (!empty($tpl['image_size']) ? $tpl['image_size'] : 'small'), $item['title']); ?>
                            </a>
                        </div>
                    <?php } elseif (!empty($tpl['noimage'])) { ?>
                        <?php $image_size = !empty($tpl['image_size']) ? $tpl['image_size'] : 'small'; ?>
                        <div class="photo">
                            <a href="<?php echo href_to($ctype['name'], $item['slug'] . '.html'); ?>">
                                <img src="/templates/default/images/noimage_<?php echo file_exists('templates/default/images/noimage_' . $image_size . '.png') ? $image_size : 'small'; ?>.png" alt="<?php echo html_clean($item['title']); ?>" />
                            </a>
                        </div>
                    <?php } ?>
                <?php } ?>

                <?php if (!empty($tpl['show_title']) || !empty($tpl['show_description'])) { ?>
                    <div class="desc">
                        <?php if (!empty($tpl['show_title'])) { ?>
                            <div class="title">
                                <?php if ($item['parent_id']) { ?>
                                    <a class="parent_title" href="<?php echo href_to($item['parent_url']); ?>"><?php echo html_clean($item['parent_title']); ?></a>
                                    &rarr;
                                <?php } ?>
                                <a href="<?php echo href_to($ctype['name'], $item['slug'] . '.html'); ?>"><?php echo html_clean($item['title']); ?></a>
                            </div>
                        <?php } ?>

                        <?php if (!empty($tpl['show_description'])) { ?>
                            <div class="description">
                                <?php $desc = $tpl['teaser_field'] ? $item[$tpl['teaser_field']] : $item['content']; ?>
                                <?php echo html_clean($desc, $tpl['size_teaser']); ?>
                            </div>
                        <?php } ?>

                    </div>
                <?php } ?>

                <?php if (!empty($tpl['show_tags'])) { ?>
                    <?php $is_tags = $ctype['is_tags'] && !empty($ctype['options']['is_tags_in_list']) && $item['tags']; ?>
                    <?php if ($is_tags) { ?>
                        <div class="tags_bar"><?php echo html_tags_bar($item['tags']); ?></div>
                    <?php } ?>
                <?php } ?>

                <?php if (!empty($tpl['show_category']) && $item['category_id'] > 1 && !empty($subcats[$item['category_id']])) { ?>
                    <div class="cat"><a href="<?php echo href_to($ctype['name'], $subcats[$item['category_id']]['slug']) ?>"><?php echo $subcats[$item['category_id']]['title']; ?></a></div>
                <?php } ?>

                <?php if (!empty($tpl['show_details'])) { ?>

                    <?php
                    $show_bar = $ctype['is_rating'] ||
                            $fields['date_pub']['is_in_item'] ||
                            $fields['user']['is_in_item'] ||
                            !empty($ctype['options']['hits_on']) ||
                            !$item['is_pub'] ||
                            !$item['is_approved'];

                    ?>
                        <?php if ($show_bar) { ?>
                        <div class="info_bar">
                                <?php if ($ctype['is_rating'] && !empty($tpl['show_rating'])) { ?>
                                <div class="bar_item bi_rating">
                                <?php echo $item['rating_widget']; ?>
                                </div>
                                <?php } ?>
                                <?php if ($fields['date_pub']['is_in_list']) { ?>
                                <div class="bar_item bi_date_pub" title="<?php echo $fields['date_pub']['title']; ?>">
                                <?php echo $fields['date_pub']['handler']->parse($item['date_pub']); ?>
                                </div>
                                <?php } ?>
                                <?php if (!$item['is_pub']) { ?>
                                <div class="bar_item bi_not_pub">
                                <?php echo LANG_CONTENT_NOT_IS_PUB; ?>
                                </div>
                                <?php } ?>
                                <?php if ($fields['user']['is_in_list']) { ?>
                                <div class="bar_item bi_user" title="<?php echo $fields['user']['title']; ?>">
                                <?php echo $fields['user']['handler']->parse($item['user']); ?>
                                </div>
                    <?php if (!empty($item['folder_title'])) { ?>
                                    <div class="bar_item bi_folder">
                                        <a href="<?php echo href_to('users', $item['user']['id'], array('content', $ctype['name'], $item['folder_id'])); ?>"><?php echo $item['folder_title']; ?></a>
                                    </div>
                                <?php } ?>
                <?php } ?>
                <?php if ($ctype['is_comments']) { ?>
                                <div class="bar_item bi_comments">
                                    <a href="<?php echo href_to($ctype['name'], $item['slug'] . '.html'); ?>#comments" title="<?php echo LANG_COMMENTS; ?>"><?php echo intval($item['comments']); ?></a>
                                </div>
                                <?php } ?>
                                <?php if (!$item['is_approved']) { ?>
                                <div class="bar_item bi_not_approved">
                                <?php echo LANG_CONTENT_NOT_APPROVED; ?>
                                </div>
                        <?php } ?>
                        </div>
                <?php } ?>
            <?php } ?>
            </div>
    <?php } ?>
    </div>

<?php } else {
    echo LANG_LIST_EMPTY;
} ?>