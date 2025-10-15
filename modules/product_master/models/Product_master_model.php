<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Product Master Model
 */
class Product_master_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ==================== GROUPS ====================
    
    /**
     * Check if group code already exists
     */
    public function group_code_exists($group_code, $exclude_id = null)
    {
        $this->db->where('group_code', strtoupper($group_code));
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get(db_prefix() . 'pm_groups')->num_rows() > 0;
    }

    /**
     * Add new group
     */
    public function add_group($data)
    {
        $data['group_code'] = strtoupper($data['group_code']);
        
        // Check for duplicate group code
        if ($this->group_code_exists($data['group_code'])) {
            return 'duplicate_code';
        }
        
        if (isset($data['id'])) {
            unset($data['id']);
        }
        
        $this->db->insert(db_prefix() . 'pm_groups', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('New Product Group Added [' . $data['group_name'] . ']');
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Update group
     */
    public function update_group($data, $id)
    {
        $data['group_code'] = strtoupper($data['group_code']);
        
        // Check for duplicate group code (excluding current record)
        if ($this->group_code_exists($data['group_code'], $id)) {
            return 'duplicate_code';
        }
        
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'pm_groups', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Product Group Updated [' . $data['group_name'] . ']');
            return true;
        }
        
        return false;
    }

    /**
     * Get groups
     */
    public function get_groups($where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('group_name', 'ASC');
        
        return $this->db->get(db_prefix() . 'pm_groups')->result_array();
    }

    /**
     * Get single group
     */
    public function get_group($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'pm_groups')->row_array();
    }

    /**
     * Delete group
     */
    public function delete_group($id)
    {
        $group = $this->get_group($id);
        
        if (!$group) {
            return false;
        }
        
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'pm_groups');
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Product Group Deleted [' . $group['group_name'] . ']');
            return true;
        }
        
        return false;
    }

    // ==================== SUBGROUPS ====================
    
    /**
     * Check if subgroup code already exists
     */
    public function subgroup_code_exists($subgroup_code, $exclude_id = null)
    {
        $this->db->where('subgroup_code', strtoupper($subgroup_code));
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get(db_prefix() . 'pm_subgroups')->num_rows() > 0;
    }
    
    /**
     * Add new subgroup
     */
    public function add_subgroup($data)
    {
        $data['subgroup_code'] = strtoupper($data['subgroup_code']);
        
        // Check for duplicate subgroup code
        if ($this->subgroup_code_exists($data['subgroup_code'])) {
            return 'duplicate_code';
        }
        
        if (isset($data['id'])) {
            unset($data['id']);
        }
        
        $this->db->insert(db_prefix() . 'pm_subgroups', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('New Product Subgroup Added [' . $data['subgroup_name'] . ']');
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Update subgroup
     */
    public function update_subgroup($data, $id)
    {
        $data['subgroup_code'] = strtoupper($data['subgroup_code']);
        
        // Check for duplicate subgroup code (excluding current record)
        if ($this->subgroup_code_exists($data['subgroup_code'], $id)) {
            return 'duplicate_code';
        }
        
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'pm_subgroups', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Product Subgroup Updated [' . $data['subgroup_name'] . ']');
            return true;
        }
        
        return false;
    }

    /**
     * Get subgroups
     */
    public function get_subgroups($where = [])
    {
        $this->db->select('s.*, g.group_name');
        $this->db->from(db_prefix() . 'pm_subgroups s');
        $this->db->join(db_prefix() . 'pm_groups g', 'g.id = s.group_id', 'left');
        
        if (!empty($where)) {
            // Process where conditions to add table aliases for ambiguous columns
            foreach ($where as $key => $value) {
                if ($key === 'status') {
                    // Qualify status with subgroups table alias
                    $this->db->where('s.status', $value);
                } else if ($key === 'group_id') {
                    // Qualify group_id with subgroups table alias
                    $this->db->where('s.group_id', $value);
                } else if (strpos($key, '.') === false) {
                    // If no table alias specified, assume it's for subgroups table
                    $this->db->where('s.' . $key, $value);
                } else {
                    // Already has table alias
                    $this->db->where($key, $value);
                }
            }
        }
        
        $this->db->order_by('s.sort_order', 'ASC');
        $this->db->order_by('s.subgroup_name', 'ASC');
        
        return $this->db->get()->result_array();
    }

    /**
     * Get single subgroup
     */
    public function get_subgroup($id)
    {
        $this->db->select('s.*, g.group_name');
        $this->db->from(db_prefix() . 'pm_subgroups s');
        $this->db->join(db_prefix() . 'pm_groups g', 'g.id = s.group_id', 'left');
        $this->db->where('s.id', $id);
        
        return $this->db->get()->row_array();
    }

    /**
     * Delete subgroup
     */
    public function delete_subgroup($id)
    {
        $subgroup = $this->get_subgroup($id);
        
        if (!$subgroup) {
            return false;
        }
        
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'pm_subgroups');
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Product Subgroup Deleted [' . $subgroup['subgroup_name'] . ']');
            return true;
        }
        
        return false;
    }

    // ==================== CATEGORIES ====================
    
    /**
     * Add new category
     */
    public function add_category($data)
    {
        $data['category_code'] = strtoupper($data['category_code']);
        
        if (isset($data['id'])) {
            unset($data['id']);
        }
        
        $this->db->insert(db_prefix() . 'pm_categories', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('New Product Category Added [' . $data['category_name'] . ']');
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Update category
     */
    public function update_category($data, $id)
    {
        $data['category_code'] = strtoupper($data['category_code']);
        
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'pm_categories', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Product Category Updated [' . $data['category_name'] . ']');
            return true;
        }
        
        return false;
    }

    /**
     * Get categories
     */
    public function get_categories($where = [])
    {
        $this->db->select('c.*, s.subgroup_name, g.group_name');
        $this->db->from(db_prefix() . 'pm_categories c');
        $this->db->join(db_prefix() . 'pm_subgroups s', 's.id = c.subgroup_id', 'left');
        $this->db->join(db_prefix() . 'pm_groups g', 'g.id = s.group_id', 'left');
        
        if (!empty($where)) {
            // Process where conditions to add table aliases for ambiguous columns
            foreach ($where as $key => $value) {
                if ($key === 'status') {
                    // Qualify status with categories table alias
                    $this->db->where('c.status', $value);
                } else if ($key === 'subgroup_id') {
                    // Qualify subgroup_id with categories table alias
                    $this->db->where('c.subgroup_id', $value);
                } else if ($key === 'group_id') {
                    // For group_id, we can use either subgroups or groups table - using subgroups
                    $this->db->where('s.group_id', $value);
                } else if (in_array($key, ['id', 'sort_order', 'created_date', 'updated_date'])) {
                    // Common columns that might be ambiguous - assume categories table
                    $this->db->where('c.' . $key, $value);
                } else if (strpos($key, '.') === false) {
                    // If no table alias specified, assume it's for categories table
                    $this->db->where('c.' . $key, $value);
                } else {
                    // Already has table alias
                    $this->db->where($key, $value);
                }
            }
        }
        
        $this->db->order_by('c.sort_order', 'ASC');
        $this->db->order_by('c.category_name', 'ASC');
        
        return $this->db->get()->result_array();
    }

    /**
     * Get single category
     */
    public function get_category($id)
    {
        $this->db->select('c.*, s.subgroup_name, g.group_name');
        $this->db->from(db_prefix() . 'pm_categories c');
        $this->db->join(db_prefix() . 'pm_subgroups s', 's.id = c.subgroup_id', 'left');
        $this->db->join(db_prefix() . 'pm_groups g', 'g.id = s.group_id', 'left');
        $this->db->where('c.id', $id);
        
        return $this->db->get()->row_array();
    }

    /**
     * Delete category
     */
    public function delete_category($id)
    {
        $category = $this->get_category($id);
        
        if (!$category) {
            return false;
        }
        
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'pm_categories');
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Product Category Deleted [' . $category['category_name'] . ']');
            return true;
        }
        
        return false;
    }

    // ==================== SUBCATEGORIES ====================
    
    /**
     * Add new subcategory
     */
    public function add_subcategory($data)
    {
        $data['subcategory_code'] = strtoupper($data['subcategory_code']);
        
        if (isset($data['id'])) {
            unset($data['id']);
        }
        
        $this->db->insert(db_prefix() . 'pm_subcategories', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('New Product Subcategory Added [' . $data['subcategory_name'] . ']');
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Update subcategory
     */
    public function update_subcategory($data, $id)
    {
        $data['subcategory_code'] = strtoupper($data['subcategory_code']);
        
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'pm_subcategories', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Product Subcategory Updated [' . $data['subcategory_name'] . ']');
            return true;
        }
        
        return false;
    }

    /**
     * Get subcategories
     */
    public function get_subcategories($where = [])
    {
        $this->db->select('sc.*, c.category_name, s.subgroup_name, g.group_name');
        $this->db->from(db_prefix() . 'pm_subcategories sc');
        $this->db->join(db_prefix() . 'pm_categories c', 'c.id = sc.category_id', 'left');
        $this->db->join(db_prefix() . 'pm_subgroups s', 's.id = c.subgroup_id', 'left');
        $this->db->join(db_prefix() . 'pm_groups g', 'g.id = s.group_id', 'left');
        
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        $this->db->order_by('sc.sort_order', 'ASC');
        $this->db->order_by('sc.subcategory_name', 'ASC');
        
        return $this->db->get()->result_array();
    }

    /**
     * Get single subcategory
     */
    public function get_subcategory($id)
    {
        $this->db->select('sc.*, c.category_name, s.subgroup_name, g.group_name');
        $this->db->from(db_prefix() . 'pm_subcategories sc');
        $this->db->join(db_prefix() . 'pm_categories c', 'c.id = sc.category_id', 'left');
        $this->db->join(db_prefix() . 'pm_subgroups s', 's.id = c.subgroup_id', 'left');
        $this->db->join(db_prefix() . 'pm_groups g', 'g.id = s.group_id', 'left');
        $this->db->where('sc.id', $id);
        
        return $this->db->get()->row_array();
    }

    /**
     * Delete subcategory
     */
    public function delete_subcategory($id)
    {
        $subcategory = $this->get_subcategory($id);
        
        if (!$subcategory) {
            return false;
        }
        
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'pm_subcategories');
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Product Subcategory Deleted [' . $subcategory['subcategory_name'] . ']');
            return true;
        }
        
        return false;
    }

    // ==================== UNITS ====================
    
    /**
     * Add new unit
     */
    public function add_unit($data)
    {
        $data['unit_code'] = strtoupper($data['unit_code']);
        $data['unit_symbol'] = strtolower($data['unit_symbol']);
        
        if (isset($data['id'])) {
            unset($data['id']);
        }
        
        $this->db->insert(db_prefix() . 'pm_units', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('New Product Unit Added [' . $data['unit_name'] . ']');
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Update unit
     */
    public function update_unit($data, $id)
    {
        $data['unit_code'] = strtoupper($data['unit_code']);
        $data['unit_symbol'] = strtolower($data['unit_symbol']);
        
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'pm_units', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Product Unit Updated [' . $data['unit_name'] . ']');
            return true;
        }
        
        return false;
    }

    /**
     * Get units
     */
    public function get_units($where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        $this->db->order_by('unit_type', 'ASC');
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('unit_name', 'ASC');
        
        return $this->db->get(db_prefix() . 'pm_units')->result_array();
    }

    /**
     * Get single unit
     */
    public function get_unit($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'pm_units')->row_array();
    }

    /**
     * Delete unit
     */
    public function delete_unit($id)
    {
        $unit = $this->get_unit($id);
        
        if (!$unit) {
            return false;
        }
        
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'pm_units');
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Product Unit Deleted [' . $unit['unit_name'] . ']');
            return true;
        }
        
        return false;
    }

    // ==================== MULTI UNITS ====================
    
    /**
     * Add new multi unit
     */
    public function add_multi_unit($data)
    {
        if (isset($data['id'])) {
            unset($data['id']);
        }
        
        $this->db->insert(db_prefix() . 'pm_multi_units', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('New Multi Unit Conversion Added');
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Update multi unit
     */
    public function update_multi_unit($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'pm_multi_units', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Multi Unit Conversion Updated');
            return true;
        }
        
        return false;
    }

    /**
     * Get multi units
     */
    public function get_multi_units($where = [])
    {
        $this->db->select('mu.*, fu.unit_name as from_unit_name, fu.unit_symbol as from_unit_symbol, tu.unit_name as to_unit_name, tu.unit_symbol as to_unit_symbol');
        $this->db->from(db_prefix() . 'pm_multi_units mu');
        $this->db->join(db_prefix() . 'pm_units fu', 'fu.id = mu.from_unit_id', 'left');
        $this->db->join(db_prefix() . 'pm_units tu', 'tu.id = mu.to_unit_id', 'left');
        
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        $this->db->order_by('fu.unit_name', 'ASC');
        $this->db->order_by('tu.unit_name', 'ASC');
        
        return $this->db->get()->result_array();
    }

    /**
     * Get single multi unit
     */
    public function get_multi_unit($id)
    {
        $this->db->select('mu.*, fu.unit_name as from_unit_name, fu.unit_symbol as from_unit_symbol, tu.unit_name as to_unit_name, tu.unit_symbol as to_unit_symbol');
        $this->db->from(db_prefix() . 'pm_multi_units mu');
        $this->db->join(db_prefix() . 'pm_units fu', 'fu.id = mu.from_unit_id', 'left');
        $this->db->join(db_prefix() . 'pm_units tu', 'tu.id = mu.to_unit_id', 'left');
        $this->db->where('mu.id', $id);
        
        return $this->db->get()->row_array();
    }

    /**
     * Delete multi unit
     */
    public function delete_multi_unit($id)
    {
        $multi_unit = $this->get_multi_unit($id);
        
        if (!$multi_unit) {
            return false;
        }
        
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'pm_multi_units');
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Multi Unit Conversion Deleted');
            return true;
        }
        
        return false;
    }

    /**
     * Convert units
     */
    public function convert_units($from_unit_id, $to_unit_id, $quantity)
    {
        $this->db->where('from_unit_id', $from_unit_id);
        $this->db->where('to_unit_id', $to_unit_id);
        $this->db->where('status', 1);
        $conversion = $this->db->get(db_prefix() . 'pm_multi_units')->row_array();
        
        if ($conversion) {
            return $quantity * $conversion['conversion_rate'];
        }
        
        return $quantity; // Return original quantity if no conversion found
    }
}