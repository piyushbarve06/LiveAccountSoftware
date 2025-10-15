<?php
// Debug script to check groups data
require_once('application/config/database.php');

// Connect to database
$mysqli = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Check if table exists
$table_name = $db['default']['dbprefix'] . 'pm_groups';
echo "<h3>Checking table: " . $table_name . "</h3>";

$result = $mysqli->query("SHOW TABLES LIKE '{$table_name}'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Table exists</p>";
    
    // Check data
    $data_result = $mysqli->query("SELECT * FROM `{$table_name}` ORDER BY sort_order ASC, group_name ASC");
    echo "<p>Found " . $data_result->num_rows . " records:</p>";
    
    if ($data_result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Group Code</th><th>Group Name</th><th>Description</th><th>Status</th><th>Sort Order</th></tr>";
        
        while ($row = $data_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['group_code']) . "</td>";
            echo "<td>" . htmlspecialchars($row['group_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['description'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['sort_order']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠ Table exists but is empty</p>";
        echo "<p><strong>Solution:</strong> You need to add some groups first!</p>";
        
        // Insert some sample data
        echo "<h4>Adding sample data...</h4>";
        $sample_groups = [
            ['group_code' => 'ELEC', 'group_name' => 'Electronics', 'description' => 'Electronic items and components', 'status' => 1, 'sort_order' => 1],
            ['group_code' => 'FOOD', 'group_name' => 'Food Items', 'description' => 'Food and beverage products', 'status' => 1, 'sort_order' => 2],
            ['group_code' => 'CLOTH', 'group_name' => 'Clothing', 'description' => 'Apparel and accessories', 'status' => 1, 'sort_order' => 3],
            ['group_code' => 'HOME', 'group_name' => 'Home & Garden', 'description' => 'Home improvement and garden items', 'status' => 1, 'sort_order' => 4],
            ['group_code' => 'AUTO', 'group_name' => 'Automotive', 'description' => 'Automotive parts and accessories', 'status' => 1, 'sort_order' => 5]
        ];
        
        foreach ($sample_groups as $group) {
            $stmt = $mysqli->prepare("INSERT INTO `{$table_name}` (group_code, group_name, description, status, sort_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssii', $group['group_code'], $group['group_name'], $group['description'], $group['status'], $group['sort_order']);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✓ Added: " . htmlspecialchars($group['group_name']) . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to add: " . htmlspecialchars($group['group_name']) . " - " . $mysqli->error . "</p>";
            }
            $stmt->close();
        }
        
        echo "<p><strong>Now refresh your groups page to see the data!</strong></p>";
    }
} else {
    echo "<p style='color: red;'>✗ Table does not exist</p>";
    echo "<p><strong>Solution:</strong> The module needs to be installed/activated properly.</p>";
}

$mysqli->close();
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>