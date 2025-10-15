<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-cog"></i>
                            <?php echo $title; ?>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open(admin_url('tally_integration/settings')); ?>
                        
                        <!-- Enable Integration -->
                        <div class="form-group">
                            <div class="checkbox">
                                <input type="checkbox" name="tally_integration_enabled" id="tally_integration_enabled" 
                                       <?php echo $settings['enabled'] ? 'checked' : ''; ?>>
                                <label for="tally_integration_enabled">
                                    <strong><?php echo _l('tally_enable_integration'); ?></strong>
                                </label>
                            </div>
                        </div>

                        <hr>

                        <!-- Connection Settings -->
                        <h4><?php echo _l('tally_connection_settings'); ?></h4>

                        <div class="form-group">
                            <label for="tally_version_name" class="control-label">
                                <?php echo _l('tally_version_name'); ?>
                                <small class="req text-danger">*</small>
                            </label>
                            <select id="tally_version_name" name="tally_version_name" class="form-control">
                                <option value="">Select your tally version here</option>
                                <option value="vesion_2.1" <?php echo ($settings['version_name'] ?? '') == 'vesion_2.1' ? 'selected' : ''; ?>>Version 2.1</option>
                                <option value="vesion_6.2" <?php echo ($settings['version_name'] ?? '') == 'vesion_6.2' ? 'selected' : ''; ?>>Version 6.2</option>
                            </select>
                            <small class="text-muted"><?php echo _l('tally_version_msg'); ?></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="tally_server_url" class="control-label">
                                <?php echo _l('tally_server_url'); ?>
                                <small class="req text-danger">*</small>
                            </label>
                            <input type="text" id="tally_server_url" name="tally_server_url" 
                                   class="form-control" value="<?php echo $settings['server_url']; ?>"
                                   placeholder="http://localhost:9002 or http://192.168.1.100:9000">
                            <small class="text-muted"><?php echo _l('tally_help_server_url'); ?></small>
                        </div>

                        <div class="form-group">
                            <label for="tally_company_name" class="control-label">
                                <?php echo _l('tally_company_name'); ?>
                                <small class="req text-danger">*</small>
                            </label>
                            <input type="text" id="tally_company_name" name="tally_company_name" 
                                   class="form-control" value="<?php echo $settings['company_name']; ?>"
                                   placeholder="Company Name as in Tally">
                            <small class="text-muted"><?php echo _l('tally_help_company_name'); ?></small>
                        </div>

                        <div class="form-group">
                            <a href="<?php echo admin_url('tally_integration/test_connection'); ?>" 
                               class="btn btn-info" id="test-connection">
                                <i class="fa fa-plug"></i> <?php echo _l('tally_test_connection'); ?>
                            </a>
                        </div>

                        <hr>

                        <!-- Sync Settings -->
                        <h4><?php echo _l('tally_sync_settings'); ?></h4>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <input type="checkbox" name="tally_auto_sync_customers" id="tally_auto_sync_customers"
                                               <?php echo $settings['auto_sync_customers'] ? 'checked' : ''; ?>>
                                        <label for="tally_auto_sync_customers">
                                            <?php echo _l('tally_auto_sync_customers'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <input type="checkbox" name="tally_auto_sync_invoices" id="tally_auto_sync_invoices"
                                               <?php echo $settings['auto_sync_invoices'] ? 'checked' : ''; ?>>
                                        <label for="tally_auto_sync_invoices">
                                            <?php echo _l('tally_auto_sync_invoices'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <input type="checkbox" name="tally_auto_sync_payments" id="tally_auto_sync_payments"
                                               <?php echo $settings['auto_sync_payments'] ? 'checked' : ''; ?>>
                                        <label for="tally_auto_sync_payments">
                                            <?php echo _l('tally_auto_sync_payments'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <input type="checkbox" name="tally_sync_on_create" id="tally_sync_on_create"
                                               <?php echo $settings['sync_on_create'] ? 'checked' : ''; ?>>
                                        <label for="tally_sync_on_create">
                                            <?php echo _l('tally_sync_on_create'); ?>
                                        </label>
                                    </div>
                                    <small class="text-muted">Sync automatically when new records are created</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <input type="checkbox" name="tally_sync_on_update" id="tally_sync_on_update"
                                               <?php echo $settings['sync_on_update'] ? 'checked' : ''; ?>>
                                        <label for="tally_sync_on_update">
                                            <?php echo _l('tally_sync_on_update'); ?>
                                        </label>
                                    </div>
                                    <small class="text-muted">Sync automatically when records are updated</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <strong>Note:</strong> Auto-sync settings apply to data being sent FROM CRM TO Tally.
                        </div>

                        <hr>

                        <!-- Import Settings -->
                        <h4>Import Settings (Tally to CRM)</h4>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <input type="checkbox" name="tally_auto_import_customers" id="tally_auto_import_customers"
                                               <?php echo get_option('tally_auto_import_customers') ? 'checked' : ''; ?>>
                                        <label for="tally_auto_import_customers">
                                            Auto Import Customers
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <input type="checkbox" name="tally_auto_import_invoices" id="tally_auto_import_invoices"
                                               <?php echo get_option('tally_auto_import_invoices') ? 'checked' : ''; ?>>
                                        <label for="tally_auto_import_invoices">
                                            Auto Import Invoices
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <input type="checkbox" name="tally_auto_import_payments" id="tally_auto_import_payments"
                                               <?php echo get_option('tally_auto_import_payments') ? 'checked' : ''; ?>>
                                        <label for="tally_auto_import_payments">
                                            Auto Import Payments
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tally_import_frequency" class="control-label">
                                        Import Frequency (minutes)
                                    </label>
                                    <select name="tally_import_frequency" id="tally_import_frequency" class="form-control">
                                        <option value="15" <?php echo get_option('tally_import_frequency') == '15' ? 'selected' : ''; ?>>Every 15 minutes</option>
                                        <option value="30" <?php echo get_option('tally_import_frequency') == '30' ? 'selected' : ''; ?>>Every 30 minutes</option>
                                        <option value="60" <?php echo get_option('tally_import_frequency') == '60' ? 'selected' : ''; ?>>Every hour</option>
                                        <option value="120" <?php echo get_option('tally_import_frequency') == '120' ? 'selected' : ''; ?>>Every 2 hours</option>
                                        <option value="480" <?php echo get_option('tally_import_frequency') == '480' ? 'selected' : ''; ?>>Every 8 hours</option>
                                        <option value="1440" <?php echo get_option('tally_import_frequency') == '1440' ? 'selected' : ''; ?>>Once daily</option>
                                    </select>
                                    <small class="text-muted">How often to automatically check for new data in Tally</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tally_import_days_back" class="control-label">
                                        Import Days Back
                                    </label>
                                    <input type="number" id="tally_import_days_back" name="tally_import_days_back" 
                                           class="form-control" value="<?php echo get_option('tally_import_days_back') ?: '7'; ?>"
                                           min="1" max="365">
                                    <small class="text-muted">How many days back to look for new/updated data</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong>Auto Import:</strong> Automatic import requires a cron job to be set up. 
                            Contact your system administrator to schedule the import task.
                        </div>

                        <hr>

                        <!-- Action Buttons -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Save Settings
                            </button>
                            <a href="<?php echo admin_url('tally_integration'); ?>" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>

                <!-- Quick Actions Panel -->
                <?php if($settings['enabled'] && $settings['server_url'] && $settings['company_name']): ?>
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-bolt"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="<?php echo admin_url('tally_integration/sync_customers'); ?>" 
                                   class="btn btn-block btn-success">
                                    <i class="fa fa-users"></i><br>
                                    <?php echo _l('tally_sync_customers'); ?>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="<?php echo admin_url('tally_integration/sync_invoices'); ?>" 
                                   class="btn btn-block btn-info">
                                    <i class="fa fa-file-invoice"></i><br>
                                    <?php echo _l('tally_sync_invoices'); ?>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="<?php echo admin_url('tally_integration/sync_payments'); ?>" 
                                   class="btn btn-block btn-warning">
                                    <i class="fa fa-credit-card"></i><br>
                                    <?php echo _l('tally_sync_payments'); ?>
                                </a>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <a href="<?php echo admin_url('tally_integration/sync_all'); ?>" 
                                   class="btn btn-block btn-danger">
                                    <i class="fa fa-sync-alt"></i>
                                    <?php echo _l('tally_sync_all'); ?>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="<?php echo admin_url('tally_integration/export_xml'); ?>" 
                                   class="btn btn-block btn-primary">
                                    <i class="fa fa-download"></i>
                                    <?php echo _l('tally_export_xml'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Requirements Check -->
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-check-circle"></i>
                            System Requirements
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tr>
                                    <td>PHP cURL Extension</td>
                                    <td>
                                        <?php if(extension_loaded('curl')): ?>
                                            <span class="label label-success">
                                                <i class="fa fa-check"></i> Available
                                            </span>
                                        <?php else: ?>
                                            <span class="label label-danger">
                                                <i class="fa fa-times"></i> Missing
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>PHP XML Extension</td>
                                    <td>
                                        <?php if(extension_loaded('xml')): ?>
                                            <span class="label label-success">
                                                <i class="fa fa-check"></i> Available
                                            </span>
                                        <?php else: ?>
                                            <span class="label label-danger">
                                                <i class="fa fa-times"></i> Missing
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Tally Server Connection</td>
                                    <td>
                                        <?php if($settings['server_url']): ?>
                                            <span class="label label-info">
                                                <i class="fa fa-info"></i> Configured
                                            </span>
                                        <?php else: ?>
                                            <span class="label label-warning">
                                                <i class="fa fa-exclamation"></i> Not Configured
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(document).ready(function() {
    // Form validation
    $('form').on('submit', function(e) {
        var serverUrl = $('#tally_server_url').val();
        var companyName = $('#tally_company_name').val();
        var enabled = $('#tally_integration_enabled').is(':checked');
        
        if (enabled && (!serverUrl || !companyName)) {
            e.preventDefault();
            alert('Please fill in Server URL and Company Name when integration is enabled.');
            return false;
        }
    });

    // Test connection with loading state
    $('#test-connection').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var originalText = $btn.html();
        
        $btn.html('<i class="fa fa-spinner fa-spin"></i> Testing...').prop('disabled', true);
        
        // Add a small delay to show the loading state
        setTimeout(function() {
            window.location.href = $btn.attr('href');
        }, 500);
    });

    // Enable/disable fields based on integration status
    $('#tally_integration_enabled').on('change', function() {
        var enabled = $(this).is(':checked');
        var fields = ['tally_server_url', 'tally_company_name'];
        
        $.each(fields, function(index, field) {
            $('#' + field).prop('disabled', !enabled);
        });
    });

    // Trigger the change event on page load
    $('#tally_integration_enabled').trigger('change');
});
</script>
</body>
</html>