<?php

// Module Name
$lang['product_master'] = 'Product Master';

// Menu Items
$lang['pm_groups'] = 'Groups';
$lang['pm_subgroups'] = 'Subgroups';
$lang['pm_categories'] = 'Categories';
$lang['pm_subcategories'] = 'Sub Categories';
$lang['pm_units'] = 'Units';
$lang['pm_multi_units'] = 'Multi Units';

// Groups
$lang['pm_add_group'] = 'Add Group';
$lang['pm_edit_group'] = 'Edit Group';
$lang['pm_group_code'] = 'Group Code';
$lang['pm_group_name'] = 'Group Name';
$lang['pm_select_group'] = 'Select Group';

// Subgroups
$lang['pm_add_subgroup'] = 'Add Subgroup';
$lang['pm_edit_subgroup'] = 'Edit Subgroup';
$lang['pm_subgroup_code'] = 'Subgroup Code';
$lang['pm_subgroup_name'] = 'Subgroup Name';
$lang['pm_select_subgroup'] = 'Select Subgroup';

// Categories
$lang['pm_add_category'] = 'Add Category';
$lang['pm_edit_category'] = 'Edit Category';
$lang['pm_category_code'] = 'Category Code';
$lang['pm_category_name'] = 'Category Name';
$lang['pm_select_category'] = 'Select Category';

// Subcategories
$lang['pm_add_subcategory'] = 'Add Sub Category';
$lang['pm_edit_subcategory'] = 'Edit Sub Category';
$lang['pm_subcategory_code'] = 'Sub Category Code';
$lang['pm_subcategory_name'] = 'Sub Category Name';
$lang['pm_select_subcategory'] = 'Select Sub Category';

// Units
$lang['pm_add_unit'] = 'Add Unit';
$lang['pm_edit_unit'] = 'Edit Unit';
$lang['pm_unit_code'] = 'Unit Code';
$lang['pm_unit_name'] = 'Unit Name';
$lang['pm_unit_symbol'] = 'Unit Symbol';
$lang['pm_unit_type'] = 'Unit Type';
$lang['pm_base_unit'] = 'Base Unit';
$lang['pm_conversion_factor'] = 'Conversion Factor';
$lang['pm_select_unit'] = 'Select Unit';

// Unit Types
$lang['pm_unit_type_length'] = 'Length';
$lang['pm_unit_type_weight'] = 'Weight';
$lang['pm_unit_type_volume'] = 'Volume';
$lang['pm_unit_type_area'] = 'Area';
$lang['pm_unit_type_quantity'] = 'Quantity';
$lang['pm_unit_type_time'] = 'Time';

// Multi Units
$lang['pm_add_multi_unit'] = 'Add Unit Conversion';
$lang['pm_edit_multi_unit'] = 'Edit Unit Conversion';
$lang['pm_from_unit'] = 'From Unit';
$lang['pm_to_unit'] = 'To Unit';
$lang['pm_conversion_rate'] = 'Conversion Rate';
$lang['pm_formula'] = 'Formula';
$lang['pm_default'] = 'Default';
$lang['pm_default_conversion'] = 'Default Conversion';

// Common Fields
$lang['pm_sort_order'] = 'Sort Order';
$lang['pm_hierarchy'] = 'Product Hierarchy';

// Settings
$lang['pm_settings'] = 'Product Master Settings';
$lang['pm_auto_generate_codes'] = 'Auto Generate Codes';
$lang['pm_auto_generate_codes_help'] = 'Automatically generate unique codes for new items';
$lang['pm_require_approval'] = 'Require Approval';
$lang['pm_require_approval_help'] = 'Require admin approval for new items';
$lang['pm_code_prefix'] = 'Code Prefix';
$lang['pm_code_prefix_help'] = 'Prefix for auto-generated codes (e.g., PM)';
$lang['pm_code_length'] = 'Code Length';
$lang['pm_code_length_help'] = 'Total length of auto-generated codes including prefix';
$lang['pm_enable_categories'] = 'Enable Categories';
$lang['pm_enable_categories_help'] = 'Enable category management in the system';
$lang['pm_enable_subcategories'] = 'Enable Sub Categories';
$lang['pm_enable_subcategories_help'] = 'Enable sub-category management in the system';
$lang['pm_enable_multi_units'] = 'Enable Multi Units';
$lang['pm_enable_multi_units_help'] = 'Enable multiple unit conversions';
$lang['pm_default_status'] = 'Default Status';
$lang['pm_default_status_help'] = 'Default status for new items';

