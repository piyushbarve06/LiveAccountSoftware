<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tally_integration_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add sync log entry
     */
    public function add_sync_log($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert(db_prefix() . 'tallysynclogs', $data);
        return $this->db->insert_id();
    }

    /**
     * Update sync log
     */
    public function update_sync_log($log_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $log_id);
        return $this->db->update(db_prefix() . 'tallysynclogs', $data);
    }

    /**
     * Get sync log by ID
     */
    public function get_log($log_id)
    {
        $this->db->where('id', $log_id);
        return $this->db->get(db_prefix() . 'tallysynclogs')->row();
    }

    /**
     * Get all sync logs
     */
    public function get_all_logs($limit = null, $offset = 0, $where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }

        $this->db->order_by('created_at', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get(db_prefix() . 'tallysynclogs')->result_array();
    }

    /**
     * Get recent logs
     */
    public function get_recent_logs($limit = 10)
    {
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get(db_prefix() . 'tallysynclogs')->result_array();
    }

    /**
     * Get sync statistics
     */
    public function get_sync_statistics()
    {
        $stats = [];

        // Total synced records
        $this->db->select('COUNT(*) as total');
        $this->db->where('status', 'success');
        $total = $this->db->get(db_prefix() . 'tallysynclogs')->row();
        $stats['total_synced'] = $total->total;

        // Success count by type
        $this->db->select('sync_type, COUNT(*) as count');
        $this->db->where('status', 'success');
        $this->db->group_by('sync_type');
        $success_by_type = $this->db->get(db_prefix() . 'tallysynclogs')->result_array();
        $stats['success_by_type'] = [];
        foreach ($success_by_type as $row) {
            $stats['success_by_type'][$row['sync_type']] = $row['count'];
        }

        // Error count by type
        $this->db->select('sync_type, COUNT(*) as count');
        $this->db->where('status', 'error');
        $this->db->group_by('sync_type');
        $errors_by_type = $this->db->get(db_prefix() . 'tallysynclogs')->result_array();
        $stats['errors_by_type'] = [];
        foreach ($errors_by_type as $row) {
            $stats['errors_by_type'][$row['sync_type']] = $row['count'];
        }

        // Last sync times
        $this->db->select('sync_type, MAX(created_at) as last_sync');
        $this->db->where('status', 'success');
        $this->db->group_by('sync_type');
        $last_sync = $this->db->get(db_prefix() . 'tallysynclogs')->result_array();
        $stats['last_sync'] = [];
        foreach ($last_sync as $row) {
            $stats['last_sync'][$row['sync_type']] = $row['last_sync'];
        }

        // Recent activity (last 24 hours)
        $this->db->select('COUNT(*) as count');
        $this->db->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')));
        $recent = $this->db->get(db_prefix() . 'tallysynclogs')->row();
        $stats['recent_activity'] = $recent->count;

        return $stats;
    }

    /**
     * Get failed syncs
     */
    public function get_failed_syncs($limit = null)
    {
        $this->db->where('status', 'error');
        $this->db->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get(db_prefix() . 'tallysynclogs')->result_array();
    }

    /**
     * Get pending syncs
     */
    public function get_pending_syncs($limit = null)
    {
        $this->db->where('status', 'pending');
        $this->db->order_by('created_at', 'ASC');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get(db_prefix() . 'tallysynclogs')->result_array();
    }

    /**
     * Clear all logs
     */
    public function clear_all_logs()
    {
        return $this->db->empty_table(db_prefix() . 'tallysynclogs');
    }

    /**
     * Clear logs older than specified days
     */
    public function clear_old_logs($days = 30)
    {
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $this->db->where('created_at <', $cutoff_date);
        return $this->db->delete(db_prefix() . 'tallysynclogs');
    }

    /**
     * Check if record was recently synced
     */
    public function is_recently_synced($sync_type, $record_id, $hours = 1)
    {
        $cutoff_time = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        
        $this->db->where('sync_type', $sync_type);
        $this->db->where('record_id', $record_id);
        $this->db->where('status', 'success');
        $this->db->where('created_at >=', $cutoff_time);
        
        $result = $this->db->get(db_prefix() . 'tallysynclogs')->row();
        
        return $result ? true : false;
    }

    /**
     * Get sync history for a specific record
     */
    public function get_record_sync_history($sync_type, $record_id)
    {
        $this->db->where('sync_type', $sync_type);
        $this->db->where('record_id', $record_id);
        $this->db->order_by('created_at', 'DESC');
        
        return $this->db->get(db_prefix() . 'tallysynclogs')->result_array();
    }

    /**
     * Get sync status for multiple records
     */
    public function get_bulk_sync_status($sync_type, $record_ids)
    {
        if (empty($record_ids)) {
            return [];
        }

        $this->db->select('record_id, status, created_at, error_message');
        $this->db->where('sync_type', $sync_type);
        $this->db->where_in('record_id', $record_ids);
        $this->db->group_by('record_id');
        $this->db->order_by('created_at', 'DESC');
        
        return $this->db->get(db_prefix() . 'tallysynclogs')->result_array();
    }

    /**
     * Mark sync as failed
     */
    public function mark_sync_failed($log_id, $error_message)
    {
        return $this->update_sync_log($log_id, [
            'status' => 'error',
            'error_message' => $error_message
        ]);
    }

    /**
     * Mark sync as successful
     */
    public function mark_sync_successful($log_id, $tally_response = null)
    {
        return $this->update_sync_log($log_id, [
            'status' => 'success',
            'tally_response' => $tally_response,
            'error_message' => null
        ]);
    }

    /**
     * Get logs with pagination
     */
    public function get_logs_paginated($limit, $offset, $search = '', $sync_type = '', $status = '')
    {
        // Count total records
        if (!empty($search)) {
            $this->db->like('error_message', $search);
            $this->db->or_like('tally_response', $search);
        }
        
        if (!empty($sync_type)) {
            $this->db->where('sync_type', $sync_type);
        }
        
        if (!empty($status)) {
            $this->db->where('status', $status);
        }
        
        $total_rows = $this->db->count_all_results(db_prefix() . 'tallysynclogs');

        // Get actual records
        if (!empty($search)) {
            $this->db->like('error_message', $search);
            $this->db->or_like('tally_response', $search);
        }
        
        if (!empty($sync_type)) {
            $this->db->where('sync_type', $sync_type);
        }
        
        if (!empty($status)) {
            $this->db->where('status', $status);
        }

        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        $records = $this->db->get(db_prefix() . 'tallysynclogs')->result_array();

        return [
            'total_rows' => $total_rows,
            'records' => $records
        ];
    }
}