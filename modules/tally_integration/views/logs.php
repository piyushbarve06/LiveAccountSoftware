<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-history"></i>
                            <?php echo $title; ?>
                        </h3>
                        <div class="panel-heading-right">
                            <div class="btn-group">
                                <a href="<?php echo admin_url('tally_integration'); ?>" 
                                   class="btn btn-default btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back to Dashboard
                                </a>
                                <a href="<?php echo admin_url('tally_integration/clear_logs'); ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to clear all logs?')">
                                    <i class="fa fa-trash"></i> <?php echo _l('tally_sync_log_clear'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <!-- Filters -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="well well-sm">
                                    <?php echo form_open(admin_url('tally_integration/logs'), ['method' => 'get', 'class' => 'form-inline']); ?>
                                    <div class="form-group">
                                        <select name="sync_type" class="form-control input-sm">
                                            <option value="">All Types</option>
                                            <option value="customer" <?php echo $this->input->get('sync_type') == 'customer' ? 'selected' : ''; ?>>Customer</option>
                                            <option value="invoice" <?php echo $this->input->get('sync_type') == 'invoice' ? 'selected' : ''; ?>>Invoice</option>
                                            <option value="payment" <?php echo $this->input->get('sync_type') == 'payment' ? 'selected' : ''; ?>>Payment</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <select name="status" class="form-control input-sm">
                                            <option value="">All Status</option>
                                            <option value="success" <?php echo $this->input->get('status') == 'success' ? 'selected' : ''; ?>>Success</option>
                                            <option value="error" <?php echo $this->input->get('status') == 'error' ? 'selected' : ''; ?>>Error</option>
                                            <option value="pending" <?php echo $this->input->get('status') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fa fa-filter"></i> Filter
                                    </button>
                                    <a href="<?php echo admin_url('tally_integration/logs'); ?>" class="btn btn-default btn-sm">
                                        <i class="fa fa-times"></i> Clear Filters
                                    </a>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Logs Table -->
                        <?php if(empty($logs)): ?>
                            <div class="text-center">
                                <i class="fa fa-info-circle fa-3x text-muted"></i>
                                <h4>No sync logs found</h4>
                                <p class="text-muted">Sync logs will appear here after synchronization attempts.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="logs-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th><?php echo _l('tally_sync_log_type'); ?></th>
                                            <th><?php echo _l('tally_sync_log_record_id'); ?></th>
                                            <th><?php echo _l('tally_sync_log_status'); ?></th>
                                            <th><?php echo _l('tally_sync_log_date'); ?></th>
                                            <th>Duration</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($logs as $log): ?>
                                            <tr>
                                                <td><?php echo $log['id']; ?></td>
                                                <td>
                                                    <span class="text-capitalize">
                                                        <i class="fa fa-<?php 
                                                        echo $log['sync_type'] == 'customer' ? 'user' : 
                                                            ($log['sync_type'] == 'invoice' ? 'file-invoice' : 'credit-card'); 
                                                        ?>"></i>
                                                        <?php echo $log['sync_type']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <code><?php echo $log['record_id']; ?></code>
                                                </td>
                                                <td>
                                                    <?php if($log['status'] == 'success'): ?>
                                                        <span class="label label-success">
                                                            <i class="fa fa-check"></i> Success
                                                        </span>
                                                    <?php elseif($log['status'] == 'error'): ?>
                                                        <span class="label label-danger">
                                                            <i class="fa fa-times"></i> Error
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="label label-warning">
                                                            <i class="fa fa-clock"></i> Pending
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span title="<?php echo _dt($log['created_at']); ?>">
                                                        <?php echo time_ago($log['created_at']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if($log['updated_at']): ?>
                                                        <?php 
                                                        $start = strtotime($log['created_at']);
                                                        $end = strtotime($log['updated_at']);
                                                        $duration = $end - $start;
                                                        echo $duration > 0 ? $duration . 's' : '<1s';
                                                        ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-xs">
                                                        <button type="button" 
                                                                class="btn btn-default btn-xs view-log-details"
                                                                data-log-id="<?php echo $log['id']; ?>"
                                                                data-toggle="tooltip" 
                                                                title="View Details">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                        <?php if($log['status'] == 'error'): ?>
                                                            <button type="button" 
                                                                    class="btn btn-warning btn-xs retry-sync"
                                                                    data-log-id="<?php echo $log['id']; ?>"
                                                                    data-toggle="tooltip" 
                                                                    title="Retry Sync">
                                                                <i class="fa fa-redo"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Sync Log Details</h4>
            </div>
            <div class="modal-body">
                <div id="log-details-content">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                        <p>Loading details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
$(document).ready(function() {
    // Initialize DataTable
    <?php if(!empty($logs)): ?>
    $('#logs-table').DataTable({
        "order": [[ 0, "desc" ]],
        "pageLength": 25,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": 6 }
        ]
    });
    <?php endif; ?>

    // View log details
    $(document).on('click', '.view-log-details', function() {
        var logId = $(this).data('log-id');
        
        $('#logDetailsModal').modal('show');
        
        $.ajax({
            url: '<?php echo admin_url("tally_integration/get_log_details"); ?>/' + logId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var html = buildLogDetailsHtml(response.data);
                    $('#log-details-content').html(html);
                } else {
                    $('#log-details-content').html('<div class="alert alert-danger">' + 
                        (response.message || 'Failed to load log details') + '</div>');
                }
            },
            error: function() {
                $('#log-details-content').html('<div class="alert alert-danger">Error loading log details</div>');
            }
        });
    });

    // Retry sync
    $(document).on('click', '.retry-sync', function() {
        var $btn = $(this);
        var logId = $btn.data('log-id');
        
        if (!confirm('Are you sure you want to retry this sync?')) {
            return;
        }
        
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '<?php echo admin_url("tally_integration/retry_sync"); ?>',
            type: 'POST',
            data: { log_id: logId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Sync retry successful');
                    location.reload();
                } else {
                    alert('Sync retry failed: ' + (response.message || 'Unknown error'));
                    $btn.prop('disabled', false).html('<i class="fa fa-redo"></i>');
                }
            },
            error: function() {
                alert('Error during retry');
                $btn.prop('disabled', false).html('<i class="fa fa-redo"></i>');
            }
        });
    });

    function buildLogDetailsHtml(log) {
        var html = '<div class="row">';
        html += '<div class="col-md-6">';
        html += '<strong>Log ID:</strong> ' + log.id + '<br>';
        html += '<strong>Type:</strong> ' + log.sync_type + '<br>';
        html += '<strong>Record ID:</strong> ' + log.record_id + '<br>';
        html += '<strong>Status:</strong> ';
        
        if (log.status == 'success') {
            html += '<span class="label label-success">Success</span><br>';
        } else if (log.status == 'error') {
            html += '<span class="label label-danger">Error</span><br>';
        } else {
            html += '<span class="label label-warning">Pending</span><br>';
        }
        
        html += '</div>';
        html += '<div class="col-md-6">';
        html += '<strong>Created:</strong> ' + log.created_at + '<br>';
        if (log.updated_at) {
            html += '<strong>Updated:</strong> ' + log.updated_at + '<br>';
        }
        html += '</div>';
        html += '</div>';
        
        if (log.error_message) {
            html += '<hr><strong>Error Message:</strong>';
            html += '<div class="alert alert-danger">' + log.error_message + '</div>';
        }
        
        if (log.tally_response) {
            html += '<hr><strong>Tally Response:</strong>';
            html += '<pre class="well well-sm" style="max-height: 300px; overflow-y: auto;">' + 
                    log.tally_response + '</pre>';
        }
        
        return html;
    }

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
</body>
</html>