<?php if ($items) { ?>

    <?php
    $items_row = min($tpl['number_cols'], count($items));
    $row_md = bcdiv(12, $items_row);
    $row_sm = bcdiv(12, bcsub($row_md, 2));

    ?>

    <div class="row relevants content_list <?php echo $ctype['name']; ?>_list mb-n3 mb-md-n4">
    <?php foreach ($items as $item) { ?>
            <div class="relevants_list_item col-xs-12 col-sm-<?php echo $row_sm; ?> col-md-<?php echo $row_md; ?> <?php echo $ctype['name']; ?>_list_item tile mb-3 mb-md-4<?php if (!empty($item['is_vip'])) { ?> is_vip<?php } ?>">

                <div class="icms-content-fields d-flex flex-column h-100">

                    <?php if (!empty($tpl['show_image'])) { ?>

            <?php if (!empty($tpl['image_field']) && !empty($item[$tpl['image_field']])) { ?>
                            <div class="field ft_image">
                                <a href="<?php echo href_to($ctype['name'], $item['slug'] . '.html'); ?>">
                <?php echo html_image($item[$tpl['image_field']], (!empty($tpl['image_size']) ? $tpl['image_size'] : 'small'), $item['title']); ?>
                                </a>
                            </div>
                        <?php } elseif (!empty($tpl['noimage'])) { ?>
                <?php $image_size = !empty($tpl['image_size']) ? $tpl['image_size'] : 'small'; ?>
                            <div class="field ft_image">
                                <a href="<?php echo href_to($ctype['name'], $item['slug'] . '.html'); ?>">
                                    <img src="/templates/default/images/noimage_<?php echo file_exists('templates/default/images/noimage_' . $image_size . '.png') ? $image_size : 'small'; ?>.png" alt="<?php echo html_clean($item['title']); ?>" />
                                </a>
                            </div>
                        <?php } ?>
                    <?php } ?>

                        <?php if (!empty($tpl['show_title']) || !empty($tpl['show_description'])) { ?>
                        <div class="desc">
                                <?php if (!empty($tpl['show_title'])) { ?>
                                <h5>
                <?php if ($item['parent_id']) { ?>
                                        <a class="parent_title" href="<?php echo href_to($item['parent_url']); ?>"><?php echo html_clean($item['parent_title']); ?></a>
                                        &rarr;
                <?php } ?>
                                    <a href="<?php echo href_to($ctype['name'], $item['slug'] . '.html'); ?>"><?php echo html_clean($item['title']); ?></a>
                                </h5>
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
                            <div class="tags_bar mb-2"><?php echo html_tags_bar($item['tags'], 'content-' . $ctype['name'], 'btn btn-outline-secondary btn-sm mr-1 icms-btn-tag', ''); ?></div>
                        <?php } ?>
                    <?php } ?>

                    <?php if (!empty($tpl['show_category']) && $item['category_id'] > 1 && !empty($subcats[$item['category_id']])) { ?>
                        <div class="cat mb-2"><a href="<?php echo href_to($ctype['name'], $subcats[$item['category_id']]['slug']) ?>"><?php echo $subcats[$item['category_id']]['title']; ?></a></div>
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
                            <div class="info_bar d-flex text-muted mt-3">
                                <?php if ($ctype['is_rating'] && !empty($tpl['show_rating'])) { ?>
                                    <div class="mr-3 bar_item bi_rating">
                                    <?php echo $item['rating_widget']; ?>
                                    </div>
                                    <?php } ?>
                                    <?php if ($fields['date_pub']['is_in_list']) { ?>
                                    <div class="mr-3 bar_item bi_date_pub" title="<?php echo $fields['date_pub']['title']; ?>">
                                    <?php echo html_svg_icon('solid', 'calendar-alt'); ?>
                                    <?php echo $fields['date_pub']['handler']->parse($item['date_pub']); ?>
                                    </div>
                                    <?php } ?>
                                <?php if (!$item['is_pub']) { ?>
                                    <div class="mr-3 bar_item bi_not_pub">
                                    <?php echo LANG_CONTENT_NOT_IS_PUB; ?>
                                    </div>
                                    <?php } ?>
                                    <?php if ($fields['user']['is_in_list']) { ?>
                                    <div class="mr-3 bar_item bi_user" title="<?php echo $fields['user']['title']; ?>">
                                    <?php echo html_svg_icon('solid', 'user'); ?>
                    <?php echo $fields['user']['handler']->parse($item['user']); ?>
                                    </div>
                                    <?php if (!empty($item['folder_title'])) { ?>
                                        <div class="mr-3 bar_item bi_folder">
                                            <a href="<?php echo href_to('users', $item['user']['id'], array('content', $ctype['name'], $item['folder_id'])); ?>"><?php echo html_svg_icon('solid', 'folder'); ?> <?php echo $item['folder_title']; ?></a>
                                        </div>
                    <?php } ?>
                <?php } ?>
                <?php if ($ctype['is_comments']) { ?>
                                    <div class="mr-3 bar_item bi_comments">

                                        <a href="<?php echo href_to($ctype['name'], $item['slug'] . '.html'); ?>#comments" title="<?php echo LANG_COMMENTS; ?>"><?php echo html_svg_icon('solid', 'comments'); ?> <?php echo intval($item['comments']); ?></a>
                                    </div>
                                    <?php } ?>
                                <?php if (!$item['is_approved']) { ?>
                                    <div class="mr-3 bar_item bi_not_approved">
                                <?php echo LANG_CONTENT_NOT_APPROVED; ?>
                                    </div>
                            <?php } ?>
                            </div>
                <?php } ?>
            <?php } ?>
                </div>
            </div>
    <?php } ?>
    </div>

<?php } else {
    echo LANG_LIST_EMPTY;
} ?>