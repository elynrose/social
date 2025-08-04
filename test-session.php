<?php

echo "🔍 Testing Laravel Cloud Session Functionality\n";

// Test if we can write to sessions directory
$sessionPath = __DIR__ . '/storage/framework/sessions';
$testFile = $sessionPath . '/test-session.txt';

try {
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0777, true);
        echo "📁 Created sessions directory\n";
    }
    
    file_put_contents($testFile, 'Session test at ' . date('Y-m-d H:i:s'));
    echo "✅ Can write to sessions directory\n";
    unlink($testFile);
} catch (Exception $e) {
    echo "❌ Cannot write to sessions directory: " . $e->getMessage() . "\n";
}

// Check session directory permissions
echo "\n📁 Session Directory Permissions:\n";
if (is_dir($sessionPath)) {
    $perms = substr(sprintf('%o', fileperms($sessionPath)), -4);
    $writable = is_writable($sessionPath) ? '✅' : '❌';
    echo "$writable $sessionPath ($perms)\n";
} else {
    echo "❌ Sessions directory does not exist\n";
}

// Test session functionality
echo "\n🔄 Testing Session Functionality:\n";
session_start();
$_SESSION['test'] = 'Laravel Cloud Session Test';
if (isset($_SESSION['test'])) {
    echo "✅ Session write/read working\n";
} else {
    echo "❌ Session write/read failed\n";
}
session_destroy();

echo "\n✅ Session test completed!\n"; 