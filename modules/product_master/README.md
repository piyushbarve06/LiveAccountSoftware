# Product Master Module

## Overview
Complete Product Master management system for Perfex CRM that handles hierarchical product categorization and multi-unit management.

## Features

### 📁 Product Hierarchy Management
- **Groups** - Top-level product grouping
- **Subgroups** - Secondary level categorization under groups
- **Categories** - Third level categorization under subgroups
- **Sub-categories** - Fourth level detailed categorization

### ⚖️ Units & Multi-Units Management
- **Units** - Define measurement units (length, weight, volume, area, quantity, time)
- **Multi-Units** - Setup conversion rates between different units
- **Base Units** - Define base units for each unit type
- **Conversion Calculator** - Automatic unit conversions

### 🔧 Key Features
- **Hierarchical Structure** - Complete 4-level product categorization
- **Code Management** - Auto-generate or manual product codes
- **Status Management** - Active/Inactive status for all items
- **Sort Ordering** - Custom sort order for organized display
- **Data Validation** - Prevent duplicate codes and ensure data integrity
- **Cascade Operations** - Related data loading based on parent selection
- **Responsive Design** - Works perfectly on all devices
- **AJAX Operations** - Smooth user experience with real-time updates

## Installation

1. Upload the `product_master` folder to your `modules/` directory
2. Navigate to **Setup > Modules** in your Perfex CRM admin panel
3. Find "Product Master" and click **Install**
4. The module will automatically create required database tables
5. Access the module from the main navigation menu

## Database Tables Created

- `pm_groups` - Product groups
- `pm_subgroups` - Product subgroups
- `pm_categories` - Product categories  
- `pm_subcategories` - Product sub-categories
- `pm_units` - Units of measurement
- `pm_multi_units` - Unit conversions

## Usage

### Managing Product Groups
1. Go to **Product Master > Groups**
2. Click **Add Group** to create new groups
3. Define group code, name, description, and sort order

### Managing Subgroups
1. Go to **Product Master > Subgroups**
2. Select parent group and define subgroup details
3. System maintains parent-child relationships automatically

### Managing Categories & Sub-categories
1. Follow the same hierarchical pattern
2. Each level depends on the parent level selection
3. Real-time cascade loading for smooth data entry

### Managing Units
1. Go to **Product Master > Units**
2. Define unit types (length, weight, volume, etc.)
3. Set base units and conversion factors

### Managing Multi-Units
1. Go to **Product Master > Multi Units**
2. Setup conversion rates between different units
3. Define formulas for complex conversions

## Settings Configuration

Access **Product Master > Settings** to configure:

- **Auto Generate Codes** - Automatic code generation
- **Code Prefix** - Prefix for auto-generated codes
- **Code Length** - Length of generated codes
- **Default Status** - Default active/inactive status
- **Enable/Disable Features** - Control which features to use
- **Data Management** - Validation and deletion rules

## Integration

The module provides helper functions for integration with other modules:

```php
// Get all active groups
$groups = get_pm_groups();

// Get subgroups by group
$subgroups = get_pm_subgroups_by_group($group_id);

// Get complete hierarchy dropdown
$hierarchy = get_pm_hierarchy_dropdown();

// Convert units
$converted = convert_pm_units($from_unit_id, $to_unit_id, $quantity);
```

## Permissions

The module includes comprehensive permission system:

- **View** - View product master data
- **Create** - Add new items
- **Edit** - Modify existing items  
- **Delete** - Remove items

## Technical Details

- **Framework** - Built on Perfex CRM (CodeIgniter)
- **Database** - MySQL with foreign key constraints
- **Frontend** - Bootstrap, jQuery, DataTables
- **Backend** - PHP with MVC architecture
- **Security** - CSRF protection, SQL injection prevention
- **Performance** - Optimized queries and indexes

## File Structure

```
product_master/
├── assets/
│   ├── css/product_master.css
│   └── js/product_master.js
├── controllers/Product_master.php
├── models/Product_master_model.php
├── views/
│   ├── groups.php
│   ├── subgroups.php
│   ├── categories.php
│   ├── subcategories.php
│   ├── units.php
│   ├── multi_units.php
│   └── settings.php
├── helpers/Product_master_helper.php
├── language/english/product_master_lang.php
├── install.php
├── product_master.php
└── README.md
```

## Support

For support and customization requests, please contact the development team.

## Version History

- **v1.0.0** - Initial release with complete product master functionality

## License

This module is part of the Perfex CRM ecosystem and follows the same licensing terms.