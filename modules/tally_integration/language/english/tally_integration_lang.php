<?php

# Version 1.0.0

$lang['tally_integration'] = 'Tally Integration';
$lang['tally_settings'] = 'Tally Settings';
$lang['tally_sync_logs'] = 'Sync Logs';
$lang['tally_dashboard'] = 'Tally Dashboard';

// Settings
$lang['tally_integration_settings'] = 'Tally Integration Settings';
$lang['tally_enable_integration'] = 'Enable Tally Integration';
$lang['tally_server_url'] = 'Tally Server URL';
$lang['tally_server_port'] = 'Tally Server Port';
$lang['tally_company_name'] = 'Tally Company Name';
$lang['tally_version_name'] = 'Tally Version';
$lang['tally_connection_settings'] = 'Connection Settings';
$lang['tally_sync_settings'] = 'Sync Settings';
$lang['tally_auto_sync_invoices'] = 'Auto Sync Invoices';
$lang['tally_auto_sync_payments'] = 'Auto Sync Payments';
$lang['tally_auto_sync_customers'] = 'Auto Sync Customers';
$lang['tally_sync_on_create'] = 'Sync on Create';
$lang['tally_sync_on_update'] = 'Sync on Update';

// Actions
$lang['tally_test_connection'] = 'Test Connection';
$lang['tally_sync_all'] = 'Sync All Data';
$lang['tally_sync_invoices'] = 'Sync Invoices';
$lang['tally_sync_payments'] = 'Sync Payments';
$lang['tally_sync_customers'] = 'Sync Customers';
$lang['tally_export_xml'] = 'Export to XML';
$lang['tally_import_from_tally'] = 'Import from Tally';

// Status Messages
$lang['tally_connection_successful'] = 'Tally connection successful!';
$lang['tally_connection_failed'] = 'Failed to connect to Tally server';
$lang['tally_sync_in_progress'] = 'Synchronization in progress...';
$lang['tally_sync_completed'] = 'Synchronization completed successfully';
$lang['tally_sync_failed'] = 'Synchronization failed';
$lang['tally_no_data_to_sync'] = 'No data to synchronize';

// Sync Logs
$lang['tally_sync_log_type'] = 'Sync Type';
$lang['tally_sync_log_record_id'] = 'Record ID';
$lang['tally_sync_log_status'] = 'Status';
$lang['tally_sync_log_date'] = 'Date';
$lang['tally_sync_log_response'] = 'Tally Response';
$lang['tally_sync_log_error'] = 'Error Message';
$lang['tally_sync_log_clear'] = 'Clear Logs';
$lang['tally_sync_log_export'] = 'Export Logs';

// Data Types
$lang['tally_invoice'] = 'Invoice';
$lang['tally_payment'] = 'Payment';
$lang['tally_customer'] = 'Customer';
$lang['tally_product'] = 'Product';
$lang['tally_expense'] = 'Expense';

// Status
$lang['tally_status_success'] = 'Success';
$lang['tally_status_error'] = 'Error';
$lang['tally_status_pending'] = 'Pending';

// Errors
$lang['tally_error_invalid_url'] = 'Invalid Tally server URL';
$lang['tally_error_connection_timeout'] = 'Connection timeout to Tally server';
$lang['tally_error_invalid_company'] = 'Invalid company name';
$lang['tally_error_xml_format'] = 'Invalid XML format';
$lang['tally_error_permission_denied'] = 'Permission denied to access Tally';

// Help Text
$lang['tally_help_server_url'] = 'Enter the complete Tally server URL with port (e.g., http://localhost:9002 or http://192.168.1.100:9000)';
$lang['tally_help_server_port'] = 'Default Tally port is 9000. Change if you have configured a different port';
$lang['tally_help_company_name'] = 'Enter the exact company name as it appears in Tally';
$lang['tally_help_auto_sync'] = 'When enabled, data will be automatically synchronized with Tally upon creation/update';
$lang['tally_version_msg'] = 'Please select your tally version, for best connection';

// Dashboard
$lang['tally_dashboard_title'] = 'Tally Integration Dashboard';
$lang['tally_connection_status'] = 'Connection Status';
$lang['tally_last_sync'] = 'Last Sync';
$lang['tally_total_synced'] = 'Total Records Synced';
$lang['tally_sync_statistics'] = 'Sync Statistics';
$lang['tally_recent_logs'] = 'Recent Sync Logs';

// XML Export
$lang['tally_xml_export_title'] = 'Export Data to Tally XML Format';
$lang['tally_select_data_type'] = 'Select Data Type';
$lang['tally_select_date_range'] = 'Select Date Range';
$lang['tally_xml_generated'] = 'XML file generated successfully';

// Mapping
$lang['tally_field_mapping'] = 'Field Mapping';
$lang['tally_crm_field'] = 'CRM Field';
$lang['tally_tally_field'] = 'Tally Field';
$lang['tally_mapping_save'] = 'Save Mapping';
$lang['tally_mapping_reset'] = 'Reset to Default';

// Bulk Operations
$lang['tally_bulk_sync'] = 'Bulk Synchronization';
$lang['tally_select_records'] = 'Select Records to Sync';
$lang['tally_sync_selected'] = 'Sync Selected';
$lang['tally_sync_all_invoices'] = 'Sync All Invoices';
$lang['tally_sync_all_payments'] = 'Sync All Payments';
$lang['tally_sync_all_customers'] = 'Sync All Customers';