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
                                <a href="#" class="btn btn-info pull-left display-block" data-toggle="modal" data-target="#multi_unit_modal">
                                    <i class="fa fa-plus-circle"></i> <?= _l('pm_add_multi_unit'); ?>
                                </a>
                            <?php } ?>
                            <div class="clearfix"></div>
                        </div>
                        <hr class="hr-panel-heading" />
                        
                        <div class="clearfix"></div>
                        
                        <?= form_hidden('custom_view'); ?>
                        
                        <!-- Debug Info -->
                        <div class="alert alert-info" style="margin-bottom: 10px;">
                            <strong>Debug:</strong> Found <?= count($multi_units ?? []); ?> multi units. 
                            <small>Using basic table (DataTables disabled for testing).</small>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="50"><?= _l('id'); ?></th>
                                        <th><?= _l('pm_from_unit'); ?></th>
                                        <th><?= _l('pm_to_unit'); ?></th>
                                        <th><?= _l('pm_conversion_rate'); ?></th>
                                        <th><?= _l('pm_formula'); ?></th>
                                        <th><?= _l('pm_default'); ?></th>
                                        <th><?= _l('status'); ?></th>
                                        <th><?= _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($multi_units)) { ?>
                                        <?php foreach ($multi_units as $multi_unit) { ?>
                                            <tr>
                                                <td><?= htmlspecialchars($multi_unit['id'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars(($multi_unit['from_unit_name'] ?? '') . ' (' . ($multi_unit['from_unit_symbol'] ?? '') . ')'); ?></td>
                                                <td><?= htmlspecialchars(($multi_unit['to_unit_name'] ?? '') . ' (' . ($multi_unit['to_unit_symbol'] ?? '') . ')'); ?></td>
                                                <td><?= htmlspecialchars($multi_unit['conversion_rate'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($multi_unit['formula'] ?? ''); ?></td>
                                                <td><?= ($multi_unit['is_default'] ?? 0) ? '<span class="label label-success">' . _l('yes') . '</span>' : '<span class="label label-default">' . _l('no') . '</span>'; ?></td>
                                                <td>
                                                    <?php 
                                                    if (function_exists('render_pm_status_badge')) {
                                                        echo render_pm_status_badge($multi_unit['status'] ?? 0);
                                                    } else {
                                                        echo ($multi_unit['status'] ?? 0) == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';
                                                    }
                                                    ?>
                                                </td>
                                            <td>
                                                <?php if (has_permission('product_master', '', 'edit')) { ?>
                                                    <a href="#" class="btn btn-default btn-icon" 
                                                       onclick="edit_multi_unit(<?= $multi_unit['id']; ?>); return false;"
                                                       data-toggle="tooltip" title="<?= _l('edit'); ?>">
                                                        <i class="fa fa-pencil-square-o"></i>
                                                    </a>
                                                <?php } ?>
                                                <?php if (has_permission('product_master', '', 'delete')) { ?>
                                                    <a href="<?= admin_url('product_master/delete/multi_unit/' . $multi_unit['id']); ?>" 
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
                                                <p><strong>No multi units found.</strong></p>
                                                <p>Click the "Add Multi Unit" button above to create your first multi unit conversion.</p>
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

<!-- Multi Unit Modal -->
<div class="modal fade" id="multi_unit_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="add-title"><?= _l('pm_add_multi_unit'); ?></span>
                    <span class="edit-title" style="display:none;"><?= _l('pm_edit_multi_unit'); ?></span>
                </h4>
            </div>
            <?= form_open(admin_url('product_master/multi_units'), ['id' => 'multi_unit_form']); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?= form_hidden('id'); ?>
                        
                        <div class="form-group">
                            <label for="from_unit_id" class="control-label"><?= _l('pm_from_unit'); ?> *</label>
                            <select name="from_unit_id" id="from_unit_id" class="form-control selectpicker" required data-live-search="true">
                                <option value=""><?= _l('pm_select_unit'); ?></option>
                                <?php foreach ($units as $unit) { ?>
                                    <option value="<?= $unit['id']; ?>"><?= $unit['unit_name'] . ' (' . $unit['unit_symbol'] . ')'; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="to_unit_id" class="control-label"><?= _l('pm_to_unit'); ?> *</label>
                            <select name="to_unit_id" id="to_unit_id" class="form-control selectpicker" required data-live-search="true">
                                <option value=""><?= _l('pm_select_unit'); ?></option>
                                <?php foreach ($units as $unit) { ?>
                                    <option value="<?= $unit['id']; ?>"><?= $unit['unit_name'] . ' (' . $unit['unit_symbol'] . ')'; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        
                        <?= render_input('conversion_rate', 'pm_conversion_rate', '', 'number', ['required' => true, 'step' => '0.000001', 'min' => '0.000001']); ?>
                        
                        <?= render_input('formula', 'pm_formula', '', 'text', ['maxlength' => 500, 'placeholder' => 'e.g., 1 Kg = 1000 Grams']); ?>
                        
                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="is_default" id="is_default" value="1">
                                <label for="is_default"><?= _l('pm_default_conversion'); ?></label>
                            </div>
                        </div>
                        
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
    console.log('Multi Units page loaded successfully without DataTables');
    
    // Handle form submission via AJAX
    $('#multi_unit_form').on('submit', function(e) {
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
                $('#multi_unit_modal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                alert('An error occurred while saving. Please try again.');
                submitBtn.prop('disabled', false).text('<?= _l('submit'); ?>');
            }
        });
    });
    
    function edit_multi_unit(id) {
        // Get multi unit data via AJAX and populate modal
        $.get('<?= admin_url('product_master/get_multi_unit/'); ?>' + id)
        .done(function(data) {
            try {
                var multi_unit = JSON.parse(data);
                $('#multi_unit_form input[name="id"]').val(multi_unit.id);
                $('#multi_unit_form select[name="from_unit_id"]').val(multi_unit.from_unit_id).selectpicker('refresh');
                $('#multi_unit_form select[name="to_unit_id"]').val(multi_unit.to_unit_id).selectpicker('refresh');
                $('#multi_unit_form input[name="conversion_rate"]').val(multi_unit.conversion_rate);
                $('#multi_unit_form input[name="formula"]').val(multi_unit.formula);
                $('#multi_unit_form input[name="is_default"]').prop('checked', multi_unit.is_default == 1);
                $('#multi_unit_form select[name="status"]').val(multi_unit.status).selectpicker('refresh');
                
                $('.add-title').hide();
                $('.edit-title').show();
                $('#multi_unit_modal').modal('show');
            } catch(e) {
                console.error('Error parsing multi unit data:', e);
                alert('Error loading multi unit data: ' + e.message);
            }
        })
        .fail(function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            alert('Failed to load multi unit data. Please try again.');
        });
    }

    // Make function globally available
    window.edit_multi_unit = edit_multi_unit;
    
    $('#multi_unit_modal').on('hidden.bs.modal', function() {
        $('#multi_unit_form')[0].reset();
        $('#multi_unit_form input[name="id"]').val('');
        $('.add-title').show();
        $('.edit-title').hide();
        $('#multi_unit_form .selectpicker').selectpicker('refresh');
        // Re-enable submit button
        $('#multi_unit_form button[type="submit"]').prop('disabled', false).text('<?= _l('submit'); ?>');
    });
    
});
</script>