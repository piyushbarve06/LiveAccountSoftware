<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Tally Integration Controller
 * @property Tally_integration_model $tally_integration_model
 * @property Tally_api $tally_api
 */
class Tally_integration extends AdminController
{
    private $module_name = 'tally_integration';
    
    public function __construct()
    {
        parent::__construct();
        if (!is_admin()) {
            access_denied('admin');
        }
        
        // Define constant if not already defined
        if (!defined('TALLY_INTEGRATION_MODULE_NAME')) {
            define('TALLY_INTEGRATION_MODULE_NAME', $this->module_name);
        }
        
        $this->load->model($this->module_name . '/tally_integration_model');
        $this->load->library($this->module_name . '/tally_api');
        $this->load->language($this->module_name . '/' . $this->module_name);
    }

    /**
     * Main dashboard view
     */
    public function index()
    {
        if (!has_permission('tally_integration', '', 'view')) {
            access_denied('tally_integration');
        }

        // Get dashboard statistics
        $data['connection_status'] = $this->tally_api->test_connection();
        $data['sync_stats'] = $this->tally_integration_model->get_sync_statistics();
        $data['recent_logs'] = $this->tally_integration_model->get_recent_logs(10);
        $data['settings'] = $this->get_tally_settings();
        
        $data['title'] = _l('tally_dashboard_title');
        $this->load->view($this->module_name . '/dashboard', $data);
    }

    /**
     * Settings page
     */
    public function settings()
    {
        if (!has_permission('tally_integration', '', 'edit')) {
            access_denied('tally_integration');
        }

        if ($this->input->post()) {
            $this->handle_settings_update();
        }

        $data['settings'] = $this->get_tally_settings();
        $data['title'] = _l('tally_integration_settings');
        $this->load->view($this->module_name . '/settings', $data);
    }

    /**
     * Sync logs page
     */
    public function logs()
    {
        if (!has_permission('tally_integration', '', 'view')) {
            access_denied('tally_integration');
        }

        $data['logs'] = $this->tally_integration_model->get_all_logs();
        $data['title'] = _l('tally_sync_logs');
        $this->load->view($this->module_name . '/logs', $data);
    }

    /**
     * Test Tally connection
     */
    public function test_connection()
    {
        if (!has_permission('tally_integration', '', 'view')) {
            access_denied('tally_integration');
        }

        $result = $this->tally_api->test_connection();
        
        if ($result['success']) {
            set_alert('success', _l('tally_connection_successful'));
        } else {
            // Check if we're on a live server
            $server_name = $_SERVER['SERVER_NAME'] ?? '';
            if (strpos($server_name, 'localhost') === false && strpos($server_name, '127.0.0.1') === false) {
                set_alert('warning', 'Live Server Detected: Direct Tally connection not possible. Use file-based import instead.');
                set_alert('info', 'Upload CSV/XML files from your local Tally installation using the import tools.');
            } else {
                set_alert('danger', _l('tally_connection_failed') . ': ' . $result['message']);
            }
        }

        redirect(admin_url('tally_integration/settings'));
    }

    /**
     * Manual sync all data
     */
    public function sync_all()
    {
        if (!has_permission('tally_integration', '', 'create')) {
            access_denied('tally_integration');
        }

        set_alert('info', _l('tally_sync_in_progress'));

        // Sync in background
        $this->sync_customers();
        $this->sync_invoices();
        $this->sync_payments();

        set_alert('success', _l('tally_sync_completed'));
        redirect(admin_url('tally_integration'));
    }

    /**
     * Sync customers
     */
    public function sync_customers($redirect = true)
    {
        if (!has_permission('tally_integration', '', 'create')) {
            access_denied('tally_integration');
        }

        $customers = $this->clients_model->get();
        $synced = 0;
        $errors = 0;


        foreach ($customers as $customer) {
            $result = $this->tally_api->sync_customer($customer['userid']);
            if ($result['success']) {
                $synced++;
            } else {
                $errors++;
            }
        }

        if ($redirect) {
            if ($errors == 0) {
                set_alert('success', "Successfully synced {$synced} customers");
            } else {
                set_alert('warning', "Synced {$synced} customers with {$errors} errors");
            }
            redirect(admin_url('tally_integration'));
        }

        return ['synced' => $synced, 'errors' => $errors];
    }

