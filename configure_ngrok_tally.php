<?php
// Direct configuration script for ngrok Tally setup
require_once 'application/config/app-config.php';

// Your ngrok URL
$ngrok_url = "https://josue-considerate-brad.ngrok-free.dev";

echo "🔗 Configuring Tally Integration for Ngrok URL...\n";
echo "URL: $ngrok_url\n\n";

try {
    // Connect to database
    $pdo = new PDO("mysql:host=" . APP_DB_HOSTNAME . ";dbname=" . APP_DB_NAME, APP_DB_USERNAME, APP_DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Update or insert tally_server_url
    $stmt = $pdo->prepare("INSERT INTO tbloptions (name, value) VALUES ('tally_server_url', ?) 
                          ON DUPLICATE KEY UPDATE value = ?");
    $stmt->execute([$ngrok_url, $ngrok_url]);
    echo "✅ Server URL updated to: $ngrok_url\n";
    
    // Make sure integration is enabled
    $stmt = $pdo->prepare("INSERT INTO tbloptions (name, value) VALUES ('tally_integration_enabled', '1') 
                          ON DUPLICATE KEY UPDATE value = '1'");
    $stmt->execute();
    echo "✅ Integration enabled\n";
    
    // Set default company name if not set
    $stmt = $pdo->prepare("SELECT value FROM tbloptions WHERE name = 'tally_company_name'");
    $stmt->execute();
    $company = $stmt->fetchColumn();
    
    if (empty($company)) {
        $stmt = $pdo->prepare("INSERT INTO tbloptions (name, value) VALUES ('tally_company_name', '') 
                              ON DUPLICATE KEY UPDATE value = ''");
        $stmt->execute();
        echo "⚠️  Company name not set - please configure in settings\n";
    } else {
        echo "✅ Company name: $company\n";
    }
    
    // Enable auto sync options
    $sync_options = [
        'tally_auto_sync_customers' => '1',
        'tally_auto_sync_invoices' => '1', 
        'tally_auto_sync_payments' => '1',
        'tally_sync_on_create' => '1',
        'tally_sync_on_update' => '0'
    ];
    
    foreach ($sync_options as $option => $value) {
        $stmt = $pdo->prepare("INSERT INTO tbloptions (name, value) VALUES (?, ?) 
                              ON DUPLICATE KEY UPDATE value = ?");
        $stmt->execute([$option, $value, $value]);
    }
    echo "✅ Sync options configured\n";
    
    echo "\n🎉 Configuration Complete!\n\n";
    echo "Next steps:\n";
    echo "1. Make sure your ngrok tunnel is running\n";
    echo "2. Make sure TallyPrime is running with gateway enabled\n";
    echo "3. Go to: http://localhost/Accountsoftwarecrm/admin/tally_integration/settings\n";
    echo "4. Set your company name\n";
    echo "5. Test the connection\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>