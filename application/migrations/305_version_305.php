<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_305 extends CI_Migration
{
    public function up()
    {
        // Add current_session_token to tblstaff for single session login
        if (!$this->db->field_exists('current_session_token', db_prefix() . 'staff')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'staff` ADD `current_session_token` VARCHAR(128) NULL DEFAULT NULL AFTER `last_login`;');
        }

        // Add current_session_token to tblcontacts for single session login
        if (!$this->db->field_exists('current_session_token', db_prefix() . 'contacts')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'contacts` ADD `current_session_token` VARCHAR(128) NULL DEFAULT NULL AFTER `last_login`;');
        }
    }
}