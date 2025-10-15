<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <h4 class="customer-profile-group-heading"><?php echo $title; ?></h4>
                            <hr class="hr-panel-heading">
                        </div>

                        <?php if ($server_info['is_live']): ?>
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong>Live Server Detected:</strong> Direct Tally connection is not possible on live servers. Use this file import tool instead.
                            <br><small>Server: <?php echo htmlspecialchars($server_info['server_name']); ?></small>
                        </div>
                        <?php endif; ?>

                        <!-- File Import Form -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="panel panel-primary">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><i class="fa fa-upload"></i> Upload Tally Data File</h3>
                                    </div>
                                    <div class="panel-body">
                                        <?php echo form_open_multipart(admin_url('tally_integration/file_import')); ?>
                                        
                                        <div class="form-group">
                                            <label for="tally_file">Select Tally Data File</label>
                                            <input type="file" name="tally_file" id="tally_file" class="form-control" accept=".csv,.xml,.txt" required>
                                            <small class="text-muted">Supported formats: CSV, XML, TXT (Max size: 10MB)</small>
                                        </div>

                                        <div class="form-group">
                                            <label>File Type:</label>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="file_type" value="customers" checked>
                                                    Customer Data (CSV/XML)
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="file_type" value="invoices">
                                                    Invoice Data (XML)
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="file_type" value="payments">
                                                    Payment Data (XML)
                                                </label>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-upload"></i> Upload & Import
                                        </button>
                                        
                                        <?php echo form_close(); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Instructions Panel -->
                                <div class="panel panel-info">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><i class="fa fa-info-circle"></i> Instructions</h3>
                                    </div>
                                    <div class="panel-body">
                                        <h5><i class="fa fa-laptop"></i> Step 1: Export from Local Tally</h5>
                                        <p class="text-muted small">
                                            Use your local <code>testmannualy.php</code> or export CSV from TallyPrime directly.
                                        </p>
                                        
                                        <h5><i class="fa fa-upload"></i> Step 2: Upload File</h5>
                                        <p class="text-muted small">
                                            Upload the exported file using the form on the left.
                                        </p>
                                        
                                        <h5><i class="fa fa-database"></i> Step 3: Import to CRM</h5>
                                        <p class="text-muted small">
                                            Data will be automatically imported into your CRM database.
                                        </p>
                                    </div>
                                </div>

                                <!-- CSV Format Help -->
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><i class="fa fa-table"></i> CSV Format</h3>
                                    </div>
                                    <div class="panel-body">
                                        <p class="text-muted small">Expected CSV columns:</p>
                                        <code class="small">
                                            Company Name, Email, Phone, Address, Balance
                                        </code>
                                        <br><br>
                                        <p class="text-muted small">Example:</p>
                                        <code class="small">
                                            John Doe Ltd, john@example.com, 9876543210, Delhi, 5000.00
                                        </code>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><i class="fa fa-flash"></i> Quick Actions</h3>
                                    </div>
                                    <div class="panel-body">
                                        <a href="<?php echo base_url('testmannualy.php'); ?>" target="_blank" class="btn btn-success btn-sm">
                                            <i class="fa fa-external-link"></i> Open Local Test Script
                                        </a>
                                        
                                        <a href="<?php echo admin_url('tally_integration'); ?>" class="btn btn-primary btn-sm">
                                            <i class="fa fa-dashboard"></i> Back to Dashboard
                                        </a>
                                        
                                        <a href="<?php echo admin_url('tally_integration/logs'); ?>" class="btn btn-info btn-sm">
                                            <i class="fa fa-list"></i> View Import Logs
                                        </a>
                                        
                                        <a href="<?php echo base_url('fix_tally_live_connection.php'); ?>" target="_blank" class="btn btn-warning btn-sm">
                                            <i class="fa fa-wrench"></i> Connection Fixes
                                        </a>
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

<script>
$(document).ready(function() {
    // File upload validation
    $('#tally_file').change(function() {
        var file = this.files[0];
        if (file) {
            var fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
            var fileType = file.name.split('.').pop().toLowerCase();
            
            if (fileSize > 10) {
                alert('File size must be less than 10MB');
                $(this).val('');
                return false;
            }
            
            if (!['csv', 'xml', 'txt'].includes(fileType)) {
                alert('Only CSV, XML, and TXT files are allowed');
                $(this).val('');
                return false;
            }
            
            console.log('File selected: ' + file.name + ' (' + fileSize + ' MB)');
        }
    });
});
</script>

<?php init_tail(); ?>