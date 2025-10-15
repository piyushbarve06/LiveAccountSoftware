<?php
/**
 * CLEANUP TALLY DEBUG - REMOVE DEBUG LOGGING
 * 
 * This script removes all the debug logging added to fix Tally import issues.
 * Use this once you've confirmed the phone extraction and customer matching fixes are working.
 */

echo "<h1>🧹 Cleanup Tally Debug Logging</h1>";

$tally_api_path = 'modules/tally_integration/libraries/Tally_api.php';

if (!file_exists($tally_api_path)) {
    echo "<p style='color: red;'>❌ Tally_api.php not found!</p>";
    exit;
}

echo "<p>✅ Found Tally_api.php</p>";

// Read the file
$content = file_get_contents($tally_api_path);
$original_content = $content;

// Count current debug statements
$debug_count = substr_count($content, 'error_log(');

echo "<p>📊 Found $debug_count debug statements to remove</p>";

if ($debug_count === 0) {
    echo "<p style='color: green;'>✅ No debug statements found - file is already clean!</p>";
    exit;
}

// Remove all error_log statements and their associated comments
$patterns_to_remove = [
    // Remove debug statements
    "/\s*\/\/\s*Debug.*\n/i",
    "/\s*error_log\([^;]*\);\s*\n/",
    "/\s*error_log\([^;]*\);\s*/",
];

foreach ($patterns_to_remove as $pattern) {
    $content = preg_replace($pattern, '', $content);
}

// Remove debug comment blocks
$content = preg_replace('/\s*\/\/ Debug[^\n]*\n/', "\n", $content);
$content = preg_replace('/\s*error_log\([^)]*\);\s*/', '', $content);

// Clean up extra whitespace
$content = preg_replace('/\n\n\n+/', "\n\n", $content);

// Count remaining debug statements
$remaining_debug = substr_count($content, 'error_log(');

if ($remaining_debug === 0) {
    // Create backup
    $backup_path = $tally_api_path . '.debug_backup.' . date('Y-m-d_H-i-s');
    if (copy($tally_api_path, $backup_path)) {
        echo "<p>✅ Created backup: $backup_path</p>";
    }
    
    // Write cleaned content
    if (file_put_contents($tally_api_path, $content)) {
        echo "<p style='color: green;'>✅ Successfully removed all $debug_count debug statements!</p>";
        echo "<p>📁 Original file backed up to: $backup_path</p>";
        
        // Also clean up debug log file
        $log_file = __DIR__ . '/tally_debug.log';
        if (file_exists($log_file)) {
            if (unlink($log_file)) {
                echo "<p>✅ Removed debug log file</p>";
            }
        }
        
        echo "<h2>🎉 Cleanup Complete!</h2>";
        echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #0066cc;'>";
        echo "<p><strong>What was cleaned:</strong></p>";
        echo "<ul>";
        echo "<li>Removed $debug_count debug logging statements</li>";
        echo "<li>Cleaned up debug comments</li>";
        echo "<li>Removed debug log file</li>";
        echo "<li>Created backup of original file</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<p><strong>⚠️ Important:</strong> Test your Tally integration to make sure it still works correctly!</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to write cleaned file!</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Warning: $remaining_debug debug statements remain. Manual cleanup may be needed.</p>";
}

echo "<hr>";
echo "<p><em>Cleanup completed: " . date('Y-m-d H:i:s') . "</em></p>";
?>