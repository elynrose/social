<?php

echo "🚀 Testing Laravel Application Startup\n";

// Load environment variables first
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
            
            // Remove quotes if present
            if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                $value = substr($value, 1, -1);
            }
            
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
    echo "✅ Environment variables loaded\n";
}

try {
    // Load Composer autoloader
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Composer autoloader loaded\n";
    
    // Bootstrap Laravel
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "✅ Laravel app created\n";
    
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    echo "✅ Kernel created\n";
    
    $kernel->bootstrap();
    echo "✅ Laravel application bootstrapped successfully\n";
    
    // Test basic configuration
    $appKey = config('app.key');
    if ($appKey) {
        echo "✅ APP_KEY from config: " . substr($appKey, 0, 10) . "...\n";
    } else {
        echo "❌ APP_KEY not found in config\n";
    }
    
    // Test database connection
    try {
        $db = app('db');
        $db->connection()->getPdo();
        echo "✅ Database connection successful\n";
    } catch (Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    }
    
    // Test session configuration
    $sessionDriver = config('session.driver');
    echo "✅ Session driver: $sessionDriver\n";
    
    // Test cache configuration
    $cacheDriver = config('cache.default');
    echo "✅ Cache driver: $cacheDriver\n";
    
    echo "\n✅ Laravel application is ready!\n";
    
} catch (Exception $e) {
    echo "❌ Laravel bootstrap failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} 