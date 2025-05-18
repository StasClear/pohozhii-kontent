<?php
$this->addCSS('templates/default/css/jquery-ui.css');
$this->addJS('templates/default/js/jquery-ui.js');

$this->addBreadcrumb($do == 'edit' ? $relevant['title'] : LANG_RELEVANTER_CP_RELEVANT_ADD);

$this->addToolButton(array(
    'class' => 'save',
    'title' => LANG_SAVE,
    'href' => "javascript:icms.forms.submit()"
));

$this->addToolButton(array(
    'class' => 'cancel',
    'title' => LANG_CANCEL,
    'href' => $this->href_to('relevants')
));

?>

<h2><?php if ($do == 'edit') { ?><?php echo LANG_RELEVANTER_CP_RELEVANT; ?>: <span><?php echo $relevant['title']; ?></span><?php } else { ?><?php echo LANG_RELEVANTER_CP_RELEVANT_ADD; ?><?php } ?></h2>

<?php
$this->renderForm($form, $relevant, array(
    'action' => '',
    'method' => 'post',
    'append_html' => $this->getRenderedChild('backend/relevant_filters', array(
        'do' => $do,
        'relevant' => $relevant,
        'fields' => $fields,
        'errors' => $errors
    ))
), $errors);

?>

<script>
    if ($("#content_this_category").is(":checked")) {
        $("#f_content_category_id").hide();
    }
    $("#content_this_category").on("click", function () {
        $("#f_content_category_id").toggle();
    });

    if (!$("#template_show_description").is(":checked")) {
        $("#f_template_teaser_field, #f_template_size_teaser").hide();
    }
    $("#template_show_description").on("click", function () {
        $("#f_template_teaser_field, #f_template_size_teaser").toggle();
    });

    if (!$("#template_show_image").is(":checked")) {
        $("#f_template_image_field, #f_template_image_size, #f_template_noimage").hide();
    }
    $("#template_show_image").on("click", function () {
        $("#f_template_image_field, #f_template_image_size, #f_template_noimage").toggle();
    });
</script>