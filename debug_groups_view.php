<?php
// Simple debug script to test the Product Master controller directly
defined('BASEPATH') or define('BASEPATH', dirname(__FILE__));

// Include CodeIgniter bootstrap
require_once 'index.php';

// Get CI instance
$CI =& get_instance();

// Load the product master model
$CI->load->model('modules/product_master/models/product_master_model', 'product_master_model');
$CI->load->helper('modules/product_master/helpers/product_master_helper');

// Test getting groups data
echo "<h2>Debug: Product Master Groups Data</h2>";

try {
    // Get groups data same way as controller
    $groups = $CI->product_master_model->get_groups();
    
    echo "<h3>Groups Data Retrieved:</h3>";
    echo "<pre>";
    print_r($groups);
    echo "</pre>";
    
    echo "<h3>Count: " . count($groups) . " groups</h3>";
    
    if (!empty($groups)) {
        echo "<h3>Table Preview:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Group Code</th>";
        echo "<th>Group Name</th>";
        echo "<th>Description</th>";
        echo "<th>Sort Order</th>";
        echo "<th>Status</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($groups as $group) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($group['id'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($group['group_code'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($group['group_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($group['description'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($group['sort_order'] ?? 'N/A') . "</td>";
            echo "<td>";
            if (function_exists('render_pm_status_badge')) {
                echo render_pm_status_badge($group['status'] ?? 0);
            } else {
                echo htmlspecialchars($group['status'] ?? 'N/A');
            }
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p style='color: red;'>No groups found!</p>";
        
        // Check database connection and table
        echo "<h3>Database Diagnostics:</h3>";
        
        // Check if table exists
        $table_check = $CI->db->query("SHOW TABLES LIKE '" . db_prefix() . "pm_groups'");
        if ($table_check->num_rows() > 0) {
            echo "<p style='color: green;'>✓ Table '" . db_prefix() . "pm_groups' exists</p>";
            
            // Check table structure
            $structure = $CI->db->query("DESCRIBE " . db_prefix() . "pm_groups")->result_array();
            echo "<h4>Table Structure:</h4>";
            echo "<pre>";
            print_r($structure);
            echo "</pre>";
            
            // Check if there's any data at all
            $count = $CI->db->count_all(db_prefix() . 'pm_groups');
            echo "<p>Total records in table: " . $count . "</p>";
            
            if ($count > 0) {
                // Get raw data
                $raw_data = $CI->db->get(db_prefix() . 'pm_groups')->result_array();
                echo "<h4>Raw Data:</h4>";
                echo "<pre>";
                print_r($raw_data);
                echo "</pre>";
            }
        } else {
            echo "<p style='color: red;'>✗ Table '" . db_prefix() . "pm_groups' does not exist!</p>";
            echo "<p>Run the module installation to create tables.</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>";
    print_r($e->getTrace());
    echo "</pre>";
}
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; font-weight: bold; }
.label { padding: 2px 6px; border-radius: 3px; color: white; font-size: 11px; }
.label-success { background-color: #5cb85c; }
.label-danger { background-color: #d9534f; }
</style>