    /**
     * Sync invoices
     */
    public function sync_invoices($redirect = true)
    {
        if (!has_permission('tally_integration', '', 'create')) {
            access_denied('tally_integration');
        }

        $this->load->model('invoices_model');
        $invoices = $this->invoices_model->get();
        $synced = 0;
        $errors = 0;

        foreach ($invoices as $invoice) {
            $result = $this->tally_api->sync_invoice($invoice['id']);
            if ($result['success']) {
                $synced++;
            } else {
                $errors++;
            }
        }

        if ($redirect) {
            if ($errors == 0) {
                set_alert('success', "Successfully synced {$synced} invoices");
            } else {
                set_alert('warning', "Synced {$synced} invoices with {$errors} errors");
            }
            redirect(admin_url('tally_integration'));
        }

        return ['synced' => $synced, 'errors' => $errors];
    }

    /**
     * Sync payments
     */
    public function sync_payments($redirect = true)
    {
        if (!has_permission('tally_integration', '', 'create')) {
            access_denied('tally_integration');
        }

        $this->load->model('payments_model');
        $payments = $this->payments_model->get_all_payments();
        $synced = 0;
        $errors = 0;

        foreach ($payments as $payment) {
            $result = $this->tally_api->sync_payment($payment['paymentid']);
            if ($result['success']) {
                $synced++;
            } else {
                $errors++;
            }
        }

        if ($redirect) {
            if ($errors == 0) {
                set_alert('success', "Successfully synced {$synced} payments");
            } else {
                set_alert('warning', "Synced {$synced} payments with {$errors} errors");
            }
            redirect(admin_url('tally_integration'));
        }

        return ['synced' => $synced, 'errors' => $errors];
    }

    /**
     * Export data to XML
     */
    public function export_xml()
    {
        if (!has_permission('tally_integration', '', 'view')) {
            access_denied('tally_integration');
        }

        if ($this->input->post()) {
            $type = $this->input->post('export_type');
            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');

            $xml_data = $this->tally_api->export_to_xml($type, $start_date, $end_date);

            if ($xml_data) {
                $filename = $type . '_' . date('Y-m-d_H-i-s') . '.xml';
                
                header('Content-Type: application/xml');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                echo $xml_data;
                exit;
            } else {
                set_alert('danger', _l('tally_no_data_to_sync'));
            }
        }

        $data['title'] = _l('tally_xml_export_title');
        $this->load->view($this->module_name . '/export_xml', $data);
    }

    /**
     * Clear sync logs
     */
    public function clear_logs()
    {
        if (!has_permission('tally_integration', '', 'delete')) {
            access_denied('tally_integration');
        }

        $this->tally_integration_model->clear_all_logs();
        set_alert('success', 'Sync logs cleared successfully');
        redirect(admin_url('tally_integration/logs'));
    }

    /**
     * File-based import for live servers
     */
    public function file_import()
    {
        if (!has_permission('tally_integration', '', 'create')) {
            access_denied('tally_integration');
        }

        if ($this->input->post() && isset($_FILES['tally_file'])) {
            $upload_result = $this->handle_file_upload();
            
            if ($upload_result['success']) {
                $import_result = $this->process_tally_file($upload_result['file_path']);
                
                if ($import_result['success']) {
                    set_alert('success', 'File imported successfully: ' . $import_result['message']);
                } else {
                    set_alert('danger', 'Import failed: ' . $import_result['message']);
                }
            } else {
                set_alert('danger', 'File upload failed: ' . $upload_result['message']);
            }
            
            redirect(admin_url('tally_integration/file_import'));
        }

        $data['title'] = 'Tally File Import (Live Server)';
        $data['server_info'] = [
            'is_live' => (strpos($_SERVER['SERVER_NAME'] ?? '', 'localhost') === false),
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown'
        ];
        
        $this->load->view($this->module_name . '/file_import', $data);
    }

