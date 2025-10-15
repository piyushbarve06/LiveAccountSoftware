<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-upload"></i>
                            <?php echo $title; ?>
                        </h3>
                        <div class="panel-actions">
                            <a href="<?php echo admin_url('tally_integration'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                    <div class="panel-body">
                        
                        <?php if(!$settings['enabled'] || empty($settings['server_url'])): ?>
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i>
                                <strong>Configuration Required:</strong> 
                                Please enable and configure Tally integration in 
                                <a href="<?php echo admin_url('tally_integration/settings'); ?>">settings</a> 
                                before importing data.
                            </div>
                        <?php else: ?>

                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <strong>Import from Tally:</strong> 
                                This feature allows you to import customers, invoices, and payments from Tally ERP to your CRM system.
                                Make sure Tally ERP is running with server mode enabled.
                            </div>

                            <?php echo form_open(admin_url('tally_integration/import_data'), 'id="import-form"'); ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="import_type" class="control-label">
                                            <strong>Select Data Type to Import</strong>
                                        </label>
                                        <select name="import_type" id="import_type" class="form-control selectpicker" required>
                                            <option value="">Choose what to import...</option>
                                            <option value="customers">Customers (Ledgers)</option>
                                            <option value="invoices">Invoices (Sales Vouchers)</option>
                                            <option value="payments">Payments (Receipt Vouchers)</option>
                                            <option value="all">All Data (Customers + Invoices + Payments)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">
                                            <strong>Import Options</strong>
                                        </label>
                                        <div class="checkbox">
                                            <input type="checkbox" id="preview_mode" name="preview_mode" checked>
                                            <label for="preview_mode">Preview data before import</label>
                                        </div>
                                        <small class="text-muted">Recommended: Preview data to check what will be imported</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Date Range Selection (for invoices and payments) -->
                            <div class="row" id="date-range-section" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_from" class="control-label">
                                            From Date
                                        </label>
                                        <input type="date" id="date_from" name="date_from" class="form-control" 
                                               value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_to" class="control-label">
                                            To Date
                                        </label>
                                        <input type="date" id="date_to" name="date_to" class="form-control" 
                                               value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Action Buttons -->
                            <div class="form-group">
                                <button type="button" id="preview-btn" class="btn btn-info" style="display: none;">
                                    <i class="fa fa-eye"></i> Preview Import Data
                                </button>
                                <button type="submit" id="import-btn" class="btn btn-success">
                                    <i class="fa fa-upload"></i> Start Import
                                </button>
                            </div>

                            <?php echo form_close(); ?>

                            <!-- Preview Section -->
                            <div id="preview-section" style="display: none;">
                                <hr>
                                <div class="panel panel-info">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">Import Preview</h4>
                                    </div>
                                    <div class="panel-body">
                                        <div id="preview-loading" style="display: none;">
                                            <div class="text-center">
                                                <i class="fa fa-spinner fa-spin fa-2x"></i>
                                                <p class="mtop10">Loading preview data from Tally...</p>
                                            </div>
                                        </div>
                                        <div id="preview-content"></div>
                                    </div>
                                </div>
                            </div>

                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Import Panel -->
                <?php if($settings['enabled'] && $settings['server_url']): ?>
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-bolt"></i>
                            Quick Import Actions
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="<?php echo admin_url('tally_integration/import_customers'); ?>" 
                                   class="btn btn-block btn-success">
                                    <i class="fa fa-users"></i><br>
                                    Import Customers<br>
                                    <small>Import all customer ledgers</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo admin_url('tally_integration/import_invoices'); ?>" 
                                   class="btn btn-block btn-info">
                                    <i class="fa fa-file-invoice"></i><br>
                                    Import Invoices<br>
                                    <small>Last 30 days</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo admin_url('tally_integration/import_payments'); ?>" 
                                   class="btn btn-block btn-warning">
                                    <i class="fa fa-credit-card"></i><br>
                                    Import Payments<br>
                                    <small>Last 30 days</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo admin_url('tally_integration/import_all'); ?>" 
                                   class="btn btn-block btn-danger"
                                   onclick="return confirm('This will import all data from Tally. Continue?');">
                                    <i class="fa fa-sync-alt"></i><br>
                                    Import All Data<br>
                                    <small>Customers + Invoices + Payments</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Import Guidelines -->
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-info-circle"></i>
                            Import Guidelines
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="fa fa-check-circle text-success"></i> What Gets Imported:</h5>
                                <ul>
                                    <li><strong>Customers:</strong> All ledgers under "Sundry Debtors"</li>
                                    <li><strong>Invoices:</strong> Sales vouchers with customer details</li>
                                    <li><strong>Payments:</strong> Receipt vouchers linked to customers</li>
                                    <li><strong>Data Matching:</strong> Existing records are updated, new ones are created</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fa fa-exclamation-triangle text-warning"></i> Important Notes:</h5>
                                <ul>
                                    <li>Ensure Tally ERP is running with server mode enabled (port 9000)</li>
                                    <li>Company should be open in Tally during import</li>
                                    <li>Large imports may take some time - please be patient</li>
                                    <li>Check sync logs for any errors after import</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Import Functionality -->
