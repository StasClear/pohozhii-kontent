<?php if ($user->is_admin) { ?>
    <div class="text-right">
        <a href="<?php echo href_to('admin', 'controllers/edit/relevanter/relevant_edit', $relevant['id']); ?>" target="_blank"><?php echo LANG_RELEVANTER_EDIT; ?></a>
    </div>
<?php } ?>

<?php if ($user->is_admin && !empty($relevant['template']['debug'])) {
    echo "<div>" . LANG_RELEVANTER_SEARCH_FOR . $search . "</div>";
} ?>

<?php
echo $items_list_html;
