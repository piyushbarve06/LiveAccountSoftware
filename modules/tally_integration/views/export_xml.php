<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-download"></i>
                            <?php echo $title; ?>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open(admin_url('tally_integration/export_xml')); ?>
                        
                        <div class="form-group">
                            <label for="export_type" class="control-label">
                                <?php echo _l('tally_select_data_type'); ?>
                                <small class="req text-danger">*</small>
                            </label>
                            <select name="export_type" id="export_type" class="form-control selectpicker" 
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" required>
                                <option value=""></option>
                                <option value="customers">
                                    <i class="fa fa-users"></i> <?php echo _l('clients'); ?>
                                </option>
                                <option value="invoices">
                                    <i class="fa fa-file-invoice"></i> <?php echo _l('invoices'); ?>
                                </option>
                                <option value="payments">
                                    <i class="fa fa-credit-card"></i> <?php echo _l('payments'); ?>
                                </option>
                            </select>
                        </div>

                        <hr>
                        
                        <h4><?php echo _l('tally_select_date_range'); ?></h4>
                        
                        <div class="form-group">
                            <label for="period" class="control-label">Period</label>
                            <select class="form-control selectpicker" name="period" id="period">
                                <option value="all_time">All Time</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_year">This Year</option>
                                <option value="last_year">Last Year</option>
                                <option value="last_3_months">Last 3 Months</option>
                                <option value="last_6_months">Last 6 Months</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>

                        <div id="custom-date-range" class="hide">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="start_date" class="control-label">From Date</label>
                                        <div class="input-group date">
                                            <input type="text" class="form-control datepicker" 
                                                   id="start_date" name="start_date" autocomplete="off">
                                            <div class="input-group-addon">
                                                <i class="fa-regular fa-calendar calendar-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_date" class="control-label">To Date</label>
                                        <div class="input-group date">
                                            <input type="text" class="form-control datepicker" 
                                                   id="end_date" name="end_date" autocomplete="off">
                                            <div class="input-group-addon">
                                                <i class="fa-regular fa-calendar calendar-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <strong>Export Information:</strong>
                            <ul class="m-0">
                                <li>Data will be exported in Tally-compatible XML format</li>
                                <li>You can import the generated XML file directly into Tally</li>
                                <li>Large datasets may take some time to process</li>
                                <li>Exported file will be automatically downloaded</li>
                            </ul>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-download"></i> Generate & Download XML
                            </button>
                        </div>

                        <div class="text-center">
                            <a href="<?php echo admin_url('tally_integration'); ?>" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>

                <!-- Export Guidelines -->
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-book"></i>
                            Export Guidelines
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="accordion" id="exportGuidelines">
                            <!-- Customers Export -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#exportGuidelines" href="#customersGuide">
                                            <i class="fa fa-users"></i> Customers Export
                                        </a>
                                    </h4>
                                </div>
                                <div id="customersGuide" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <p><strong>What gets exported:</strong></p>
                                        <ul>
                                            <li>Customer/Client name as Ledger Master</li>
                                            <li>Contact information (phone, email)</li>
                                            <li>Billing address details</li>
                                            <li>VAT/Tax registration numbers</li>
                                        </ul>
                                        <p><strong>Tally Import:</strong></p>
                                        <ul>
                                            <li>Creates Ledger under "Sundry Debtors"</li>
                                            <li>Alt+F12 → Company Features → Accounting Features → Maintain Payroll</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoices Export -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#exportGuidelines" href="#invoicesGuide">
                                            <i class="fa fa-file-invoice"></i> Invoices Export
                                        </a>
                                    </h4>
                                </div>
                                <div id="invoicesGuide" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <p><strong>What gets exported:</strong></p>
                                        <ul>
                                            <li>Invoice details as Sales Vouchers</li>
                                            <li>Invoice items and quantities</li>
                                            <li>Customer references</li>
                                            <li>Tax calculations</li>
                                        </ul>
                                        <p><strong>Requirements:</strong></p>
                                        <ul>
                                            <li>Customer ledgers must exist in Tally</li>
                                            <li>Stock items should be pre-configured</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Payments Export -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#exportGuidelines" href="#paymentsGuide">
                                            <i class="fa fa-credit-card"></i> Payments Export
                                        </a>
                                    </h4>
                                </div>
                                <div id="paymentsGuide" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <p><strong>What gets exported:</strong></p>
                                        <ul>
                                            <li>Payment records as Receipt Vouchers</li>
                                            <li>Payment modes and amounts</li>
                                            <li>Customer payment allocations</li>
                                            <li>Payment dates and references</li>
                                        </ul>
                                        <p><strong>Prerequisites:</strong></p>
                                        <ul>
                                            <li>Payment mode ledgers in Tally</li>
                                            <li>Corresponding invoice entries</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
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
    // Show/hide custom date range
    $('#period').on('change', function() {
        var selectedPeriod = $(this).val();
        var customRange = $('#custom-date-range');
        
        if (selectedPeriod === 'custom') {
            customRange.removeClass('hide');
            $('#start_date, #end_date').prop('required', true);
        } else {
            customRange.addClass('hide');
            $('#start_date, #end_date').prop('required', false);
        }
    });

    // Form submission with loading state
    $('form').on('submit', function() {
        var $submitBtn = $('button[type="submit"]');
        var originalText = $submitBtn.html();
        
        $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Generating XML...').prop('disabled', true);
        
        // Re-enable button after 10 seconds in case of issues
        setTimeout(function() {
            $submitBtn.html(originalText).prop('disabled', false);
        }, 10000);
    });

    // Form validation
    $('form').on('submit', function(e) {
        var exportType = $('#export_type').val();
        var period = $('#period').val();
        
        if (!exportType) {
            e.preventDefault();
            alert('Please select a data type to export.');
            $('#export_type').focus();
            return false;
        }
        
        if (period === 'custom') {
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();
            
            if (!startDate || !endDate) {
                e.preventDefault();
                alert('Please select both start and end dates for custom range.');
                return false;
            }
            
            if (new Date(startDate) > new Date(endDate)) {
                e.preventDefault();
                alert('Start date must be before end date.');
                return false;
            }
        }
    });

    // Initialize date pickers
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });

    // Data type change handler
    $('#export_type').on('change', function() {
        var selectedType = $(this).val();
        
        // You can add specific logic here based on selected type
        // For example, show/hide certain options or provide type-specific help
    });
});
</script>
</body>
</html>