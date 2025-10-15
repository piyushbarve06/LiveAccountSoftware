<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Product Master Helper Functions
 */

if (!function_exists('get_pm_groups')) {
    /**
     * Get all active product groups
     */
    function get_pm_groups()
    {
        $CI = &get_instance();
        $CI->load->model('product_master/product_master_model');
        return $CI->product_master_model->get_groups(['status' => 1]);
    }
}

if (!function_exists('get_pm_subgroups_by_group')) {
    /**
     * Get subgroups by group ID
     */
    function get_pm_subgroups_by_group($group_id)
    {
        $CI = &get_instance();
        $CI->load->model('product_master/product_master_model');
        return $CI->product_master_model->get_subgroups(['group_id' => $group_id, 'status' => 1]);
    }
}

if (!function_exists('get_pm_categories_by_subgroup')) {
    /**
     * Get categories by subgroup ID
     */
    function get_pm_categories_by_subgroup($subgroup_id)
    {
        $CI = &get_instance();
        $CI->load->model('product_master/product_master_model');
        return $CI->product_master_model->get_categories(['subgroup_id' => $subgroup_id, 'status' => 1]);
    }
}

if (!function_exists('get_pm_subcategories_by_category')) {
    /**
     * Get subcategories by category ID
     */
    function get_pm_subcategories_by_category($category_id)
    {
        $CI = &get_instance();
        $CI->load->model('product_master/product_master_model');
        return $CI->product_master_model->get_subcategories(['category_id' => $category_id, 'status' => 1]);
    }
}

if (!function_exists('get_pm_units')) {
    /**
     * Get all active units
     */
    function get_pm_units()
    {
        $CI = &get_instance();
        $CI->load->model('product_master/product_master_model');
        return $CI->product_master_model->get_units(['status' => 1]);
    }
}

if (!function_exists('get_pm_units_by_type')) {
    /**
     * Get units by type
     */
    function get_pm_units_by_type($type)
    {
        $CI = &get_instance();
        $CI->load->model('product_master/product_master_model');
        return $CI->product_master_model->get_units(['unit_type' => $type, 'status' => 1]);
    }
}

if (!function_exists('convert_pm_units')) {
    /**
     * Convert quantity from one unit to another
     */
    function convert_pm_units($from_unit_id, $to_unit_id, $quantity)
    {
        $CI = &get_instance();
        $CI->load->model('product_master/product_master_model');
        return $CI->product_master_model->convert_units($from_unit_id, $to_unit_id, $quantity);
    }
}

if (!function_exists('get_pm_hierarchy_dropdown')) {
    /**
     * Get complete product hierarchy as dropdown options
     */
    function get_pm_hierarchy_dropdown()
    {
        $CI = &get_instance();
        $CI->load->model('product_master/product_master_model');
        
        $hierarchy = [];
        $groups = $CI->product_master_model->get_groups(['status' => 1]);
        
        foreach ($groups as $group) {
            $subgroups = $CI->product_master_model->get_subgroups(['group_id' => $group['id'], 'status' => 1]);
            
            foreach ($subgroups as $subgroup) {
                $categories = $CI->product_master_model->get_categories(['subgroup_id' => $subgroup['id'], 'status' => 1]);
                
                foreach ($categories as $category) {
                    $subcategories = $CI->product_master_model->get_subcategories(['category_id' => $category['id'], 'status' => 1]);
                    
                    if (!empty($subcategories)) {
                        foreach ($subcategories as $subcategory) {
                            $hierarchy[] = [
                                'value' => $subcategory['id'],
                                'label' => $group['group_name'] . ' > ' . $subgroup['subgroup_name'] . ' > ' . $category['category_name'] . ' > ' . $subcategory['subcategory_name'],
                                'group_id' => $group['id'],
                                'subgroup_id' => $subgroup['id'],
                                'category_id' => $category['id'],
                                'subcategory_id' => $subcategory['id']
                            ];
                        }
                    } else {
                        $hierarchy[] = [
                            'value' => $category['id'],
                            'label' => $group['group_name'] . ' > ' . $subgroup['subgroup_name'] . ' > ' . $category['category_name'],
                            'group_id' => $group['id'],
                            'subgroup_id' => $subgroup['id'],
                            'category_id' => $category['id'],
                            'subcategory_id' => null
                        ];
                    }
                }
            }
        }
        
        return $hierarchy;
    }
}

if (!function_exists('render_pm_status_badge')) {
    /**
     * Render status badge
     */
    function render_pm_status_badge($status)
    {
        if ($status == 1) {
            return '<span class="label label-success">' . _l('active') . '</span>';
        } else {
            return '<span class="label label-danger">' . _l('inactive') . '</span>';
        }
    }
}

if (!function_exists('get_pm_unit_types')) {
    /**
     * Get available unit types
     */
    function get_pm_unit_types()
    {
        return [
            'length' => _l('pm_unit_type_length'),
            'weight' => _l('pm_unit_type_weight'),
            'volume' => _l('pm_unit_type_volume'),
            'area' => _l('pm_unit_type_area'),
            'quantity' => _l('pm_unit_type_quantity'),
            'time' => _l('pm_unit_type_time')
        ];
    }
}