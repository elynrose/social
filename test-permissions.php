<?php

echo "🔍 Testing Laravel Cloud Permissions\n";

// Test if we can write to storage
$testFile = storage_path('framework/cache/test-permissions.txt');
$content = 'Test write at ' . date('Y-m-d H:i:s');

try {
    file_put_contents($testFile, $content);
    echo "✅ Can write to storage/framework/cache\n";
    unlink($testFile);
} catch (Exception $e) {
    echo "❌ Cannot write to storage/framework/cache: " . $e->getMessage() . "\n";
}

// Test if we can write to bootstrap/cache
$testFile = base_path('bootstrap/cache/test-permissions.txt');
try {
    file_put_contents($testFile, $content);
    echo "✅ Can write to bootstrap/cache\n";
    unlink($testFile);
} catch (Exception $e) {
    echo "❌ Cannot write to bootstrap/cache: " . $e->getMessage() . "\n";
}

// Test if we can write to logs
$testFile = storage_path('logs/test-permissions.log');
try {
    file_put_contents($testFile, $content);
    echo "✅ Can write to storage/logs\n";
    unlink($testFile);
} catch (Exception $e) {
    echo "❌ Cannot write to storage/logs: " . $e->getMessage() . "\n";
}

// Check directory permissions
echo "\n📁 Directory Permissions:\n";
$dirs = [
    storage_path('framework/cache'),
    storage_path('framework/sessions'),
    storage_path('framework/views'),
    storage_path('logs'),
    base_path('bootstrap/cache')
];

foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? '✅' : '❌';
        echo "$writable $dir ($perms)\n";
    } else {
        echo "❌ $dir (does not exist)\n";
    }
}

echo "\n✅ Permission test completed!\n"; 