// Data Management
$lang['pm_data_management'] = 'Data Management';
$lang['pm_data_management_info'] = 'Configure how the system handles product master data validation and deletion.';
$lang['pm_allow_duplicate_codes'] = 'Allow Duplicate Codes';
$lang['pm_allow_duplicate_codes_help'] = 'Allow duplicate codes within the same level';
$lang['pm_cascade_delete'] = 'Cascade Delete';
$lang['pm_cascade_delete_help'] = 'Delete child records when parent is deleted';

// Validation Messages
$lang['pm_group_code_exists'] = 'Group code already exists';
$lang['pm_subgroup_code_exists'] = 'Subgroup code already exists';
$lang['pm_category_code_exists'] = 'Category code already exists';
$lang['pm_subcategory_code_exists'] = 'Sub category code already exists';
$lang['pm_unit_code_exists'] = 'Unit code already exists';
$lang['pm_conversion_exists'] = 'Unit conversion already exists';

// Success Messages
$lang['pm_group_added'] = 'Group added successfully';
$lang['pm_group_updated'] = 'Group updated successfully';
$lang['pm_group_deleted'] = 'Group deleted successfully';
$lang['pm_subgroup_added'] = 'Subgroup added successfully';
$lang['pm_subgroup_updated'] = 'Subgroup updated successfully';
$lang['pm_subgroup_deleted'] = 'Subgroup deleted successfully';
$lang['pm_category_added'] = 'Category added successfully';
$lang['pm_category_updated'] = 'Category updated successfully';
$lang['pm_category_deleted'] = 'Category deleted successfully';
$lang['pm_subcategory_added'] = 'Sub category added successfully';
$lang['pm_subcategory_updated'] = 'Sub category updated successfully';
$lang['pm_subcategory_deleted'] = 'Sub category deleted successfully';
$lang['pm_unit_added'] = 'Unit added successfully';
$lang['pm_unit_updated'] = 'Unit updated successfully';
$lang['pm_unit_deleted'] = 'Unit deleted successfully';
$lang['pm_multi_unit_added'] = 'Unit conversion added successfully';
$lang['pm_multi_unit_updated'] = 'Unit conversion updated successfully';
$lang['pm_multi_unit_deleted'] = 'Unit conversion deleted successfully';

// Error Messages
$lang['pm_group_delete_error'] = 'Cannot delete group with subgroups';
$lang['pm_subgroup_delete_error'] = 'Cannot delete subgroup with categories';
$lang['pm_category_delete_error'] = 'Cannot delete category with sub categories';
$lang['pm_unit_delete_error'] = 'Cannot delete unit with conversions';

// Help Text
$lang['pm_hierarchy_help'] = 'Product hierarchy: Group > Subgroup > Category > Sub Category';
$lang['pm_unit_conversion_help'] = 'Define conversion rates between different units';
$lang['pm_base_unit_help'] = 'Mark this as the base unit for this unit type';
$lang['pm_conversion_factor_help'] = 'Factor to convert to the base unit (e.g., 0.001 for grams to kilograms)';

// Tooltips
$lang['pm_view_hierarchy'] = 'View Product Hierarchy';
$lang['pm_manage_conversions'] = 'Manage Unit Conversions';
$lang['pm_bulk_import'] = 'Bulk Import';
$lang['pm_bulk_export'] = 'Bulk Export';

// Reports
$lang['pm_reports'] = 'Product Master Reports';
$lang['pm_hierarchy_report'] = 'Product Hierarchy Report';
$lang['pm_units_report'] = 'Units & Conversions Report';
$lang['pm_usage_report'] = 'Product Master Usage Report';