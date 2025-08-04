<?php
header('Content-Type: text/plain');
echo "🔍 Laravel Cloud Diagnostic Report\n";
echo "================================\n\n";

// Test 1: Basic PHP
echo "✅ PHP Version: " . PHP_VERSION . "\n";
echo "✅ Current Directory: " . __DIR__ . "\n";

// Test 2: File System
echo "\n📁 File System Check:\n";
$criticalFiles = [
    '../.env',
    '../vendor/autoload.php',
    '../bootstrap/app.php',
    '../storage/framework/cache',
    '../storage/framework/sessions',
    '../storage/framework/views',
    '../storage/logs',
    '../bootstrap/cache'
];

foreach ($criticalFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        $writable = is_writable($fullPath) ? '✅' : '❌';
        echo "$writable $file ($perms)\n";
    } else {
        echo "❌ $file (missing)\n";
    }
}

// Test 3: Environment Variables
echo "\n🔧 Environment Variables:\n";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    echo "✅ .env file exists\n";
    $envContent = file_get_contents($envFile);
    $lines = explode("\n", $envContent);
    
    $criticalVars = ['APP_KEY', 'APP_ENV', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE'];
    foreach ($criticalVars as $var) {
        foreach ($lines as $line) {
            if (strpos($line, $var . '=') === 0) {
                $value = trim(substr($line, strlen($var) + 1));
                if (strlen($value) > 20) {
                    $value = substr($value, 0, 20) . "...";
                }
                echo "✅ $var: $value\n";
                break;
            }
        }
    }
} else {
    echo "❌ .env file missing\n";
}

// Test 4: Composer
echo "\n📦 Composer Check:\n";
$autoloadFile = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadFile)) {
    echo "✅ vendor/autoload.php exists\n";
} else {
    echo "❌ vendor/autoload.php missing\n";
}

// Test 5: Laravel Bootstrap
echo "\n🚀 Laravel Bootstrap Test:\n";
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "✅ Composer autoloader loaded\n";
    
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "✅ Laravel app created\n";
    
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    echo "✅ Kernel created\n";
    
    $kernel->bootstrap();
    echo "✅ Laravel application bootstrapped successfully\n";
    
    // Test configuration
    $appKey = config('app.key');
    if ($appKey) {
        echo "✅ APP_KEY from config: " . substr($appKey, 0, 10) . "...\n";
    } else {
        echo "❌ APP_KEY not found in config\n";
    }
    
} catch (Exception $e) {
    echo "❌ Laravel bootstrap failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

// Test 6: Database
echo "\n🗄️ Database Test:\n";
try {
    $db = app('db');
    $db->connection()->getPdo();
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test 7: Session
echo "\n🔄 Session Test:\n";
try {
    session_start();
    $_SESSION['test'] = 'diagnostic';
    if (isset($_SESSION['test'])) {
        echo "✅ Session write/read working\n";
    } else {
        echo "❌ Session write/read failed\n";
    }
    session_destroy();
} catch (Exception $e) {
    echo "❌ Session test failed: " . $e->getMessage() . "\n";
}

echo "\n🎉 Diagnostic completed!\n";
echo "Check the results above to identify the issue.\n"; 