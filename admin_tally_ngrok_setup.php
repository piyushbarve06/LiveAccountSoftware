<!DOCTYPE html>
<html>
<head>
    <title>Tally Ngrok Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px 0; }
        .code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>🔗 Tally Integration - Ngrok Setup</h1>
    
    <?php
    // Include database config
    if (file_exists('application/config/app-config.php')) {
        require_once 'application/config/app-config.php';
        
        $ngrok_url = "https://josue-considerate-brad.ngrok-free.dev";
        
        try {
            $pdo = new PDO("mysql:host=" . APP_DB_HOSTNAME . ";dbname=" . APP_DB_NAME, APP_DB_USERNAME, APP_DB_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Handle form submission
            if (isset($_POST['update_settings'])) {
                // Update server URL
                $stmt = $pdo->prepare("INSERT INTO tbloptions (name, value) VALUES ('tally_server_url', ?) ON DUPLICATE KEY UPDATE value = ?");
                $stmt->execute([$ngrok_url, $ngrok_url]);
                
                // Remove old server_port if exists
                $stmt = $pdo->prepare("DELETE FROM tbloptions WHERE name = 'tally_server_port'");
                $stmt->execute();
                
                // Enable integration
                $stmt = $pdo->prepare("INSERT INTO tbloptions (name, value) VALUES ('tally_integration_enabled', '1') ON DUPLICATE KEY UPDATE value = '1'");
                $stmt->execute();
                
                echo '<div class="success">✅ Settings updated successfully!</div>';
            }
            
            // Get current settings
            $stmt = $pdo->prepare("SELECT name, value FROM tbloptions WHERE name LIKE 'tally_%' ORDER BY name");
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            echo '<div class="info">';
            echo '<h3>📋 Current Configuration</h3>';
            echo '<p><strong>Ngrok URL:</strong> <span class="code">' . $ngrok_url . '</span></p>';
            echo '<p><strong>Current Server URL:</strong> <span class="code">' . ($settings['tally_server_url'] ?? 'Not set') . '</span></p>';
            echo '</div>';
            
            if (($settings['tally_server_url'] ?? '') !== $ngrok_url) {
                echo '<div class="warning">';
                echo '<p><strong>⚠️ Configuration Update Needed</strong></p>';
                echo '<p>Your server URL needs to be updated to use the ngrok tunnel.</p>';
                echo '<form method="post" style="margin-top: 15px;">';
                echo '<button type="submit" name="update_settings" class="btn">Update to Ngrok URL</button>';
                echo '</form>';
                echo '</div>';
            } else {
                echo '<div class="success">✅ Perfect! Already configured with ngrok URL</div>';
            }
            
            echo '<h3>📊 All Tally Settings</h3>';
            echo '<table>';
            echo '<tr><th>Setting</th><th>Value</th></tr>';
            foreach ($settings as $name => $value) {
                echo '<tr><td>' . htmlspecialchars($name) . '</td><td>' . htmlspecialchars($value ?: 'Not set') . '</td></tr>';
            }
            echo '</table>';
            
        } catch (Exception $e) {
            echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px;">❌ Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px;">❌ Could not find app-config.php</div>';
    }
    ?>
    
    <div class="info">
        <h3>🎯 Next Steps</h3>
        <ol>
            <li><strong>Start Ngrok:</strong> Make sure your ngrok tunnel is running and forwarding to TallyPrime</li>
            <li><strong>Start TallyPrime:</strong> Enable gateway and make sure it's accessible</li>
            <li><strong>Configure Company:</strong> Go to <a href="admin/tally_integration/settings" target="_blank">Tally Integration Settings</a></li>
            <li><strong>Set Company Name:</strong> Enter your exact company name from TallyPrime</li>
            <li><strong>Test Connection:</strong> Use the test connection feature</li>
        </ol>
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="admin/tally_integration/settings" class="btn">→ Go to Tally Integration Settings</a>
        <a href="admin/tally_integration/" class="btn" style="background: #28a745;">→ Go to Dashboard</a>
    </div>
    
    <div class="info">
        <h4>💡 Ngrok Setup Verification</h4>
        <p>To verify your ngrok tunnel is working:</p>
        <ul>
            <li>Open <span class="code"><?php echo $ngrok_url; ?></span> in your browser</li>
            <li>You should see TallyPrime gateway response or data</li>
            <li>If you see "tunnel offline", restart your ngrok tunnel</li>
        </ul>
    </div>
</body>
</html>