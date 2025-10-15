<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Tally Integration
Description: Complete Tally ERP integration module for accounting data synchronization
Version: 1.0.0
Requires at least: 2.3.*
Author: Your Company
Author URI: https://yourcompany.com
*/
define('TALLY_INTEGRATION_MODULE_NAME', 'tally_integration');

// Only register helper functions if they exist (when module is properly loaded)
if (function_exists('register_language_files')) {
    register_language_files(TALLY_INTEGRATION_MODULE_NAME, [TALLY_INTEGRATION_MODULE_NAME]);
}

// Register module activation hooks only if functions exist
if (function_exists('register_activation_hook')) {
    register_activation_hook(TALLY_INTEGRATION_MODULE_NAME, 'tally_integration_module_activate');
}
if (function_exists('register_deactivation_hook')) {
    register_deactivation_hook(TALLY_INTEGRATION_MODULE_NAME, 'tally_integration_module_deactivate'); 
}
if (function_exists('register_uninstall_hook')) {
    register_uninstall_hook(TALLY_INTEGRATION_MODULE_NAME, 'tally_integration_module_uninstall');
}

// Hook for admin menu initialization (only if hooks function exists)
if (function_exists('hooks')) {
    hooks()->add_action('admin_init', 'tally_integration_module_init_menu_items');
    hooks()->add_action('admin_init', 'tally_integration_permissions');

    // Hook for adding settings tab
    hooks()->add_action('app_admin_head', 'tally_integration_head_components');

    // Hook for invoice creation to sync with Tally
    hooks()->add_action('after_invoice_added', 'tally_sync_invoice_create');

    // Hook for payment creation to sync with Tally  
    hooks()->add_action('after_payment_added', 'tally_sync_payment_create');

    // Hook for customer creation to sync with Tally
    hooks()->add_action('after_client_added', 'tally_sync_customer_create');
}

/**
 * Initialize Tally Integration module menu items
 * @return null
 */
function tally_integration_module_init_menu_items()
{
    $CI = &get_instance();
    if (is_admin()) {
        // Add main menu item under Utilities
        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug' => 'tally-integration',
            'name' => _l('tally_integration'),
            'href' => admin_url('tally_integration'),
            'position' => 12,
            'icon' => 'fa fa-exchange-alt',
        ]);

        // Add sub-menu items
        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug' => 'tally-settings',
            'name' => _l('tally_settings'),
            'href' => admin_url('tally_integration/settings'),
            'position' => 13,
        ]);

        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug' => 'tally-sync-logs',
            'name' => _l('tally_sync_logs'),
            'href' => admin_url('tally_integration/logs'),
            'position' => 14,
        ]);
    }
}

/**
 * Add Tally Integration permissions
 */
function tally_integration_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('tally_integration') . ')',
        'create' => _l('permission_create') . '(' . _l('tally_integration') . ')',
        'edit' => _l('permission_edit') . '(' . _l('tally_integration') . ')',
        'delete' => _l('permission_delete') . '(' . _l('tally_integration') . ')',
    ];

    register_staff_capabilities('tally_integration', $capabilities, _l('tally_integration'));
}

/**
 * Add Tally Integration head components
 */
function tally_integration_head_components()
{
    $CI = &get_instance();
    if (get_option('tally_integration_enabled') == 1) {
        echo '<script>var tally_auto_sync = true;</script>';
    }
}

/**
 * Sync invoice creation with Tally
 * @param int $invoice_id
 */
function tally_sync_invoice_create($invoice_id)
{
    if (get_option('tally_integration_enabled') == 1 && get_option('tally_auto_sync_invoices') == 1) {
        $CI = &get_instance();
        $CI->load->library('tally_integration/tally_api');
        $CI->tally_api->sync_invoice($invoice_id);
    }
}

/**
 * Sync payment creation with Tally
 * @param int $payment_id
 */
function tally_sync_payment_create($payment_id)
{
    if (get_option('tally_integration_enabled') == 1 && get_option('tally_auto_sync_payments') == 1) {
        $CI = &get_instance();
        $CI->load->library('tally_integration/tally_api');
        $CI->tally_api->sync_payment($payment_id);
    }
}

/**
 * Sync customer creation with Tally
 * @param int $client_id
 */
function tally_sync_customer_create($client_id)
{
    if (get_option('tally_integration_enabled') == 1 && get_option('tally_auto_sync_customers') == 1) {
        $CI = &get_instance();
        $CI->load->library('tally_integration/tally_api');
        $CI->tally_api->sync_customer($client_id);
    }
}

/**
 * Module installation function
 */
function tally_integration_module_install()
{
    $CI = &get_instance();
    
    // Set default options
    add_option('tally_integration_enabled', 0);
    add_option('tally_server_url', '');
    add_option('tally_company_name', '');
    add_option('tally_auto_sync_invoices', 1);
    add_option('tally_auto_sync_payments', 1);
    add_option('tally_auto_sync_customers', 1);
    add_option('tally_sync_on_create', 1);
    add_option('tally_sync_on_update', 0);
    
    // Create sync logs table
    $CI->load->dbforge();
    
    $fields = array(
        'id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'unsigned' => TRUE,
            'auto_increment' => TRUE
        ),
        'sync_type' => array(
            'type' => 'VARCHAR',
            'constraint' => 50,
            'null' => FALSE
        ),
        'record_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE
        ),
        'status' => array(
            'type' => 'ENUM',
            'constraint' => array('success', 'error', 'pending'),
            'default' => 'pending'
        ),
        'tally_response' => array(
            'type' => 'TEXT',
            'null' => TRUE
        ),
        'error_message' => array(
            'type' => 'TEXT',
            'null' => TRUE
        ),
        'created_at' => array(
            'type' => 'DATETIME',
            'null' => FALSE
        ),
        'updated_at' => array(
            'type' => 'DATETIME',
            'null' => TRUE
        )
    );
    
    if (!$CI->db->table_exists(db_prefix() . 'tallysynclogs')) {
        $CI->dbforge->add_field($fields);
        $CI->dbforge->add_key('id', TRUE);
        $CI->dbforge->create_table(db_prefix() . 'tallysynclogs', TRUE);
    }
}

/**
 * Module activation function
 */
function tally_integration_module_activate()
{
    // Run installation if not already done
    tally_integration_module_install();
}

/**
 * Module deactivation function
 */
function tally_integration_module_deactivate()
{
    // Nothing to do on deactivation, just stop the module
    // We don't remove data on deactivation, only on uninstall
}

/**
 * Module uninstallation function
 */
function tally_integration_module_uninstall()
{
    $CI = &get_instance();
    
    // Remove options
    delete_option('tally_integration_enabled');
    delete_option('tally_server_url');
    delete_option('tally_company_name');
    delete_option('tally_auto_sync_invoices');
    delete_option('tally_auto_sync_payments');
    delete_option('tally_auto_sync_customers');
    delete_option('tally_sync_on_create');
    delete_option('tally_sync_on_update');
    
    // Remove import options
    delete_option('tally_auto_import_customers');
    delete_option('tally_auto_import_invoices');
    delete_option('tally_auto_import_payments');
    delete_option('tally_import_frequency');
    delete_option('tally_import_days_back');
    
    // Drop sync logs table
    $CI->load->dbforge();
    if ($CI->db->table_exists(db_prefix() . 'tallysynclogs')) {
        $CI->dbforge->drop_table(db_prefix() . 'tallysynclogs');
    }
}