<?php

echo "🔍 Testing Laravel Cloud Permissions\n";

// Define paths directly
$basePath = __DIR__;
$storagePath = $basePath . '/storage';
$bootstrapPath = $basePath . '/bootstrap/cache';

$content = 'Test write at ' . date('Y-m-d H:i:s');

// Test if we can write to storage
$testFile = $storagePath . '/framework/cache/test-permissions.txt';
try {
    file_put_contents($testFile, $content);
    echo "✅ Can write to storage/framework/cache\n";
    unlink($testFile);
} catch (Exception $e) {
    echo "❌ Cannot write to storage/framework/cache: " . $e->getMessage() . "\n";
}

// Test if we can write to bootstrap/cache
$testFile = $bootstrapPath . '/test-permissions.txt';
try {
    file_put_contents($testFile, $content);
    echo "✅ Can write to bootstrap/cache\n";
    unlink($testFile);
} catch (Exception $e) {
    echo "❌ Cannot write to bootstrap/cache: " . $e->getMessage() . "\n";
}

// Test if we can write to logs
$testFile = $storagePath . '/logs/test-permissions.log';
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
    $storagePath . '/framework/cache',
    $storagePath . '/framework/sessions',
    $storagePath . '/framework/views',
    $storagePath . '/logs',
    $bootstrapPath
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