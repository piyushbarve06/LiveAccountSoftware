<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Product Master
Description: Complete Product Master management system for Groups, Subgroups, Categories, Sub-categories, and Multi-units
Version: 1.0.0
Requires at least: 2.3.*
Author: Perfex CRM Development Team
*/

define('PRODUCT_MASTER_MODULE_NAME', 'product_master');
define('PRODUCT_MASTER_MODULE_UPLOAD_FOLDER', module_dir_path(PRODUCT_MASTER_MODULE_NAME, 'uploads'));
define('PRODUCT_MASTER_REVISION', 100);

// Register activation hook
register_activation_hook(PRODUCT_MASTER_MODULE_NAME, 'product_master_module_activation_hook');

/**
 * Product master module activation hook
 * @return void 
 */
function product_master_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

// Register language files
register_language_files(PRODUCT_MASTER_MODULE_NAME, [PRODUCT_MASTER_MODULE_NAME]);

// Initialize module
hooks()->add_action('admin_init', 'product_master_permissions');
hooks()->add_action('app_admin_head', 'product_master_add_head_components');
hooks()->add_action('app_admin_footer', 'product_master_load_js');
hooks()->add_action('admin_init', 'product_master_module_init_menu_items');

// Load helper
$CI = & get_instance();
$CI->load->helper(PRODUCT_MASTER_MODULE_NAME . '/product_master');

/**
 * Init product master module menu items in admin_init hook
 * @return null
 */
function product_master_module_init_menu_items()
{
    $CI = &get_instance();
    if (has_permission('product_master', '', 'view')) {

        $CI->app_menu->add_sidebar_menu_item('product_master', [
            'name'     => _l('product_master'),
            'icon'     => 'fa fa-sitemap',
            'position' => 25,
        ]);

        $CI->app_menu->add_sidebar_children_item('product_master', [
            'slug'     => 'pm_groups',
            'name'     => _l('pm_groups'),
            'icon'     => 'fa fa-folder-o menu-icon',
            'href'     => admin_url('product_master/groups'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('product_master', [
            'slug'     => 'pm_subgroups',
            'name'     => _l('pm_subgroups'),
            'icon'     => 'fa fa-folder menu-icon',
            'href'     => admin_url('product_master/subgroups'),
            'position' => 2,
        ]);

        $CI->app_menu->add_sidebar_children_item('product_master', [
            'slug'     => 'pm_categories',
            'name'     => _l('pm_categories'),
            'icon'     => 'fa fa-tags menu-icon',
            'href'     => admin_url('product_master/categories'),
            'position' => 3,
        ]);

        $CI->app_menu->add_sidebar_children_item('product_master', [
            'slug'     => 'pm_subcategories',
            'name'     => _l('pm_subcategories'),
            'icon'     => 'fa fa-tag menu-icon',
            'href'     => admin_url('product_master/subcategories'),
            'position' => 4,
        ]);

        $CI->app_menu->add_sidebar_children_item('product_master', [
            'slug'     => 'pm_units',
            'name'     => _l('pm_units'),
            'icon'     => 'fa fa-balance-scale menu-icon',
            'href'     => admin_url('product_master/units'),
            'position' => 5,
        ]);

        $CI->app_menu->add_sidebar_children_item('product_master', [
            'slug'     => 'pm_multi_units',
            'name'     => _l('pm_multi_units'),
            'icon'     => 'fa fa-cubes menu-icon',
            'href'     => admin_url('product_master/multi_units'),
            'position' => 6,
        ]);

        $CI->app_menu->add_sidebar_children_item('product_master', [
            'slug'     => 'pm_settings',
            'name'     => _l('settings'),
            'icon'     => 'fa fa-gears',
            'href'     => admin_url('product_master/settings'),
            'position' => 7,
        ]);
    }
}

/**
 * Init product master permissions
 */
function product_master_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('product_master', $capabilities, _l('product_master'));
}

/**
 * Add head components for product master
 */
function product_master_add_head_components()
{
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];
    if (!(strpos($viewuri, '/admin/product_master') === false)) {
        echo '<link href="' . module_dir_url(PRODUCT_MASTER_MODULE_NAME, 'assets/css/product_master.css') . '?v=' . PRODUCT_MASTER_REVISION . '"  rel="stylesheet" type="text/css" />';
    }
}

/**
 * Add footer JS for product master
 */
function product_master_load_js()
{
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];
    if (!(strpos($viewuri, '/admin/product_master') === false)) {
        echo '<script src="' . module_dir_url(PRODUCT_MASTER_MODULE_NAME, 'assets/js/product_master.js') . '?v=' . PRODUCT_MASTER_REVISION . '"></script>';
    }
}