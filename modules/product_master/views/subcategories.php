<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <?php if (has_permission('product_master', '', 'create')) { ?>
                                <a href="#" class="btn btn-info pull-left display-block" data-toggle="modal" data-target="#subcategory_modal">
                                    <i class="fa fa-plus-circle"></i> <?= _l('pm_add_subcategory'); ?>
                                </a>
                            <?php } ?>
                            <div class="clearfix"></div>
                        </div>
                        <hr class="hr-panel-heading" />
                        
                        <div class="clearfix"></div>
                        
                        <?= form_hidden('custom_view'); ?>
                        
                        <!-- Debug Info -->
                        <div class="alert alert-info" style="margin-bottom: 10px;">
                            <strong>Debug:</strong> Found <?= count($subcategories ?? []); ?> subcategories. 
                            <small>Using basic table (DataTables disabled for testing).</small>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="50"><?= _l('id'); ?></th>
                                        <th><?= _l('pm_group_name'); ?></th>
                                        <th><?= _l('pm_subgroup_name'); ?></th>
                                        <th><?= _l('pm_category_name'); ?></th>
                                        <th><?= _l('pm_subcategory_code'); ?></th>
                                        <th><?= _l('pm_subcategory_name'); ?></th>
                                        <th><?= _l('description'); ?></th>
                                        <th><?= _l('pm_sort_order'); ?></th>
                                        <th><?= _l('status'); ?></th>
                                        <th><?= _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($subcategories)) { ?>
                                        <?php foreach ($subcategories as $subcategory) { ?>
                                            <tr>
                                                <td><?= htmlspecialchars($subcategory['id'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($subcategory['group_name'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($subcategory['subgroup_name'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($subcategory['category_name'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($subcategory['subcategory_code'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($subcategory['subcategory_name'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($subcategory['description'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($subcategory['sort_order'] ?? '0'); ?></td>
                                                <td>
                                                    <?php 
                                                    if (function_exists('render_pm_status_badge')) {
                                                        echo render_pm_status_badge($subcategory['status'] ?? 0);
                                                    } else {
                                                        echo ($subcategory['status'] ?? 0) == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';
                                                    }
                                                    ?>
                                                </td>
                                            <td>
                                                <?php if (has_permission('product_master', '', 'edit')) { ?>
                                                    <a href="#" class="btn btn-default btn-icon" 
                                                       onclick="edit_subcategory(<?= $subcategory['id']; ?>); return false;"
                                                       data-toggle="tooltip" title="<?= _l('edit'); ?>">
                                                        <i class="fa fa-pencil-square-o"></i>
                                                    </a>
                                                <?php } ?>
                                                <?php if (has_permission('product_master', '', 'delete')) { ?>
                                                    <a href="<?= admin_url('product_master/delete/subcategory/' . $subcategory['id']); ?>" 
                                                       class="btn btn-danger btn-icon _delete"
                                                       data-toggle="tooltip" title="<?= _l('delete'); ?>">
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php } else { ?>
                                        <tr>
                                            <td colspan="10" class="text-center">
                                                <p><strong>No subcategories found.</strong></p>
                                                <p>Click the "Add Subcategory" button above to create your first subcategory.</p>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subcategory Modal -->
<div class="modal fade" id="subcategory_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="add-title"><?= _l('pm_add_subcategory'); ?></span>
                    <span class="edit-title" style="display:none;"><?= _l('pm_edit_subcategory'); ?></span>
                </h4>
            </div>
            <?= form_open(admin_url('product_master/subcategories'), ['id' => 'subcategory_form']); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?= form_hidden('id'); ?>
                        
                        <div class="form-group">
                            <label for="category_id" class="control-label"><?= _l('pm_category_name'); ?> *</label>
                            <select name="category_id" id="category_id" class="form-control selectpicker" required data-live-search="true">
                                <option value=""><?= _l('pm_select_category'); ?></option>
                                <?php foreach ($categories as $category) { ?>
                                    <option value="<?= $category['id']; ?>"><?= $category['group_name'] . ' > ' . $category['subgroup_name'] . ' > ' . $category['category_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        
                        <?= render_input('subcategory_code', 'pm_subcategory_code', '', 'text', ['required' => true, 'maxlength' => 100]); ?>
                        
                        <?= render_input('subcategory_name', 'pm_subcategory_name', '', 'text', ['required' => true, 'maxlength' => 255]); ?>
                        
                        <?= render_textarea('description', 'description', '', ['rows' => 3]); ?>
                        
                        <?= render_input('sort_order', 'pm_sort_order', '0', 'number', ['min' => 0]); ?>
                        
                        <div class="form-group">
                            <label for="status"><?= _l('status'); ?></label>
                            <select name="status" id="status" class="form-control selectpicker">
                                <option value="1"><?= _l('active'); ?></option>
                                <option value="0"><?= _l('inactive'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?= _l('submit'); ?></button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(function() {
    
    // DataTables removed for now - basic table functionality only
    console.log('Subcategories page loaded successfully without DataTables');
    
    // Handle form submission via AJAX
    $('#subcategory_form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        var formData = $(this).serialize();
        var submitBtn = $(this).find('button[type="submit"]');
        
        // Disable submit button to prevent double submission
        submitBtn.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                // Close modal and reload page to show updated data
                $('#subcategory_modal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                alert('An error occurred while saving. Please try again.');
                submitBtn.prop('disabled', false).text('<?= _l('submit'); ?>');
            }
        });
    });
    
    function edit_subcategory(id) {
        // Get subcategory data via AJAX and populate modal
        $.get('<?= admin_url('product_master/get_subcategory/'); ?>' + id)
        .done(function(data) {
            try {
                var subcategory = JSON.parse(data);
                $('#subcategory_form input[name="id"]').val(subcategory.id);
                $('#subcategory_form select[name="category_id"]').val(subcategory.category_id).selectpicker('refresh');
                $('#subcategory_form input[name="subcategory_code"]').val(subcategory.subcategory_code);
                $('#subcategory_form input[name="subcategory_name"]').val(subcategory.subcategory_name);
                $('#subcategory_form textarea[name="description"]').val(subcategory.description);
                $('#subcategory_form input[name="sort_order"]').val(subcategory.sort_order);
                $('#subcategory_form select[name="status"]').val(subcategory.status).selectpicker('refresh');
                
                $('.add-title').hide();
                $('.edit-title').show();
                $('#subcategory_modal').modal('show');
            } catch(e) {
                console.error('Error parsing subcategory data:', e);
                alert('Error loading subcategory data: ' + e.message);
            }
        })
        .fail(function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            alert('Failed to load subcategory data. Please try again.');
        });
    }

    // Make function globally available
    window.edit_subcategory = edit_subcategory;
    
    $('#subcategory_modal').on('hidden.bs.modal', function() {
        $('#subcategory_form')[0].reset();
        $('#subcategory_form input[name="id"]').val('');
        $('.add-title').show();
        $('.edit-title').hide();
        $('#subcategory_form .selectpicker').selectpicker('refresh');
        // Re-enable submit button
        $('#subcategory_form button[type="submit"]').prop('disabled', false).text('<?= _l('submit'); ?>');
    });
    
});
</script>