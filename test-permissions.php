<?php

echo "🔍 Testing Laravel Cloud Permissions\n";

// Define paths directly
$basePath = __DIR__;
$storagePath = $basePath . '/storage';
$bootstrapPath = $basePath . '/bootstrap/cache';

$content = 'Test write at ' . date('Y-m-d H:i:s');

// Function to ensure directory exists and is writable
function ensureDirectory($path) {
    if (!is_dir($path)) {
        if (mkdir($path, 0777, true)) {
            echo "📁 Created directory: $path\n";
        } else {
            echo "❌ Failed to create directory: $path\n";
            return false;
        }
    }
    return true;
}

// Test all storage directories
$testDirs = [
    $storagePath . '/framework/cache',
    $storagePath . '/framework/sessions',
    $storagePath . '/framework/views',
    $storagePath . '/logs',
    $bootstrapPath
];

foreach ($testDirs as $dir) {
    $relativePath = str_replace($basePath . '/', '', $dir);
    $testFile = $dir . '/test-permissions.txt';
    
    try {
        if (ensureDirectory($dir)) {
            file_put_contents($testFile, $content);
            echo "✅ Can write to $relativePath\n";
            unlink($testFile);
        }
    } catch (Exception $e) {
        echo "❌ Cannot write to $relativePath: " . $e->getMessage() . "\n";
    }
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