<script>
$(document).ready(function() {
    // Show/hide date range based on import type
    $('#import_type').on('change', function() {
        const importType = $(this).val();
        const needsDateRange = ['invoices', 'payments', 'all'].includes(importType);
        
        if (needsDateRange) {
            $('#date-range-section').show();
        } else {
            $('#date-range-section').hide();
        }

        // Show/hide preview button
        if (importType && $('#preview_mode').is(':checked')) {
            $('#preview-btn').show();
        } else {
            $('#preview-btn').hide();
        }
    });

    // Toggle preview button based on preview mode checkbox
    $('#preview_mode').on('change', function() {
        if ($(this).is(':checked') && $('#import_type').val()) {
            $('#preview-btn').show();
        } else {
            $('#preview-btn').hide();
            $('#preview-section').hide();
        }
    });

    // Preview functionality
    $('#preview-btn').on('click', function() {
        const importType = $('#import_type').val();
        const dateFrom = $('#date_from').val();
        const dateTo = $('#date_to').val();

        if (!importType) {
            alert('Please select an import type first.');
            return;
        }

        // Show preview section and loading
        $('#preview-section').show();
        $('#preview-loading').show();
        $('#preview-content').html('');

        // AJAX request to get preview
        $.ajax({
            url: admin_url + 'tally_integration/preview_import',
            type: 'POST',
            dataType: 'json',
            data: {
                type: importType,
                date_from: dateFrom,
                date_to: dateTo,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            success: function(response) {
                $('#preview-loading').hide();
                
                if (response.success) {
                    displayPreview(importType, response.data, response.total_count);
                } else {
                    $('#preview-content').html(
                        '<div class="alert alert-danger">' +
                        '<i class="fa fa-times-circle"></i> ' +
                        'Error loading preview: ' + response.message +
                        '</div>'
                    );
                }
            },
            error: function(xhr, status, error) {
                $('#preview-loading').hide();
                $('#preview-content').html(
                    '<div class="alert alert-danger">' +
                    '<i class="fa fa-times-circle"></i> ' +
                    'Connection error. Please check your Tally server connection.' +
                    '</div>'
                );
            }
        });
    });

    // Function to display preview data
    function displayPreview(type, data, totalCount) {
        let html = '<div class="alert alert-success">' +
                   '<i class="fa fa-check-circle"></i> ' +
                   'Found ' + totalCount + ' records ready for import';
        
        if (totalCount > 10) {
            html += ' (showing first 10 as preview)';
        }
        
        html += '</div>';

        if (data && data.length > 0) {
            html += '<div class="table-responsive">';
            html += '<table class="table table-striped table-bordered">';
            
            // Generate table header based on type
            html += '<thead><tr>';
            if (type === 'customers') {
                html += '<th>Customer Name</th><th>Email</th><th>Phone</th><th>Address</th>';
            } else if (type === 'invoices') {
                html += '<th>Invoice Number</th><th>Date</th><th>Customer</th><th>Amount</th>';
            } else if (type === 'payments') {
                html += '<th>Receipt Number</th><th>Date</th><th>Customer</th><th>Amount</th><th>Mode</th>';
            }
            html += '</tr></thead>';
            
            // Generate table rows
            html += '<tbody>';
            data.forEach(function(item) {
                html += '<tr>';
                if (type === 'customers') {
                    html += '<td>' + (item.name || '') + '</td>';
                    html += '<td>' + (item.email || '') + '</td>';
                    html += '<td>' + (item.phone || '') + '</td>';
                    html += '<td>' + (item.address || '') + '</td>';
                } else if (type === 'invoices') {
                    html += '<td>' + (item.voucher_number || '') + '</td>';
                    html += '<td>' + (item.date || '') + '</td>';
                    html += '<td>' + (item.customer_name || '') + '</td>';
                    html += '<td>' + (item.amount || 0) + '</td>';
                } else if (type === 'payments') {
                    html += '<td>' + (item.voucher_number || '') + '</td>';
                    html += '<td>' + (item.date || '') + '</td>';
                    html += '<td>' + (item.customer_name || '') + '</td>';
                    html += '<td>' + (item.amount || 0) + '</td>';
                    html += '<td>' + (item.payment_mode || '') + '</td>';
                }
                html += '</tr>';
            });
            html += '</tbody>';
            html += '</table>';
            html += '</div>';
        }

        $('#preview-content').html(html);
    }

    // Form submission with confirmation
    $('#import-form').on('submit', function(e) {
        const importType = $('#import_type').val();
        if (!importType) {
            e.preventDefault();
            alert('Please select an import type.');
            return false;
        }

        // If preview mode is enabled and no preview was shown, require preview first
        if ($('#preview_mode').is(':checked') && $('#preview-content').html() === '') {
            e.preventDefault();
            alert('Please preview the data before importing.');
            return false;
        }

        const message = 'This will import ' + importType + ' from Tally to your CRM. ' +
                       'Existing records will be updated. Continue?';
        
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }

        // Show loading state
        $('#import-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Importing...');
    });
});
</script>

<?php init_tail(); ?>