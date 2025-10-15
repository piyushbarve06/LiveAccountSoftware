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
                                <a href="#" class="btn btn-info pull-left display-block" data-toggle="modal" data-target="#subgroup_modal">
                                    <i class="fa fa-plus-circle"></i> <?= _l('pm_add_subgroup'); ?>
                                </a>
                            <?php } ?>
                            <div class="clearfix"></div>
                        </div>
                        <hr class="hr-panel-heading" />
                        
                        <div class="clearfix"></div>
                        
                        <?= form_hidden('custom_view'); ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="subgroups-table">
                                <thead>
                                    <tr>
                                        <th width="50"><?= _l('id'); ?></th>
                                        <th><?= _l('pm_group_name'); ?></th>
                                        <th><?= _l('pm_subgroup_code'); ?></th>
                                        <th><?= _l('pm_subgroup_name'); ?></th>
                                        <th><?= _l('description'); ?></th>
                                        <th><?= _l('pm_sort_order'); ?></th>
                                        <th><?= _l('status'); ?></th>
                                        <th><?= _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($subgroups)) { ?>
                                        <?php foreach ($subgroups as $subgroup) { ?>
                                            <tr>
                                                <td><?= htmlspecialchars($subgroup['id'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($subgroup['group_name'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($subgroup['subgroup_code'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($subgroup['subgroup_name'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($subgroup['description'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($subgroup['sort_order'] ?? '0'); ?></td>
                                                <td>
                                                    <?php 
                                                    if (function_exists('render_pm_status_badge')) {
                                                        echo render_pm_status_badge($subgroup['status'] ?? 0);
                                                    } else {
                                                        echo $subgroup['status'] == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (has_permission('product_master', '', 'edit')) { ?>
                                                        <a href="#" class="btn btn-default btn-icon" 
                                                           onclick="edit_subgroup(<?= $subgroup['id']; ?>); return false;"
                                                           data-toggle="tooltip" title="<?= _l('edit'); ?>">
                                                            <i class="fa fa-pencil-square-o"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if (has_permission('product_master', '', 'delete')) { ?>
                                                        <a href="<?= admin_url('product_master/delete/subgroup/' . $subgroup['id']); ?>" 
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
                                            <td colspan="8" class="text-center">
                                                <p><strong>No subgroups found.</strong></p>
                                                <p>Click the "Add Subgroup" button above to create your first subgroup.</p>
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

<!-- Subgroup Modal -->
<div class="modal fade" id="subgroup_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="add-title"><?= _l('pm_add_subgroup'); ?></span>
                    <span class="edit-title" style="display:none;"><?= _l('pm_edit_subgroup'); ?></span>
                </h4>
            </div>
            <?= form_open(admin_url('product_master/subgroups'), ['id' => 'subgroup_form']); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?= form_hidden('id'); ?>
                        
                        <div class="form-group">
                            <label for="group_id" class="control-label"><?= _l('pm_group_name'); ?> *</label>
                            <select name="group_id" id="group_id" class="form-control selectpicker" required data-live-search="true">
                                <option value=""><?= _l('pm_select_group'); ?></option>
                                <?php foreach ($groups as $group) { ?>
                                    <option value="<?= $group['id']; ?>"><?= $group['group_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        
                        <?= render_input('subgroup_code', 'pm_subgroup_code', '', 'text', ['required' => true, 'maxlength' => 100]); ?>
                        
                        <?= render_input('subgroup_name', 'pm_subgroup_name', '', 'text', ['required' => true, 'maxlength' => 255]); ?>
                        
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
    console.log('Subgroups page loaded successfully without DataTables');
    
    // Handle form submission via AJAX
    $('#subgroup_form').on('submit', function(e) {
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
                $('#subgroup_modal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                alert('An error occurred while saving. Please try again.');
                submitBtn.prop('disabled', false).text('<?= _l('submit'); ?>');
            }
        });
    });
    
    function edit_subgroup(id) {
        // Get subgroup data via AJAX and populate modal
        $.get('<?= admin_url('product_master/get_subgroup/'); ?>' + id)
        .done(function(data) {
            try {
                var subgroup = JSON.parse(data);
                $('#subgroup_form input[name="id"]').val(subgroup.id);
                $('#subgroup_form select[name="group_id"]').val(subgroup.group_id).selectpicker('refresh');
                $('#subgroup_form input[name="subgroup_code"]').val(subgroup.subgroup_code);
                $('#subgroup_form input[name="subgroup_name"]').val(subgroup.subgroup_name);
                $('#subgroup_form textarea[name="description"]').val(subgroup.description);
                $('#subgroup_form input[name="sort_order"]').val(subgroup.sort_order);
                $('#subgroup_form select[name="status"]').val(subgroup.status).selectpicker('refresh');
                
                $('.add-title').hide();
                $('.edit-title').show();
                $('#subgroup_modal').modal('show');
            } catch(e) {
                console.error('Error parsing subgroup data:', e);
                alert('Error loading subgroup data: ' + e.message);
            }
        })
        .fail(function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            alert('Failed to load subgroup data. Please try again.');
        });
    }

    // Make function globally available
    window.edit_subgroup = edit_subgroup;
    
    $('#subgroup_modal').on('hidden.bs.modal', function() {
        $('#subgroup_form')[0].reset();
        $('#subgroup_form input[name="id"]').val('');
        $('.add-title').show();
        $('.edit-title').hide();
        $('#subgroup_form .selectpicker').selectpicker('refresh');
        // Re-enable submit button
        $('#subgroup_form button[type="submit"]').prop('disabled', false).text('<?= _l('submit'); ?>');
    });
    
});
</script>