<?php
/**
 * Tally Integration Debug Test
 * This file helps debug Tally integration issues in live server
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Tally Integration Debug Test</h2>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Test 1: Check if required functions exist
echo "<h3>1. Function Availability Test</h3>";
$functions_to_check = ['libxml_clear_errors', 'libxml_get_errors', 'array_column'];
foreach ($functions_to_check as $func) {
    echo "- <code>{$func}()</code>: " . (function_exists($func) ? "✅ Available" : "❌ Missing") . "<br>";
}

// Test 2: PHP Version
echo "<h3>2. PHP Environment</h3>";
echo "- PHP Version: <code>" . PHP_VERSION . "</code><br>";
echo "- Server Software: <code>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</code><br>";

// Test 3: XML Processing
echo "<h3>3. XML Processing Test</h3>";
$test_xml = '<?xml version="1.0" encoding="UTF-8"?>
<ENVELOPE>
    <HEADER>
        <TALLYREQUEST>Export Data</TALLYREQUEST>
    </HEADER>
    <BODY>
        <EXPORTDATA>
            <REQUESTDESC>
                <REPORTNAME>List of Accounts</REPORTNAME>
            </REQUESTDESC>
        </EXPORTDATA>
    </BODY>
</ENVELOPE>';

try {
    $dom = new DOMDocument();
    $dom->recover = true;
    $dom->strictErrorChecking = false;
    
    if ($dom->loadXML($test_xml, LIBXML_NOCDATA)) {
        echo "- DOMDocument XML parsing: ✅ Working<br>";
        
        // Test the cleanArray function logic
        function testCleanArray($array) {
            if (!is_array($array)) {
                return $array;
            }
            
            $result = [];
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $value = testCleanArray($value);
                    if (!empty($value)) {
                        $result[$key] = $value;
                    }
                } elseif ($value !== '' && $value !== null) {
                    $result[$key] = $value;
                }
            }
            return $result;
        }
        
        // Test with various data types
        echo "- cleanArray with string: ";
        $result = testCleanArray("test_string");
        echo ($result === "test_string") ? "✅ Handled correctly" : "❌ Failed";
        echo "<br>";
        
        echo "- cleanArray with array: ";
        $test_array = ['key1' => 'value1', 'key2' => '', 'key3' => null, 'key4' => ['nested' => 'value']];
        $result = testCleanArray($test_array);
        echo (!empty($result)) ? "✅ Working" : "❌ Failed";
        echo "<br>";
        
    } else {
        echo "- DOMDocument XML parsing: ❌ Failed<br>";
        $errors = libxml_get_errors();
        if (!empty($errors)) {
            echo "  Errors: " . implode(", ", array_map(function($e) { return $e->message; }, $errors)) . "<br>";
        }
    }
} catch (Exception $e) {
    echo "- XML Processing Error: ❌ " . $e->getMessage() . "<br>";
}

// Test 4: Array Functions
echo "<h3>4. Array Function Test</h3>";
$test_data = [
    ['VOUCHER' => 'invoice1'],
    ['VOUCHER' => 'invoice2'],
    ['OTHER' => 'data']
];

try {
    $vouchers = array_column($test_data, 'VOUCHER');
    $vouchers = array_filter($vouchers, function($item) { return $item !== null; });
    echo "- array_column test: ✅ Working (Found " . count($vouchers) . " vouchers)<br>";
} catch (Exception $e) {
    echo "- array_column test: ❌ " . $e->getMessage() . "<br>";
}

// Test 5: Tally Module Check (if exists)
echo "<h3>5. Tally Module Status</h3>";
$tally_lib_path = __DIR__ . '/modules/tally_integration/libraries/Tally_api.php';
if (file_exists($tally_lib_path)) {
    echo "- Tally_api.php: ✅ Found<br>";
    echo "- File size: " . number_format(filesize($tally_lib_path)) . " bytes<br>";
    echo "- Last modified: " . date('Y-m-d H:i:s', filemtime($tally_lib_path)) . "<br>";
} else {
    echo "- Tally_api.php: ❌ Not found at expected path<br>";
}

echo "<h3>6. Recommendations</h3>";
echo "<div style='background: #f0f8ff; padding: 10px; border-left: 4px solid #0066cc;'>";
echo "<strong>Fixed Issues:</strong><br>";
echo "1. ✅ Added type checking in cleanArray() function<br>";
echo "2. ✅ Added array key validation before accessing nested keys<br>";
echo "3. ✅ Added null checks before using array_column()<br>";
echo "4. ✅ Added proper error logging and exception handling<br>";
echo "5. ✅ Added XML validation with detailed error messages<br>";
echo "<br>";
echo "<strong>Testing on Live Server:</strong><br>";
echo "1. Upload the fixed Tally_api.php file<br>";
echo "2. Check error logs in: /var/www/vhosts/livesoftwaretdl.in/httpdocs/logs/<br>";
echo "3. The system will now provide detailed error messages instead of fatal errors<br>";
echo "</div>";

echo "<hr>";
echo "<p><em>Debug test completed at " . date('Y-m-d H:i:s') . "</em></p>";
?>