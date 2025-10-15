<?php
/**
 * Single Session Login - Database Migration Script
 * This script adds the current_session_token column to tblstaff and tblcontacts tables
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'accountcrm';
$prefix = 'tbl';

// Connect to database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Single Session Login - Database Migration</h2>";
echo "<hr>";

// Add column to tblstaff
$table_staff = $prefix . 'staff';
$check_staff = $conn->query("SHOW COLUMNS FROM `{$table_staff}` LIKE 'current_session_token'");

if ($check_staff->num_rows == 0) {
    $sql = "ALTER TABLE `{$table_staff}` ADD `current_session_token` VARCHAR(128) NULL DEFAULT NULL AFTER `last_login`";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green;'>✓ Column 'current_session_token' added to {$table_staff} successfully</p>";
    } else {
        echo "<p style='color:red;'>✗ Error adding column to {$table_staff}: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:orange;'>⚠ Column 'current_session_token' already exists in {$table_staff}</p>";
}

// Add column to tblcontacts
$table_contacts = $prefix . 'contacts';
$check_contacts = $conn->query("SHOW COLUMNS FROM `{$table_contacts}` LIKE 'current_session_token'");

if ($check_contacts->num_rows == 0) {
    $sql = "ALTER TABLE `{$table_contacts}` ADD `current_session_token` VARCHAR(128) NULL DEFAULT NULL AFTER `last_login`";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green;'>✓ Column 'current_session_token' added to {$table_contacts} successfully</p>";
    } else {
        echo "<p style='color:red;'>✗ Error adding column to {$table_contacts}: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:orange;'>⚠ Column 'current_session_token' already exists in {$table_contacts}</p>";
}

echo "<hr>";
echo "<h3>Migration Complete!</h3>";
echo "<p><strong>Single session login feature has been implemented successfully.</strong></p>";
echo "<p>You can now delete this file for security: <code>run_single_session_migration.php</code></p>";

$conn->close();
?>