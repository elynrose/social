<?php

echo "🔍 Laravel Cloud Deployment Verification\n";

// Load environment variables
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    $lines = explode("\n", $envContent);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                $value = substr($value, 1, -1);
            }
            
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
}

try {
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "✅ Laravel application bootstrapped\n";
    
    // Test 1: Environment Variables
    echo "\n📋 Test 1: Environment Variables\n";
    $criticalVars = ['APP_KEY', 'APP_ENV', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE'];
    foreach ($criticalVars as $var) {
        $value = config("app.$var") ?? $_ENV[$var] ?? $_SERVER[$var] ?? getenv($var);
        if ($value) {
            echo "✅ $var: " . (strlen($value) > 20 ? substr($value, 0, 20) . "..." : $value) . "\n";
        } else {
            echo "❌ $var: NOT SET\n";
        }
    }
    
    // Test 2: Database Connection
    echo "\n🗄️ Test 2: Database Connection\n";
    try {
        $db = app('db');
        $db->connection()->getPdo();
        echo "✅ Database connection successful\n";
        
        // Test a simple query
        $result = $db->select('SELECT 1 as test');
        echo "✅ Database query successful\n";
    } catch (Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    }
    
    // Test 3: Storage Directories
    echo "\n📁 Test 3: Storage Directories\n";
    $dirs = [
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'bootstrap/cache'
    ];
    
    foreach ($dirs as $dir) {
        $fullPath = __DIR__ . '/' . $dir;
        if (is_dir($fullPath)) {
            $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
            $writable = is_writable($fullPath) ? '✅' : '❌';
            echo "$writable $dir ($perms)\n";
        } else {
            echo "❌ $dir (does not exist)\n";
        }
    }
    
    // Test 4: Session Functionality
    echo "\n🔄 Test 4: Session Functionality\n";
    try {
        session_start();
        $_SESSION['test'] = 'deployment_verification';
        if (isset($_SESSION['test'])) {
            echo "✅ Session write/read working\n";
        } else {
            echo "❌ Session write/read failed\n";
        }
        session_destroy();
    } catch (Exception $e) {
        echo "❌ Session test failed: " . $e->getMessage() . "\n";
    }
    
    // Test 5: Cache Functionality
    echo "\n💾 Test 5: Cache Functionality\n";
    try {
        $cache = app('cache');
        $cache->put('test_key', 'test_value', 60);
        $value = $cache->get('test_key');
        if ($value === 'test_value') {
            echo "✅ Cache write/read working\n";
        } else {
            echo "❌ Cache write/read failed\n";
        }
        $cache->forget('test_key');
    } catch (Exception $e) {
        echo "❌ Cache test failed: " . $e->getMessage() . "\n";
    }
    
    // Test 6: Configuration
    echo "\n⚙️ Test 6: Configuration\n";
    $configs = [
        'app.env' => 'production',
        'app.debug' => false,
        'session.driver' => 'file',
        'cache.default' => 'file'
    ];
    
    foreach ($configs as $key => $expected) {
        $value = config($key);
        if ($value == $expected) {
            echo "✅ $key: $value\n";
        } else {
            echo "⚠️ $key: $value (expected: $expected)\n";
        }
    }
    
    echo "\n🎉 Deployment verification completed!\n";
    echo "If all tests pass, your application should be working correctly.\n";
    
} catch (Exception $e) {
    echo "❌ Verification failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} 