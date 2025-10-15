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
                                <a href="#" class="btn btn-info pull-left display-block" data-toggle="modal" data-target="#group_modal">
                                    <i class="fa fa-plus-circle"></i> <?= _l('pm_add_group'); ?>
                                </a>
                            <?php } ?>
                            <div class="clearfix"></div>
                        </div>
                        <hr class="hr-panel-heading" />
                        
                        <div class="clearfix"></div>
                        
                        <?= form_hidden('custom_view'); ?>
                        
                        <!-- Debug Info -->
                        <div class="alert alert-info" style="margin-bottom: 10px;">
                            <strong>Debug:</strong> Found <?= count($groups); ?> groups. 
                            <small>Using basic table (DataTables disabled for testing).</small>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="50"><?= _l('id'); ?></th>
                                        <th><?= _l('pm_group_code'); ?></th>
                                        <th><?= _l('pm_group_name'); ?></th>
                                        <th><?= _l('description'); ?></th>
                                        <th><?= _l('pm_sort_order'); ?></th>
                                        <th><?= _l('status'); ?></th>
                                        <th><?= _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($groups)) { ?>
                                        <?php foreach ($groups as $group) { ?>
                                            <tr>
                                                <td><?= htmlspecialchars($group['id'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($group['group_code'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($group['group_name'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($group['description'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($group['sort_order'] ?? '0'); ?></td>
                                                <td>
                                                    <?php 
                                                    if (function_exists('render_pm_status_badge')) {
                                                        echo render_pm_status_badge($group['status'] ?? 0);
                                                    } else {
                                                        echo $group['status'] == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (has_permission('product_master', '', 'edit')) { ?>
                                                        <a href="#" class="btn btn-default btn-icon" 
                                                           onclick="edit_group(<?= $group['id']; ?>); return false;"
                                                           data-toggle="tooltip" title="<?= _l('edit'); ?>">
                                                            <i class="fa fa-pencil-square-o"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if (has_permission('product_master', '', 'delete')) { ?>
                                                        <a href="<?= admin_url('product_master/delete/group/' . $group['id']); ?>" 
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
                                            <td colspan="7" class="text-center">
                                                <p><strong>No groups found.</strong></p>
                                                <p>Click the "Add Group" button above to create your first group.</p>
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

<!-- Group Modal -->
<div class="modal fade" id="group_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="add-title"><?= _l('pm_add_group'); ?></span>
                    <span class="edit-title" style="display:none;"><?= _l('pm_edit_group'); ?></span>
                </h4>
            </div>
            <?= form_open(admin_url('product_master/groups'), ['id' => 'group_form']); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?= form_hidden('id'); ?>
                        
                        <?= render_input('group_code', 'pm_group_code', '', 'text', ['required' => true, 'maxlength' => 100]); ?>
                        
                        <?= render_input('group_name', 'pm_group_name', '', 'text', ['required' => true, 'maxlength' => 255]); ?>
                        
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
    console.log('Groups page loaded successfully without DataTables');
    
    // Handle form submission via AJAX
    $('#group_form').on('submit', function(e) {
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
                $('#group_modal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                alert('An error occurred while saving. Please try again.');
                submitBtn.prop('disabled', false).text('<?= _l('submit'); ?>');
            }
        });
    });
    
    function edit_group(id) {
        // Get group data via AJAX and populate modal
        $.get('<?= admin_url('product_master/get_group/'); ?>' + id)
        .done(function(data) {
            try {
                var group = JSON.parse(data);
                $('#group_form input[name="id"]').val(group.id);
                $('#group_form input[name="group_code"]').val(group.group_code);
                $('#group_form input[name="group_name"]').val(group.group_name);
                $('#group_form textarea[name="description"]').val(group.description);
                $('#group_form input[name="sort_order"]').val(group.sort_order);
                $('#group_form select[name="status"]').val(group.status).selectpicker('refresh');
                
                $('.add-title').hide();
                $('.edit-title').show();
                $('#group_modal').modal('show');
            } catch(e) {
                alert('Error loading group data: ' + e.message);
            }
        })
        .fail(function() {
            alert('Failed to load group data. Please try again.');
        });
    }

    // Make function globally available
    window.edit_group = edit_group;
    
    $('#group_modal').on('hidden.bs.modal', function() {
        $('#group_form')[0].reset();
        $('#group_form input[name="id"]').val('');
        $('.add-title').show();
        $('.edit-title').hide();
        $('#group_form .selectpicker').selectpicker('refresh');
        // Re-enable submit button
        $('#group_form button[type="submit"]').prop('disabled', false).text('<?= _l('submit'); ?>');
    });
    
});
</script>