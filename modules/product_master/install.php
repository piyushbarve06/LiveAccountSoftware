<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Create product groups table
if (!$CI->db->table_exists(db_prefix() . 'pm_groups')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "pm_groups` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `group_code` varchar(100) NOT NULL,
      `group_name` varchar(255) NOT NULL,
      `description` text NULL,
      `status` tinyint(1) NOT NULL DEFAULT 1,
      `sort_order` int(10) NOT NULL DEFAULT 0,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `group_code` (`group_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Create product subgroups table
if (!$CI->db->table_exists(db_prefix() . 'pm_subgroups')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "pm_subgroups` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `group_id` int(11) UNSIGNED NOT NULL,
      `subgroup_code` varchar(100) NOT NULL,
      `subgroup_name` varchar(255) NOT NULL,
      `description` text NULL,
      `status` tinyint(1) NOT NULL DEFAULT 1,
      `sort_order` int(10) NOT NULL DEFAULT 0,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `subgroup_code` (`subgroup_code`),
      KEY `group_id` (`group_id`),
      FOREIGN KEY (`group_id`) REFERENCES `" . db_prefix() . "pm_groups` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Create product categories table
if (!$CI->db->table_exists(db_prefix() . 'pm_categories')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "pm_categories` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `subgroup_id` int(11) UNSIGNED NOT NULL,
      `category_code` varchar(100) NOT NULL,
      `category_name` varchar(255) NOT NULL,
      `description` text NULL,
      `status` tinyint(1) NOT NULL DEFAULT 1,
      `sort_order` int(10) NOT NULL DEFAULT 0,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `category_code` (`category_code`),
      KEY `subgroup_id` (`subgroup_id`),
      FOREIGN KEY (`subgroup_id`) REFERENCES `" . db_prefix() . "pm_subgroups` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Create product subcategories table
if (!$CI->db->table_exists(db_prefix() . 'pm_subcategories')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "pm_subcategories` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `category_id` int(11) UNSIGNED NOT NULL,
      `subcategory_code` varchar(100) NOT NULL,
      `subcategory_name` varchar(255) NOT NULL,
      `description` text NULL,
      `status` tinyint(1) NOT NULL DEFAULT 1,
      `sort_order` int(10) NOT NULL DEFAULT 0,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `subcategory_code` (`subcategory_code`),
      KEY `category_id` (`category_id`),
      FOREIGN KEY (`category_id`) REFERENCES `" . db_prefix() . "pm_categories` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Create units table
if (!$CI->db->table_exists(db_prefix() . 'pm_units')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "pm_units` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `unit_code` varchar(100) NOT NULL,
      `unit_name` varchar(255) NOT NULL,
      `unit_symbol` varchar(20) NOT NULL,
      `unit_type` enum('length','weight','volume','area','quantity','time') NOT NULL DEFAULT 'quantity',
      `base_unit` tinyint(1) NOT NULL DEFAULT 0,
      `conversion_factor` decimal(10,4) NOT NULL DEFAULT 1.0000,
      `description` text NULL,
      `status` tinyint(1) NOT NULL DEFAULT 1,
      `sort_order` int(10) NOT NULL DEFAULT 0,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unit_code` (`unit_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Create multi units table for conversion between units
if (!$CI->db->table_exists(db_prefix() . 'pm_multi_units')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "pm_multi_units` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `from_unit_id` int(11) UNSIGNED NOT NULL,
      `to_unit_id` int(11) UNSIGNED NOT NULL,
      `conversion_rate` decimal(15,6) NOT NULL,
      `formula` varchar(500) NULL,
      `is_default` tinyint(1) NOT NULL DEFAULT 0,
      `status` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `from_unit_id` (`from_unit_id`),
      KEY `to_unit_id` (`to_unit_id`),
      FOREIGN KEY (`from_unit_id`) REFERENCES `" . db_prefix() . "pm_units` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`to_unit_id`) REFERENCES `" . db_prefix() . "pm_units` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Insert default units
if ($CI->db->count_all_results(db_prefix() . 'pm_units') == 0) {
    $default_units = [
        ['unit_code' => 'PCS', 'unit_name' => 'Pieces', 'unit_symbol' => 'pcs', 'unit_type' => 'quantity', 'base_unit' => 1],
        ['unit_code' => 'KG', 'unit_name' => 'Kilogram', 'unit_symbol' => 'kg', 'unit_type' => 'weight', 'base_unit' => 1],
        ['unit_code' => 'GM', 'unit_name' => 'Gram', 'unit_symbol' => 'g', 'unit_type' => 'weight', 'conversion_factor' => 0.001],
        ['unit_code' => 'LTR', 'unit_name' => 'Liter', 'unit_symbol' => 'L', 'unit_type' => 'volume', 'base_unit' => 1],
        ['unit_code' => 'ML', 'unit_name' => 'Milliliter', 'unit_symbol' => 'ml', 'unit_type' => 'volume', 'conversion_factor' => 0.001],
        ['unit_code' => 'MTR', 'unit_name' => 'Meter', 'unit_symbol' => 'm', 'unit_type' => 'length', 'base_unit' => 1],
        ['unit_code' => 'CM', 'unit_name' => 'Centimeter', 'unit_symbol' => 'cm', 'unit_type' => 'length', 'conversion_factor' => 0.01],
        ['unit_code' => 'BOX', 'unit_name' => 'Box', 'unit_symbol' => 'box', 'unit_type' => 'quantity'],
        ['unit_code' => 'DOZ', 'unit_name' => 'Dozen', 'unit_symbol' => 'doz', 'unit_type' => 'quantity', 'conversion_factor' => 12],
        ['unit_code' => 'SET', 'unit_name' => 'Set', 'unit_symbol' => 'set', 'unit_type' => 'quantity'],
    ];

    foreach ($default_units as $unit) {
        $CI->db->insert(db_prefix() . 'pm_units', $unit);
    }
}

// Add permissions
add_option('product_master_permissions', json_encode([
    'view' => 1,
    'create' => 1,
    'edit' => 1,
    'delete' => 1
]));