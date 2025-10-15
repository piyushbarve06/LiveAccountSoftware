<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_100 extends App_Model_migration
{
    public function up()
    {
        // Create tally sync logs table if not exists
        if (!$this->db->table_exists(db_prefix() . 'tallysynclogs')) {
            $this->dbforge->add_field([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ],
                'sync_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => FALSE
                ],
                'record_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ],
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => ['success', 'error', 'pending'],
                    'default' => 'pending'
                ],
                'tally_response' => [
                    'type' => 'TEXT',
                    'null' => TRUE
                ],
                'error_message' => [
                    'type' => 'TEXT',
                    'null' => TRUE
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => FALSE
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => TRUE
                ]
            ]);

            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('sync_type');
            $this->dbforge->add_key('record_id');
            $this->dbforge->add_key('status');
            $this->dbforge->create_table('tallysynclogs');
        }

        // Add initial settings
        if (!get_option('tally_integration_enabled')) {
            add_option('tally_integration_enabled', 0);
            add_option('tally_version_name', '');
            add_option('tally_server_url', '');
            add_option('tally_server_port', '9000');
            add_option('tally_company_name', '');
            add_option('tally_auto_sync_invoices', 1);
            add_option('tally_auto_sync_payments', 1);
            add_option('tally_auto_sync_customers', 1);
            add_option('tally_sync_on_create', 1);
            add_option('tally_sync_on_update', 0);
        }
    }

    public function down()
    {
        // Remove table
        if ($this->db->table_exists(db_prefix() . 'tallysynclogs')) {
            $this->dbforge->drop_table('tallysynclogs');
        }

        // Remove settings
        delete_option('tally_integration_enabled');
        delete_option('tally_version_name');
        delete_option('tally_server_url');
        delete_option('tally_server_port');
        delete_option('tally_company_name');
        delete_option('tally_auto_sync_invoices');
        delete_option('tally_auto_sync_payments');
        delete_option('tally_auto_sync_customers');
        delete_option('tally_sync_on_create');
        delete_option('tally_sync_on_update');
    }
}