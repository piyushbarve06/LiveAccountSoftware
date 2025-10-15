<?php
/**
 * Quick Fix: Update Tally Port from 9000 to 9002
 */

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "accountcrm";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Fixing Tally Connection Settings</h2>";
    
    // Check current settings
    echo "<h3>Current Settings:</h3>";
    $stmt = $conn->query("SELECT name, value FROM tbloptions WHERE name LIKE 'tally_%' ORDER BY name");
    $current = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    echo "<pre>";
    print_r($current);
    echo "</pre>";
    
    // Update/Insert tally_port to 9002
    echo "<h3>Updating Settings...</h3>";
    
    $updates = [
        'tally_port' => '9002',
        'tally_server_url' => 'http://localhost'
    ];
    
    foreach ($updates as $name => $value) {
        $stmt = $conn->prepare("
            INSERT INTO tbloptions (name, value) 
            VALUES (:name, :value) 
            ON DUPLICATE KEY UPDATE value = :value2
        ");
        $stmt->execute([
            ':name' => $name,
            ':value' => $value,
            ':value2' => $value
        ]);
        echo "✅ Updated $name = $value<br>";
    }
    
    // Show updated settings
    echo "<h3>Updated Settings:</h3>";
    $stmt = $conn->query("SELECT name, value FROM tbloptions WHERE name LIKE 'tally_%' ORDER BY name");
    $updated = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    echo "<pre>";
    print_r($updated);
    echo "</pre>";
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✅ Settings Updated Successfully!</h3>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li>Make sure TallyPrime is running</li>";
    echo "<li>In TallyPrime, press F12 → Advanced Configuration → ODBC Configuration</li>";
    echo "<li>Enable ODBC Server and note the port (should be 9002)</li>";
    echo "<li>Test the connection in your browser: <a href='http://localhost:9002' target='_blank'>http://localhost:9002</a></li>";
    echo "<li>Go back to your Tally Integration page and try again</li>";
    echo "</ol>";
    
    echo "<p><a href='admin/tally_integration' style='padding:10px 20px; background:#28a745; color:white; text-decoration:none; border-radius:5px;'>Go to Tally Integration</a></p>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
    echo "<p>Make sure:</p>";
    echo "<ul>";
    echo "<li>MySQL is running (XAMPP Apache + MySQL)</li>";
    echo "<li>Database name is 'accountcrm'</li>";
    echo "<li>Username is 'root' with no password</li>";
    echo "</ul>";
}
?>