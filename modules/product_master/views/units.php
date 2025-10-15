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
                                <a href="#" class="btn btn-info pull-left display-block" data-toggle="modal" data-target="#unit_modal">
                                    <i class="fa fa-plus-circle"></i> <?= _l('pm_add_unit'); ?>
                                </a>
                            <?php } ?>
                            <div class="clearfix"></div>
                        </div>
                        <hr class="hr-panel-heading" />
                        
                        <div class="clearfix"></div>
                        
                        <?= form_hidden('custom_view'); ?>
                        
                        <!-- Debug Info -->
                        <div class="alert alert-info" style="margin-bottom: 10px;">
                            <strong>Debug:</strong> Found <?= count($units ?? []); ?> units. 
                            <small>Using basic table (DataTables disabled for testing).</small>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="50"><?= _l('id'); ?></th>
                                        <th><?= _l('pm_unit_code'); ?></th>
                                        <th><?= _l('pm_unit_name'); ?></th>
                                        <th><?= _l('pm_unit_symbol'); ?></th>
                                        <th><?= _l('pm_unit_type'); ?></th>
                                        <th><?= _l('pm_base_unit'); ?></th>
                                        <th><?= _l('pm_conversion_factor'); ?></th>
                                        <th><?= _l('pm_sort_order'); ?></th>
                                        <th><?= _l('status'); ?></th>
                                        <th><?= _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($units)) { ?>
                                        <?php foreach ($units as $unit) { ?>
                                            <tr>
                                                <td><?= htmlspecialchars($unit['id'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($unit['unit_code'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($unit['unit_name'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($unit['unit_symbol'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars(ucfirst($unit['unit_type'] ?? '')); ?></td>
                                                <td><?= ($unit['base_unit'] ?? 0) ? '<span class="label label-success">' . _l('yes') . '</span>' : '<span class="label label-default">' . _l('no') . '</span>'; ?></td>
                                                <td><?= htmlspecialchars($unit['conversion_factor'] ?? '1.0000'); ?></td>
                                                <td><?= htmlspecialchars($unit['sort_order'] ?? '0'); ?></td>
                                                <td>
                                                    <?php 
                                                    if (function_exists('render_pm_status_badge')) {
                                                        echo render_pm_status_badge($unit['status'] ?? 0);
                                                    } else {
                                                        echo ($unit['status'] ?? 0) == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';
                                                    }
                                                    ?>
                                                </td>
                                            <td>
                                                <?php if (has_permission('product_master', '', 'edit')) { ?>
                                                    <a href="#" class="btn btn-default btn-icon" 
                                                       onclick="edit_unit(<?= $unit['id']; ?>); return false;"
                                                       data-toggle="tooltip" title="<?= _l('edit'); ?>">
                                                        <i class="fa fa-pencil-square-o"></i>
                                                    </a>
                                                <?php } ?>
                                                <?php if (has_permission('product_master', '', 'delete')) { ?>
                                                    <a href="<?= admin_url('product_master/delete/unit/' . $unit['id']); ?>" 
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
                                                <p><strong>No units found.</strong></p>
                                                <p>Click the "Add Unit" button above to create your first unit.</p>
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

<!-- Unit Modal -->
<div class="modal fade" id="unit_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="add-title"><?= _l('pm_add_unit'); ?></span>
                    <span class="edit-title" style="display:none;"><?= _l('pm_edit_unit'); ?></span>
                </h4>
            </div>
            <?= form_open(admin_url('product_master/units'), ['id' => 'unit_form']); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?= form_hidden('id'); ?>
                        
                        <?= render_input('unit_code', 'pm_unit_code', '', 'text', ['required' => true, 'maxlength' => 100]); ?>
                        
                        <?= render_input('unit_name', 'pm_unit_name', '', 'text', ['required' => true, 'maxlength' => 255]); ?>
                        
                        <?= render_input('unit_symbol', 'pm_unit_symbol', '', 'text', ['required' => true, 'maxlength' => 20]); ?>
                        
                        <div class="form-group">
                            <label for="unit_type" class="control-label"><?= _l('pm_unit_type'); ?> *</label>
                            <select name="unit_type" id="unit_type" class="form-control selectpicker" required>
                                <option value="length"><?= _l('pm_unit_type_length'); ?></option>
                                <option value="weight"><?= _l('pm_unit_type_weight'); ?></option>
                                <option value="volume"><?= _l('pm_unit_type_volume'); ?></option>
                                <option value="area"><?= _l('pm_unit_type_area'); ?></option>
                                <option value="quantity" selected><?= _l('pm_unit_type_quantity'); ?></option>
                                <option value="time"><?= _l('pm_unit_type_time'); ?></option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="base_unit" id="base_unit" value="1">
                                <label for="base_unit"><?= _l('pm_base_unit'); ?></label>
                            </div>
                        </div>
                        
                        <?= render_input('conversion_factor', 'pm_conversion_factor', '1.0000', 'number', ['step' => '0.0001', 'min' => '0.0001']); ?>
                        
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
    console.log('Units page loaded successfully without DataTables');
    
    // Handle form submission via AJAX
    $('#unit_form').on('submit', function(e) {
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
                $('#unit_modal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                alert('An error occurred while saving. Please try again.');
                submitBtn.prop('disabled', false).text('<?= _l('submit'); ?>');
            }
        });
    });
    
    function edit_unit(id) {
        // Get unit data via AJAX and populate modal
        $.get('<?= admin_url('product_master/get_unit/'); ?>' + id)
        .done(function(data) {
            try {
                var unit = JSON.parse(data);
                $('#unit_form input[name="id"]').val(unit.id);
                $('#unit_form input[name="unit_code"]').val(unit.unit_code);
                $('#unit_form input[name="unit_name"]').val(unit.unit_name);
                $('#unit_form input[name="unit_symbol"]').val(unit.unit_symbol);
                $('#unit_form select[name="unit_type"]').val(unit.unit_type).selectpicker('refresh');
                $('#unit_form input[name="base_unit"]').prop('checked', unit.base_unit == 1);
                $('#unit_form input[name="conversion_factor"]').val(unit.conversion_factor);
                $('#unit_form textarea[name="description"]').val(unit.description);
                $('#unit_form input[name="sort_order"]').val(unit.sort_order);
                $('#unit_form select[name="status"]').val(unit.status).selectpicker('refresh');
                
                $('.add-title').hide();
                $('.edit-title').show();
                $('#unit_modal').modal('show');
            } catch(e) {
                console.error('Error parsing unit data:', e);
                alert('Error loading unit data: ' + e.message);
            }
        })
        .fail(function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            alert('Failed to load unit data. Please try again.');
        });
    }

    // Make function globally available
    window.edit_unit = edit_unit;
    
    $('#unit_modal').on('hidden.bs.modal', function() {
        $('#unit_form')[0].reset();
        $('#unit_form input[name="id"]').val('');
        $('.add-title').show();
        $('.edit-title').hide();
        $('#unit_form .selectpicker').selectpicker('refresh');
        // Re-enable submit button
        $('#unit_form button[type="submit"]').prop('disabled', false).text('<?= _l('submit'); ?>');
    });
    
});
</script>