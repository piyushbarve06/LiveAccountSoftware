<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                                    <?php echo $title; ?>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <?php if($settings['enabled']): ?>
                                    <div class="btn-group">
                                        <a href="<?php echo admin_url('tally_integration/sync_all'); ?>" 
                                           class="btn btn-success">
                                            <i class="fa fa-sync"></i> <?php echo _l('tally_sync_all'); ?>
                                        </a>
                                        <a href="<?php echo admin_url('tally_integration/export_xml'); ?>" 
                                           class="btn btn-primary">
                                            <i class="fa fa-download"></i> <?php echo _l('tally_export_xml'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="widget-drilldown text-center">
                            <div class="details">
                                <span class="text-dark tw-font-semibold tw-text-lg">
                                    <?php echo _l('tally_connection_status'); ?>
                                </span>
                                <span class="tw-block mtop5">
                                    <?php if($connection_status['success']): ?>
                                        <span class="label label-success">
                                            <i class="fa fa-check"></i> Connected
                                        </span>
                                    <?php else: ?>
                                        <span class="label label-danger">
                                            <i class="fa fa-times"></i> Disconnected
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="widget-drilldown text-center">
                            <div class="details">
                                <span class="text-dark tw-font-semibold tw-text-lg">
                                    <?php echo $sync_stats['total_synced']; ?>
                                </span>
                                <span class="tw-block">
                                    <?php echo _l('tally_total_synced'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="widget-drilldown text-center">
                            <div class="details">
                                <span class="text-dark tw-font-semibold tw-text-lg">
                                    <?php echo $sync_stats['recent_activity']; ?>
                                </span>
                                <span class="tw-block">
                                    Last 24 Hours
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="widget-drilldown text-center">
                            <div class="details">
                                <span class="text-dark tw-font-semibold tw-text-lg">
                                    <?php 
                                    $last_sync = '';
                                    if (!empty($sync_stats['last_sync'])) {
                                        $latest = max($sync_stats['last_sync']);
                                        $last_sync = time_ago($latest);
                                    } else {
                                        $last_sync = 'Never';
                                    }
                                    echo $last_sync;
                                    ?>
                                </span>
                                <span class="tw-block">
                                    <?php echo _l('tally_last_sync'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sync Statistics -->
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-chart-bar"></i>
                            <?php echo _l('tally_sync_statistics'); ?>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Data Type</th>
                                        <th class="text-center">Success</th>
                                        <th class="text-center">Errors</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Customers</strong></td>
                                        <td class="text-center">
                                            <span class="label label-success">
                                                <?php echo isset($sync_stats['success_by_type']['customer']) ? $sync_stats['success_by_type']['customer'] : 0; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="label label-danger">
                                                <?php echo isset($sync_stats['errors_by_type']['customer']) ? $sync_stats['errors_by_type']['customer'] : 0; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="<?php echo admin_url('tally_integration/sync_customers'); ?>" 
                                                   class="btn btn-xs btn-primary" title="Export to Tally">
                                                    <i class="fa fa-upload"></i>
                                                </a>
                                                <a href="<?php echo admin_url('tally_integration/import_customers'); ?>" 
                                                   class="btn btn-xs btn-warning" title="Import from Tally">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Invoices</strong></td>
                                        <td class="text-center">
                                            <span class="label label-success">
                                                <?php echo isset($sync_stats['success_by_type']['invoice']) ? $sync_stats['success_by_type']['invoice'] : 0; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="label label-danger">
                                                <?php echo isset($sync_stats['errors_by_type']['invoice']) ? $sync_stats['errors_by_type']['invoice'] : 0; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="<?php echo admin_url('tally_integration/sync_invoices'); ?>" 
                                                   class="btn btn-xs btn-primary" title="Export to Tally">
                                                    <i class="fa fa-upload"></i>
                                                </a>
                                                <a href="<?php echo admin_url('tally_integration/import_invoices'); ?>" 
                                                   class="btn btn-xs btn-warning" title="Import from Tally">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Payments</strong></td>
                                        <td class="text-center">
                                            <span class="label label-success">
                                                <?php echo isset($sync_stats['success_by_type']['payment']) ? $sync_stats['success_by_type']['payment'] : 0; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="label label-danger">
                                                <?php echo isset($sync_stats['errors_by_type']['payment']) ? $sync_stats['errors_by_type']['payment'] : 0; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="<?php echo admin_url('tally_integration/sync_payments'); ?>" 
                                                   class="btn btn-xs btn-primary" title="Export to Tally">
                                                    <i class="fa fa-upload"></i>
                                                </a>
                                                <a href="<?php echo admin_url('tally_integration/import_payments'); ?>" 
                                                   class="btn btn-xs btn-warning" title="Import from Tally">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-history"></i>
                            <?php echo _l('tally_recent_logs'); ?>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <?php if(empty($recent_logs)): ?>
                            <p class="text-muted">No sync logs found</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recent_logs as $log): ?>
                                            <tr>
                                                <td>
                                                    <span class="text-capitalize">
                                                        <?php echo $log['sync_type']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if($log['status'] == 'success'): ?>
                                                        <span class="label label-success">Success</span>
                                                    <?php elseif($log['status'] == 'error'): ?>
                                                        <span class="label label-danger">Error</span>
                                                    <?php else: ?>
                                                        <span class="label label-warning">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo time_ago($log['created_at']); ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-right">
                                <a href="<?php echo admin_url('tally_integration/logs'); ?>" 
                                   class="btn btn-default btn-sm">
                                    View All Logs
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Status -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-cog"></i>
                            Configuration Status
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Integration Status</label>
                                    <div>
                                        <?php if($settings['enabled']): ?>
                                            <span class="label label-success">
                                                <i class="fa fa-check"></i> Enabled
                                            </span>
                                        <?php else: ?>
                                            <span class="label label-danger">
                                                <i class="fa fa-times"></i> Disabled
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Server Configuration</label>
                                    <div>
                                        <?php if($settings['server_url'] && $settings['company_name']): ?>
                                            <span class="label label-success">
                                                <i class="fa fa-check"></i> Configured
                                            </span>
                                            <small class="text-muted">
                                                (<?php echo $settings['server_url']; ?>)
                                            </small>
                                        <?php else: ?>
                                            <span class="label label-warning">
                                                <i class="fa fa-exclamation"></i> Not Configured
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <a href="<?php echo admin_url('tally_integration/settings'); ?>" 
                               class="btn btn-primary">
                                <i class="fa fa-cog"></i> Configure Settings
                            </a>
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
    // Auto-refresh connection status every 30 seconds
    <?php if($settings['enabled']): ?>
    setInterval(function() {
        // Optional: Add AJAX call to refresh connection status
    }, 30000);
    <?php endif; ?>
});
</script>
</body>
</html>