    /**
     * Handle file upload
     */
    private function handle_file_upload()
    {
        $config['upload_path'] = FCPATH . 'uploads/tally_imports/';
        $config['allowed_types'] = 'csv|xml|txt';
        $config['max_size'] = 10240; // 10MB
        $config['encrypt_name'] = true;

        // Create directory if it doesn't exist
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('tally_file')) {
            $upload_data = $this->upload->data();
            return [
                'success' => true,
                'file_path' => $upload_data['full_path'],
                'file_name' => $upload_data['file_name']
            ];
        } else {
            return [
                'success' => false,
                'message' => $this->upload->display_errors()
            ];
        }
    }

    /**
     * Process uploaded Tally file
     */
    private function process_tally_file($file_path)
    {
        $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);
        
        try {
            switch (strtolower($file_extension)) {
                case 'csv':
                    return $this->process_csv_file($file_path);
                case 'xml':
                    return $this->process_xml_file($file_path);
                default:
                    return ['success' => false, 'message' => 'Unsupported file type'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Processing error: ' . $e->getMessage()];
        }
    }

    /**
     * Process CSV file
     */
    private function process_csv_file($file_path)
    {
        $imported = 0;
        $errors = 0;
        
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            $header = fgetcsv($handle); // Skip header row
            
            while (($data = fgetcsv($handle)) !== FALSE) {
                try {
                    // Assuming CSV format: Name, Email, Phone, Address, Balance
                    $customer_data = [
                        'company' => $data[0] ?? '',
                        'email' => $data[1] ?? '',
                        'phonenumber' => $data[2] ?? '',
                        'address' => $data[3] ?? '',
                        'balance' => floatval($data[4] ?? 0)
                    ];
                    
                    if (!empty($customer_data['company'])) {
                        $this->clients_model->add($customer_data);
                        $imported++;
                    }
                } catch (Exception $e) {
                    $errors++;
                }
            }
            fclose($handle);
        }
        
        return [
            'success' => true,
            'message' => "Imported $imported customers with $errors errors"
        ];
    }

    /**
     * Process XML file
     */
    private function process_xml_file($file_path)
    {
        $xml_content = file_get_contents($file_path);
        if (!$xml_content) {
            return ['success' => false, 'message' => 'Could not read XML file'];
        }
        
        // Process XML similar to your existing tally_api methods
        $result = $this->tally_api->process_xml_data($xml_content);
        
        return $result ?: ['success' => false, 'message' => 'XML processing failed'];
    }

    /**
     * Import all data from Tally
     */
    public function import_all()
    {
        if (!has_permission('tally_integration', '', 'create')) {
            access_denied('tally_integration');
        }

        set_alert('info', 'Import from Tally in progress...');

        // Import in sequence
        $this->import_customers(false);
        $this->import_invoices(false);
        $this->import_payments(false);

        set_alert('success', 'Import from Tally completed successfully');
        redirect(admin_url('tally_integration'));
    }

    /**
     * Import customers from Tally
     */
    public function import_customers($redirect = true)
    {
        if (!has_permission('tally_integration', '', 'create')) {
            access_denied('tally_integration');
        }

        $result = $this->tally_api->import_customers_from_tally();

        if ($redirect) {
            if ($result['success']) {
                set_alert('success', $result['message']);
                if (!empty($result['errors'])) {
                    set_alert('warning', 'Some errors occurred during import. Check logs for details.');
                }
            } else {
                set_alert('danger', 'Import failed: ' . $result['message']);
            }
            redirect(admin_url('tally_integration'));
        }

        return $result;
    }

    /**
     * Import invoices from Tally
     */
    public function import_invoices($redirect = true)
    {
        if (!has_permission('tally_integration', '', 'create')) {
            access_denied('tally_integration');
        }

        // Get date range from POST parameters or use defaults
        $date_from = $this->input->post('date_from') ?: date('d-M-Y', strtotime('-30 days'));
        $date_to = $this->input->post('date_to') ?: date('d-M-Y');

        $result = $this->tally_api->import_invoices_from_tally($date_from, $date_to);

        if ($redirect) {
            if ($result['success']) {
                set_alert('success', $result['message']);
                if (!empty($result['errors'])) {
                    set_alert('warning', 'Some errors occurred during import. Check logs for details.');
                }
            } else {
                set_alert('danger', 'Import failed: ' . $result['message']);
            }
            redirect(admin_url('tally_integration'));
        }

        return $result;
    }

    /**
     * Import payments from Tally
     */
    public function import_payments($redirect = true)
    {
        if (!has_permission('tally_integration', '', 'create')) {
            access_denied('tally_integration');
        }

        // Get date range from POST parameters or use defaults
        $date_from = $this->input->post('date_from') ?: date('Y-m-d', strtotime('-30 days'));
        $date_to = $this->input->post('date_to') ?: date('Y-m-d');

        $result = $this->tally_api->import_payments_from_tally($date_from, $date_to);

        if ($redirect) {
            if ($result['success']) {
                set_alert('success', $result['message']);
                if (!empty($result['errors'])) {
                    set_alert('warning', 'Some errors occurred during import. Check logs for details.');
                }
            } else {
                set_alert('danger', 'Import failed: ' . $result['message']);
            }
            redirect(admin_url('tally_integration'));
        }

        return $result;
    }

    /**
     * Debug customer import issues
     */
    public function debug_customer_import()
    {
        if (!has_permission('tally_integration', '', 'view')) {
            access_denied('tally_integration');
        }

        $debug_result = $this->tally_api->debug_tally_ledgers();
        
        if ($debug_result['success']) {
            echo "<div style='font-family: Arial, sans-serif; padding: 20px;'>";
            echo "<h2>🔍 Tally Customer Import Debug Report</h2>";
            
            // Show configuration details
            if (isset($debug_result['debug_info'])) {
                $debug_info = $debug_result['debug_info'];
                echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h3>⚙️ Configuration Details:</h3>";
                echo "<strong>Server URL:</strong> " . htmlspecialchars($debug_info['server_url'] ?? 'Not set') . "<br>";

                echo "<strong>Company Name:</strong> " . htmlspecialchars($debug_info['company_name'] ?? 'Not set') . "<br>";
                echo "</div>";
            }
            
            echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h3>✅ Connection & Response Summary:</h3>";
            echo "<strong>Total Ledgers Found:</strong> " . $debug_result['total_ledgers'] . "<br>";
            echo "<strong>Potential Customers Identified:</strong> " . $debug_result['potential_customers'] . "<br>";
            echo "<strong>XML Response Size:</strong> " . number_format($debug_result['response_size']) . " bytes<br>";
            echo "</div>";
            
            // Show raw request and response for troubleshooting
            if (isset($debug_result['debug_info'])) {
                $debug_info = $debug_result['debug_info'];
                
                echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h3>🔧 Technical Details (for troubleshooting):</h3>";
                
                echo "<h4>XML Request being sent to Tally:</h4>";
                echo "<textarea readonly style='width: 100%; height: 150px; font-family: monospace; background: white;'>";
                echo htmlspecialchars($debug_info['request_xml'] ?? 'No request captured');
                echo "</textarea>";
                
                echo "<h4>Raw XML Response from Tally:</h4>";
                echo "<textarea readonly style='width: 100%; height: 150px; font-family: monospace; background: white;'>";
                echo htmlspecialchars($debug_info['response_raw'] ?? 'No response captured');
                echo "</textarea>";
                echo "</div>";
            }
            
            if ($debug_result['potential_customers'] > 0) {
                echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h3>🎉 Great News: Found " . $debug_result['potential_customers'] . " Potential Customers!</h3>";
                echo "<p>The updated import logic should now work properly.</p>";
                
                echo "<h4>Sample Customers Found:</h4>";
                foreach ($debug_result['sample_customers'] as $customer) {
                    echo "<div style='background: white; padding: 8px; margin: 5px 0; border-radius: 3px; border-left: 4px solid #28a745;'>";
                    echo "<strong>" . htmlspecialchars($customer['name']) . "</strong><br>";
                    echo "<small>Group: " . htmlspecialchars($customer['parent']) . "</small>";
                    if (!empty($customer['email'])) echo "<br><small>Email: " . htmlspecialchars($customer['email']) . "</small>";
                    if (!empty($customer['phone'])) echo "<br><small>Phone: " . htmlspecialchars($customer['phone']) . "</small>";
                    echo "</div>";
                }
                echo "</div>";
            }
            
            echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h3>📊 All Ledger Groups (Top 20):</h3>";
            echo "<div style='max-height: 400px; overflow-y: auto;'>";
            
            $count = 0;
            foreach ($debug_result['groups'] as $group => $ledgers) {
                if (++$count > 20) break;
                
                $ledger_count = count($ledgers);
                $is_customer_group = (
                    stripos($group, 'debtor') !== false ||
                    stripos($group, 'customer') !== false ||
                    stripos($group, 'client') !== false
                );
                
                $style = $is_customer_group ? 'background: #fff3cd; border-left: 4px solid #ffc107;' : 'background: white; border-left: 4px solid #dee2e6;';
                $icon = $is_customer_group ? '👥' : '📁';
                
                echo "<div style='{$style} padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                echo "<strong>{$icon} " . htmlspecialchars($group) . "</strong> ({$ledger_count} ledgers)";
                if ($is_customer_group) echo " <span style='color: #856404;'>← CUSTOMER GROUP</span>";
                echo "</div>";
            }
            
            echo "</div>";
            echo "</div>";
            
            if ($debug_result['potential_customers'] > 0) {
                echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h3>🚀 Next Steps:</h3>";
                echo "<ol>";
                echo "<li><strong>Try importing customers now</strong> - the updated logic should find your customers</li>";
                echo "<li>Go to <a href='" . admin_url('tally_integration') . "'>Tally Integration Dashboard</a></li>";
                echo "<li>Click 'Import Customers' to test the import</li>";
                echo "</ol>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h3>⚠️ Still No Customers Found</h3>";
                echo "<p>This might indicate:</p>";
                echo "<ul>";
                echo "<li>Your customers are in Tally under different group names than expected</li>";
                echo "<li>The customers don't have contact information (email/phone) in Tally</li>";
                echo "<li>The Tally company selected doesn't have customer data</li>";
                echo "</ul>";
                echo "<p><strong>Recommendation:</strong> Check the ledger groups above and look for where your customers might be located.</p>";
                echo "</div>";
            }
            
            echo "</div>";
        } else {
            echo "<div style='font-family: Arial, sans-serif; padding: 20px;'>";
            echo "<h2>❌ Debug Failed</h2>";
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
            echo "<strong>Error:</strong> " . htmlspecialchars($debug_result['message']);
            echo "</div>";
            
            // Show debug info even when failed
            if (isset($debug_result['debug_info']) && !empty($debug_result['debug_info'])) {
                $debug_info = $debug_result['debug_info'];
                
                echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h3>⚙️ Configuration Details:</h3>";
                echo "<strong>Server URL:</strong> " . htmlspecialchars($debug_info['server_url'] ?? 'Not set') . "<br>";

                echo "<strong>Company Name:</strong> " . htmlspecialchars($debug_info['company_name'] ?? 'Not set') . "<br>";
                echo "<strong>Response Size:</strong> " . ($debug_info['response_size'] ?? 0) . " bytes<br>";
                echo "</div>";
                
                echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h3>🔧 Technical Details:</h3>";
                
                echo "<h4>XML Request being sent to Tally:</h4>";
                echo "<textarea readonly style='width: 100%; height: 150px; font-family: monospace; background: white;'>";
                echo htmlspecialchars($debug_info['request_xml'] ?? 'No request captured');
                echo "</textarea>";
                
                echo "<h4>Raw XML Response from Tally:</h4>";
                echo "<textarea readonly style='width: 100%; height: 150px; font-family: monospace; background: white;'>";
                echo htmlspecialchars($debug_info['response_raw'] ?? 'No response captured');
                echo "</textarea>";
                
                // Analysis of the issue
                echo "<h4>🔍 Problem Analysis:</h4>";
                $response_size = $debug_info['response_size'] ?? 0;
                if ($response_size < 200) {
                    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 3px; margin: 5px 0;'>";
                    echo "⚠️ <strong>Very small response ({$response_size} bytes)</strong> - This suggests:";
                    echo "<ul>";
                    echo "<li>Tally is not returning ledger data</li>";
                    echo "<li>Company name might be incorrect</li>";
                    echo "<li>Tally might not be running or accessible</li>";
                    echo "<li>XML request format might be incompatible</li>";
                    echo "</ul>";
                    echo "</div>";
                }
                echo "</div>";
            }
            
            echo "</div>";
        }
    }

    /**
     * Import data page with date range selection
     */
    public function import_data()
    {
        if (!has_permission('tally_integration', '', 'view')) {
            access_denied('tally_integration');
        }

        if ($this->input->post()) {
            $import_type = $this->input->post('import_type');
            $date_from = $this->input->post('date_from');
            $date_to = $this->input->post('date_to');

            switch ($import_type) {
                case 'customers':
                    $this->import_customers();
                    break;
                case 'invoices':
                    $this->import_invoices();
                    break;
                case 'payments':
                    $this->import_payments();
                    break;
                case 'all':
                    $this->import_all();
                    break;
            }
            return;
        }

        $data['title'] = 'Import Data from Tally';
        $data['settings'] = $this->get_tally_settings();
        $this->load->view($this->module_name . '/import_data', $data);
    }

    /**
     * AJAX: Get import preview
     */
    public function preview_import()
    {
        if (!has_permission('tally_integration', '', 'view')) {
            ajax_access_denied();
        }

        $type = $this->input->post('type');
        $date_from = $this->input->post('date_from');
        $date_to = $this->input->post('date_to');

        try {
            $preview_data = [];

            switch ($type) {
                case 'customers':
                    // Get a preview of customers that would be imported
                    $xml_request = $this->tally_api->build_ledger_list_request();
                    $response = $this->tally_api->send_tally_request($xml_request);
                    $customers = $this->tally_api->parse_tally_ledgers($response);
                    $preview_data = array_slice($customers, 0, 10); // First 10 for preview
                    break;

                case 'invoices':
                    // Get a preview of invoices that would be imported
                    $xml_request = $this->tally_api->build_sales_voucher_list_request($date_from, $date_to);
                    $response = $this->tally_api->send_tally_request($xml_request);
                    $invoices = $this->tally_api->parse_tally_sales_vouchers($response);
                    $preview_data = array_slice($invoices, 0, 10); // First 10 for preview
                    break;

                case 'payments':
                    // Get a preview of payments that would be imported
                    $xml_request = $this->tally_api->build_receipt_voucher_list_request($date_from, $date_to);
                    $response = $this->tally_api->send_tally_request($xml_request);
                    $payments = $this->tally_api->parse_tally_receipt_vouchers($response);
                    $preview_data = array_slice($payments, 0, 10); // First 10 for preview
                    break;
            }

            echo json_encode([
                'success' => true,
                'data' => $preview_data,
                'total_count' => count($preview_data)
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX: Get single log details
     */
    public function get_log_details($log_id)
    {
        if (!has_permission('tally_integration', '', 'view')) {
            ajax_access_denied();
        }

        $log = $this->tally_integration_model->get_log($log_id);
        
        if ($log) {
            echo json_encode([
                'success' => true,
                'data' => $log
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Log not found'
            ]);
        }
    }

    /**
     * AJAX: Retry failed sync
     */
    public function retry_sync()
    {
        if (!has_permission('tally_integration', '', 'create')) {
            ajax_access_denied();
        }

        $log_id = $this->input->post('log_id');
        $log = $this->tally_integration_model->get_log($log_id);

        if (!$log) {
            echo json_encode(['success' => false, 'message' => 'Log not found']);
            return;
        }

        // Retry the sync based on type
        $result = false;
        switch ($log->sync_type) {
            case 'invoice':
                $result = $this->tally_api->sync_invoice($log->record_id);
                break;
            case 'payment':
                $result = $this->tally_api->sync_payment($log->record_id);
                break;
            case 'customer':
                $result = $this->tally_api->sync_customer($log->record_id);
                break;
        }

        echo json_encode($result);
    }

    /**
     * Handle settings form submission
     */
    private function handle_settings_update()
    {
        // Update export settings (CRM to Tally)
        update_option('tally_integration_enabled', $this->input->post('tally_integration_enabled') ? 1 : 0);
        update_option('tally_version_name', $this->input->post('tally_version_name'));
        update_option('tally_server_url', $this->input->post('tally_server_url'));

        update_option('tally_company_name', $this->input->post('tally_company_name'));
        update_option('tally_auto_sync_invoices', $this->input->post('tally_auto_sync_invoices') ? 1 : 0);
        update_option('tally_auto_sync_payments', $this->input->post('tally_auto_sync_payments') ? 1 : 0);
        update_option('tally_auto_sync_customers', $this->input->post('tally_auto_sync_customers') ? 1 : 0);
        update_option('tally_sync_on_create', $this->input->post('tally_sync_on_create') ? 1 : 0);
        update_option('tally_sync_on_update', $this->input->post('tally_sync_on_update') ? 1 : 0);

        // Update import settings (Tally to CRM)
        update_option('tally_auto_import_customers', $this->input->post('tally_auto_import_customers') ? 1 : 0);
        update_option('tally_auto_import_invoices', $this->input->post('tally_auto_import_invoices') ? 1 : 0);
        update_option('tally_auto_import_payments', $this->input->post('tally_auto_import_payments') ? 1 : 0);
        update_option('tally_import_frequency', $this->input->post('tally_import_frequency') ?: '60');
        update_option('tally_import_days_back', $this->input->post('tally_import_days_back') ?: '7');

        set_alert('success', _l('settings_updated'));
        redirect(admin_url('tally_integration/settings'));
    }

    /**
     * Get all Tally settings
     */
    private function get_tally_settings()
    {
        return [
            'enabled' => get_option('tally_integration_enabled'),
            'version_name' => get_option('tally_version_name'),
            'server_url' => get_option('tally_server_url'),
            'company_name' => get_option('tally_company_name'),
            'auto_sync_invoices' => get_option('tally_auto_sync_invoices'),
            'auto_sync_payments' => get_option('tally_auto_sync_payments'),
            'auto_sync_customers' => get_option('tally_auto_sync_customers'),
            'sync_on_create' => get_option('tally_sync_on_create'),
            'sync_on_update' => get_option('tally_sync_on_update'),
            // Import settings
            'auto_import_customers' => get_option('tally_auto_import_customers'),
            'auto_import_invoices' => get_option('tally_auto_import_invoices'),
            'auto_import_payments' => get_option('tally_auto_import_payments'),
            'import_frequency' => get_option('tally_import_frequency') ?: '60',
            'import_days_back' => get_option('tally_import_days_back') ?: '7',
        ];
    }
}