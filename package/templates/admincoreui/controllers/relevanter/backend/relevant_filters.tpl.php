<div class="without-tabs card mb-0 rounded-0 form-tabs">
    <div class="card-body">

        <fieldset>

            <legend><?php echo LANG_SORTING; ?></legend>

            <div id="sortings"></div>

            <div id="add_sorting" class="form-inline field ft_list" style="display:none">
                <label class="my-1 mr-2"><?php echo LANG_RELEVANTER_SORTING; ?></label>
                <select class="form-control custom-select mr-sm-2"></select>
                <a class="btn btn-success add_value mr-sm-2 d-flex" href="javascript:submitSorting();" title="<?php echo LANG_ADD; ?>"><?php echo html_svg_icon('solid', 'check-square', 16); ?></a>
                <a class="btn btn-danger cancel mr-sm-2 d-flex" href="javascript:cancelSorting();" title="<?php echo LANG_CANCEL; ?>"><?php echo html_svg_icon('solid', 'minus-square', 16); ?></a>
            </div>

            <a id="add_sorting_link" class="btn btn-outline-primary add_link" href="javascript:addSorting();"><?php echo html_svg_icon('solid', 'plus-circle', 16); ?> <?php echo LANG_SORTING_ADD; ?></a>

        </fieldset>

        <fieldset>

            <legend><?php echo LANG_FILTERS; ?></legend>

            <div id="filters"></div>

            <div id="add_filter" class="form-inline field ft_list" style="display:none">
                <label class="my-1 mr-2"><?php echo LANG_RELEVANTER_FILTER; ?></label>
                <select class="form-control custom-select mr-sm-2"></select>
                <a class="btn btn-success add_value mr-sm-2 d-flex" href="javascript:submitFilter();" title="<?php echo LANG_ADD; ?>"><?php echo html_svg_icon('solid', 'check-square', 16); ?></a>
                <a class="btn btn-danger cancel mr-sm-2 d-flex" href="javascript:cancelFilter();" title="<?php echo LANG_CANCEL; ?>"><?php echo html_svg_icon('solid', 'minus-square', 16); ?></a>
            </div>

            <a id="add_filter_link" class="btn btn-outline-primary add_link" href="javascript:addFilter();"><?php echo html_svg_icon('solid', 'plus-circle', 16); ?> <?php echo LANG_FILTER_ADD; ?></a>

        </fieldset>

        <div id="sorting_template" class="sorting form-inline mb-3" style="display:none">
            <span class="title"><input type="hidden" name="" value="" /></span>
            <span class="to"><select name="" class="custom-select mx-2"></select></span>
            <span class="delete"><a class="btn btn-outline-danger unset_value" href="javascript:" onclick="deleteSorting(this)" title="<?php echo LANG_DELETE; ?>"><?php echo html_svg_icon('solid', 'minus-square', 16); ?></a></span>
        </div>

        <div id="filter_template" class="filter form-inline mb-3" style="display:none">
            <span class="title"><input type="hidden" name="" value="" /></span>
            <span class="condition"><select name="" class="custom-select mx-2"></select></span>
            <span class="value"><input class="form-control mx-2" type="text" name="" /></span>
            <span class="delete"><a class="btn btn-outline-danger unset_value" href="javascript:" onclick="deleteFilter(this)" title="<?php echo LANG_DELETE; ?>"><?php echo html_svg_icon('solid', 'minus-square', 16); ?></a></span>
        </div>

        <select class="form-control" id="fields_list" style="display:none">
            <?php foreach ($fields as $field) { ?>
                <option value="<?php echo $field['name']; ?>" data-type="<?php echo $field['handler']->filter_type; ?>"><?php echo htmlspecialchars($field['title']); ?></option>
            <?php } ?>
            <option value="tags" data-type="tags"><?php echo LANG_TAGS; ?></option>
            <option value="rating" data-type="int"><?php echo LANG_RATING; ?></option>
            <option value="comments" data-type="int"><?php echo LANG_COMMENTS; ?></option>
            <option value="hits_count" data-type="int"><?php echo LANG_HITS; ?></option>
            <option value="id" data-type="int">ID</option>
        </select>

        <select class="form-control" id="sorting_tos" style="display:none">
            <option value="asc"><?php echo LANG_SORTING_ASC; ?></option>
            <option value="desc"><?php echo LANG_SORTING_DESC; ?></option>
        </select>

        <select class="form-control" id="conditions_tags" style="display:none">
            <option value="eq">=</option>
            <option value="lk"><?php echo LANG_FILTER_LIKE; ?></option>
            <option value="lb"><?php echo LANG_FILTER_LIKE_BEGIN; ?></option>
            <option value="lf"><?php echo LANG_FILTER_LIKE_END; ?></option>
        </select>

        <select class="form-control" id="conditions_int" style="display:none">
            <option value="eq">=</option>
            <option value="gt">&gt;</option>
            <option value="lt">&lt;</option>
            <option value="ge">&ge;</option>
            <option value="le">&le;</option>
            <option value="nn"><?php echo LANG_FILTER_NOT_NULL; ?></option>
            <option value="ni"><?php echo LANG_FILTER_IS_NULL; ?></option>
        </select>

        <select class="form-control" id="conditions_str" style="display:none">
            <option value="eq">=</option>
            <option value="lk"><?php echo LANG_FILTER_LIKE; ?></option>
            <option value="lb"><?php echo LANG_FILTER_LIKE_BEGIN; ?></option>
            <option value="lf"><?php echo LANG_FILTER_LIKE_END; ?></option>
            <option value="nn"><?php echo LANG_FILTER_NOT_NULL; ?></option>
            <option value="ni"><?php echo LANG_FILTER_IS_NULL; ?></option>
        </select>

        <select class="form-control" id="conditions_date" style="display:none">
            <option value="eq">=</option>
            <option value="gt">&gt;</option>
            <option value="lt">&lt;</option>
            <option value="ge">&ge;</option>
            <option value="le">&le;</option>
            <option value="dy"><?php echo LANG_FILTER_DATE_YOUNGER; ?></option>
            <option value="do"><?php echo LANG_FILTER_DATE_OLDER; ?></option>
            <option value="nn"><?php echo LANG_FILTER_NOT_NULL; ?></option>
            <option value="ni"><?php echo LANG_FILTER_IS_NULL; ?></option>
        </select>

        <script>

            function addSorting() {
                $('#add_sorting select').html($('#fields_list').html()).show();
                $('#add_sorting').show();
                $('#add_sorting_link').hide();
            }

            function submitSorting(data) {

                if (typeof (data) === 'undefined') {
                    data = {by: false, to: false};
                }

                if (data.by) {
                    var field = data.by;
                } else {
                    var field = $('#add_sorting select').val();
                }

                var sorting_id = $('#sortings .sorting').length;
                var sorting = $('#sorting_template').clone();
                var field_title = $('#fields_list option[value=' + field + ']').html();

                $(sorting).attr('id', 'sorting' + sorting_id);

                $('.title', sorting).append(field_title);
                $('.to select', sorting).html($('#sorting_tos').html());
                $('.title input', sorting).attr('name', 'sorting[' + sorting_id + '][by]').val(field);
                $('.to select', sorting).attr('name', 'sorting[' + sorting_id + '][to]');

                if (data.to) {
                    $('.to select', sorting).val(data.to);
                }

                $('#sortings').append(sorting);
                $('#sortings #sorting' + sorting_id).slideToggle(300);

                cancelSorting();
            }

            function cancelSorting() {
                $('#add_sorting').hide();
                $('#add_sorting_link').show();
            }

            function deleteSorting(link_instance) {
                $(link_instance).parent('span').parent('div').slideToggle(300, function () {
                    $(this).remove();
                });
            }

            function addFilter() {
                $('#add_filter select').html($('#fields_list').html()).show();
                $('#add_filter').show();
                $('#add_filter_link').hide();
            }

            function submitFilter(data) {

                if (typeof (data) === 'undefined') {
                    data = {
                        field: false, condition: false, value: false
                    };
                }

                if (data.field) {
                    var field = data.field;
                } else {
                    var field = $('#add_filter select').val();
                }

                var filter_id = $('#filters .filter').length;
                var filter = $('#filter_template').clone();

                var field_title = $('#fields_list option[value=' + field + ']').html();
                var field_type = $('#fields_list option[value=' + field + ']').data('type');

                $(filter).attr('id', 'filter' + filter_id);

                $('.title', filter).append(field_title);
                $('.condition select', filter).html($('#conditions_' + field_type).html());

                $('.title input', filter).attr('name', 'filters[' + filter_id + '][field]').val(field);
                $('.condition select', filter).attr('name', 'filters[' + filter_id + '][condition]');
                if (field_type === 'tags') {
                    $('.value input', filter).attr('name', 'filters[' + filter_id + '][value]').addClass('ui-autocomplete-input').autocomplete({
                        minLength: 2,
                        delay: 500,
                        source: function (request, response) {
                            var term = request.term;
                            if (term in cache) {
                                response(cache[ term ]);
                                return;
                            }
                            $.getJSON('<?php echo href_to('tags', 'autocomplete'); ?>', request, function (data, status, xhr) {
                                cache[ term ] = data;
                                response(data);
                            });
                        }
                    });

                    var cache;
                    cache = {};
                    $('[name="filters[' + filter_id + '][value]"]');
                    $('[name="filters[' + filter_id + '][value]"]');
                } else {
                    $('.value input', filter).attr('name', 'filters[' + filter_id + '][value]');
                }

                if (data.condition) {
                    $('.condition select', filter).val(data.condition);
                }

                if (data.value) {
                    $('.value input', filter).val(data.value);
                }

                $('#filters').append(filter);

                $('#filters #filter' + filter_id).slideToggle(300);

                cancelFilter();

            }

            function cancelFilter() {
                $('#add_filter').hide();
                $('#add_filter_link').show();
            }

            function deleteFilter(link_instance) {
                $(link_instance).parent('span').parent('div').slideToggle(300, function () {
                    $(this).remove();
                });
            }

            function getFields(ctype_name) {

                $.post('<?php echo href_to('relevanter', 'fields_ajax'); ?>', {value: ctype_name}, function (result) {
                    var child_list = $('#fields_list');

                    child_list.html('<option value="tags" data-type="tags"><?php echo LANG_TAGS; ?></option><option value="rating" data-type="int"><?php echo LANG_RATING; ?></option><option value="comments" data-type="int"><?php echo LANG_COMMENTS; ?></option><option value="hits_count" data-type="int"><?php echo LANG_HITS; ?></option><option value="id" data-type="int">ID</option>');

                    for (var k in result) {
                        child_list.append('<option value="' + k + '" data-type="' + result[k].handler.filter_type + '">' + result[k].title + '</option>');
                    }
                }, 'json');
            }

            $("#content_ctype_name").on('change', function () {
                getFields($(this).val());
            });

            $(document).ready(function () {

                var ctype_name = $("#content_ctype_name").val();

                if (ctype_name) {
                    getFields(ctype_name);
                }

<?php if ($do == 'edit') { ?>
    <?php if (!empty($relevant['filters'])) { ?>
        <?php foreach ($relevant['filters'] as $filter) { ?>
            <?php if (!empty($filter['condition'])) { ?>
                                submitFilter({
                                    field: '<?php echo $filter['field']; ?>',
                                    condition: '<?php echo!empty($filter['condition']) ? $filter['condition'] : false; ?>',
                                    value: '<?php echo $filter['value']; ?>'
                                });
            <?php } ?>
        <?php } ?>
    <?php } ?>

    <?php if (!empty($relevant['sorting'])) { ?>
        <?php foreach ($relevant['sorting'] as $sort) { ?>
                            submitSorting({
                                by: '<?php echo $sort['by']; ?>',
                                to: '<?php echo $sort['to']; ?>'
                            });
        <?php } ?>
    <?php } ?>
<?php } ?>
            });
        </script>
    </div>
</div>