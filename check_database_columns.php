<?php
// Quick check if database columns exist

$conn = mysqli_connect('localhost', 'root', '', 'accountcrm');

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

echo "<h2>Checking Database Columns...</h2>";

// Check tblstaff
$result = mysqli_query($conn, "SHOW COLUMNS FROM tblstaff LIKE 'current_session_token'");
if (mysqli_num_rows($result) > 0) {
    echo "<p style='color:green;'>✅ Column 'current_session_token' EXISTS in tblstaff</p>";
} else {
    echo "<p style='color:red;'>❌ Column 'current_session_token' MISSING in tblstaff</p>";
    echo "<p><strong>Run this SQL:</strong></p>";
    echo "<pre>ALTER TABLE `tblstaff` ADD `current_session_token` VARCHAR(128) NULL DEFAULT NULL AFTER `last_login`;</pre>";
}

// Check tblcontacts
$result = mysqli_query($conn, "SHOW COLUMNS FROM tblcontacts LIKE 'current_session_token'");
if (mysqli_num_rows($result) > 0) {
    echo "<p style='color:green;'>✅ Column 'current_session_token' EXISTS in tblcontacts</p>";
} else {
    echo "<p style='color:red;'>❌ Column 'current_session_token' MISSING in tblcontacts</p>";
    echo "<p><strong>Run this SQL:</strong></p>";
    echo "<pre>ALTER TABLE `tblcontacts` ADD `current_session_token` VARCHAR(128) NULL DEFAULT NULL AFTER `last_login`;</pre>";
}

// Check if any user has a token
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tblstaff WHERE current_session_token IS NOT NULL AND current_session_token != ''");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p>📊 Staff users with active session tokens: <strong>" . $row['count'] . "</strong></p>";
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tblcontacts WHERE current_session_token IS NOT NULL AND current_session_token != ''");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p>📊 Client users with active session tokens: <strong>" . $row['count'] . "</strong></p>";
}

mysqli_close($conn);

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If columns are MISSING, run the SQL commands above in phpMyAdmin</li>";
echo "<li>Clear browser cache and cookies</li>";
echo "<li>Try logging in from two different browsers</li>";
echo "</ol>";
?>