<?php

echo "🔧 Laravel Cloud Environment Fix Script\n";

// Check if .env file exists
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "✅ .env file exists\n";
    
    // Load environment variables manually
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
    
    echo "📋 Loaded environment variables from .env\n";
} else {
    echo "❌ .env file not found\n";
}

// Check critical environment variables
$criticalVars = [
    'APP_KEY',
    'APP_ENV',
    'APP_DEBUG',
    'DB_CONNECTION',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD'
];

echo "\n🔍 Checking Critical Environment Variables:\n";
foreach ($criticalVars as $var) {
    $value = $_ENV[$var] ?? $_SERVER[$var] ?? getenv($var);
    if ($value) {
        if ($var === 'APP_KEY') {
            echo "✅ $var: " . substr($value, 0, 10) . "...\n";
        } elseif ($var === 'DB_PASSWORD') {
            echo "✅ $var: [HIDDEN]\n";
        } else {
            echo "✅ $var: $value\n";
        }
    } else {
        echo "❌ $var: NOT SET\n";
    }
}

// Test Laravel application bootstrap
echo "\n🚀 Testing Laravel Bootstrap:\n";
try {
    // Load Composer autoloader
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Bootstrap Laravel
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
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
}

echo "\n✅ Environment check completed!\n"; 