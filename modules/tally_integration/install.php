<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Installation script for Tally Integration Module
 */

if (!$CI->db->table_exists(db_prefix() . 'tallysynclogs')) {
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
    
    $CI->dbforge->add_field($fields);
    $CI->dbforge->add_key('id', TRUE);
    $CI->dbforge->add_key('sync_type');
    $CI->dbforge->add_key('record_id');
    $CI->dbforge->add_key('status');
    $CI->dbforge->create_table('tallysynclogs');
}

// Add default options
if (!get_option('tally_integration_enabled')) {
    add_option('tally_integration_enabled', 0);
    add_option('tally_server_url', '');
    add_option('tally_company_name', '');
    add_option('tally_auto_sync_invoices', 1);
    add_option('tally_auto_sync_payments', 1);
    add_option('tally_auto_sync_customers', 1);
    add_option('tally_sync_on_create', 1);
    add_option('tally_sync_on_update', 0);
}

// Add permissions
if (!$CI->db->where('shortname', 'tally_integration')->get(db_prefix() . 'permissions')->row()) {
    $CI->db->insert(db_prefix() . 'permissions', [
        'name' => 'Tally Integration',
        'shortname' => 'tally_integration'
    ]);
}

echo 'Tally Integration module installed successfully!';