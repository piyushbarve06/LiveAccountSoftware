<?php
// Debug script for single session login

require_once('application/config/database.php');

$conn = mysqli_connect(
    $db['default']['hostname'],
    $db['default']['username'],
    $db['default']['password'],
    $db['default']['database']
);

if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

echo "<h2>Single Session Login - Debug Information</h2>";
echo "<style>body{font-family:Arial;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#4CAF50;color:white;} .error{color:red;} .success{color:green;}</style>";

// 1. Check if columns exist
echo "<h3>1. Database Column Check:</h3>";
$columns_ok = true;

$result = mysqli_query($conn, "SHOW COLUMNS FROM tblstaff LIKE 'current_session_token'");
if (mysqli_num_rows($result) > 0) {
    echo "<p class='success'>✅ tblstaff.current_session_token column EXISTS</p>";
} else {
    echo "<p class='error'>❌ tblstaff.current_session_token column MISSING</p>";
    $columns_ok = false;
}

$result = mysqli_query($conn, "SHOW COLUMNS FROM tblcontacts LIKE 'current_session_token'");
if (mysqli_num_rows($result) > 0) {
    echo "<p class='success'>✅ tblcontacts.current_session_token column EXISTS</p>";
} else {
    echo "<p class='error'>❌ tblcontacts.current_session_token column MISSING</p>";
    $columns_ok = false;
}

if (!$columns_ok) {
    echo "<div style='background:#fff3cd;padding:15px;border:1px solid #ffc107;margin:10px 0;'>";
    echo "<h4>⚠️ COLUMNS ARE MISSING - Run This SQL:</h4>";
    echo "<pre style='background:#f8f9fa;padding:10px;'>ALTER TABLE `tblstaff` ADD `current_session_token` VARCHAR(128) NULL DEFAULT NULL AFTER `last_login`;
ALTER TABLE `tblcontacts` ADD `current_session_token` VARCHAR(128) NULL DEFAULT NULL AFTER `last_login`;</pre>";
    echo "</div>";
}

// 2. Check current logged in users with tokens
echo "<h3>2. Currently Logged In Staff (with session tokens):</h3>";
$result = mysqli_query($conn, "SELECT staffid, CONCAT(firstname, ' ', lastname) as name, email, current_session_token, last_login FROM tblstaff WHERE current_session_token IS NOT NULL AND current_session_token != ''");
if (mysqli_num_rows($result) > 0) {
    echo "<table>";
    echo "<tr><th>Staff ID</th><th>Name</th><th>Email</th><th>Token (truncated)</th><th>Last Login</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        $token_short = substr($row['current_session_token'], 0, 20) . '...';
        echo "<tr><td>{$row['staffid']}</td><td>{$row['name']}</td><td>{$row['email']}</td><td>{$token_short}</td><td>{$row['last_login']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No staff members currently logged in (no tokens found)</p>";
}

// 3. Check contacts
echo "<h3>3. Currently Logged In Clients (with session tokens):</h3>";
$result = mysqli_query($conn, "SELECT id, CONCAT(firstname, ' ', lastname) as name, email, current_session_token, last_login FROM tblcontacts WHERE current_session_token IS NOT NULL AND current_session_token != ''");
if (mysqli_num_rows($result) > 0) {
    echo "<table>";
    echo "<tr><th>Contact ID</th><th>Name</th><th>Email</th><th>Token (truncated)</th><th>Last Login</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        $token_short = substr($row['current_session_token'], 0, 20) . '...';
        echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['email']}</td><td>{$token_short}</td><td>{$row['last_login']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No clients currently logged in (no tokens found)</p>";
}

// 4. Check if Authentication model has been modified
echo "<h3>4. Code Implementation Check:</h3>";
$auth_model = file_get_contents('application/models/Authentication_model.php');
if (strpos($auth_model, 'set_session_token') !== false) {
    echo "<p class='success'>✅ set_session_token method exists in Authentication_model.php</p>";
} else {
    echo "<p class='error'>❌ set_session_token method MISSING in Authentication_model.php</p>";
}

if (strpos($auth_model, 'force_login') !== false) {
    echo "<p class='success'>✅ force_login parameter exists in login method</p>";
} else {
    echo "<p class='error'>❌ force_login parameter MISSING in login method</p>";
}

// 5. Check if controllers have been modified
$admin_auth = file_get_contents('application/controllers/admin/Authentication.php');
if (strpos($admin_auth, 'already_logged_in') !== false) {
    echo "<p class='success'>✅ Admin controller has already_logged_in check</p>";
} else {
    echo "<p class='error'>❌ Admin controller MISSING already_logged_in check</p>";
}

// 6. Check if JavaScript exists
$login_admin = file_get_contents('application/views/authentication/login_admin.php');
if (strpos($login_admin, 'Single session login') !== false) {
    echo "<p class='success'>✅ Admin login page has single session JavaScript</p>";
} else {
    echo "<p class='error'>❌ Admin login page MISSING single session JavaScript</p>";
}

mysqli_close($conn);

echo "<hr>";
echo "<h3>📋 Testing Instructions:</h3>";
echo "<ol>";
echo "<li>Fix any ❌ errors shown above</li>";
echo "<li>Clear browser cache and cookies (Ctrl+Shift+Del)</li>";
echo "<li>Open Chrome → Login to admin panel</li>";
echo "<li>Open Firefox → Try to login with same credentials</li>";
echo "<li>You should see a SweetAlert popup</li>";
echo "<li>Click 'OK, login here' → Should login in Firefox and logout in Chrome</li>";
echo "</ol>";

echo "<h3>🔄 Manual Token Clear (if needed):</h3>";
echo "<p>If you want to clear all tokens and start fresh, <a href='?clear_tokens=1'>click here</a></p>";

if (isset($_GET['clear_tokens'])) {
    mysqli_query($conn, "UPDATE tblstaff SET current_session_token = NULL");
    mysqli_query($conn, "UPDATE tblcontacts SET current_session_token = NULL");
    echo "<p class='success'>✅ All session tokens cleared! You can now test fresh.</p>";
    echo "<script>setTimeout(function(){ location.href = 'test_single_session.php'; }, 2000);</script>";
}
?>