<?php

echo "🔑 Checking Laravel Cloud APP_KEY\n";

// Load environment variables
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    $lines = explode("\n", $envContent);
    
    foreach ($lines as $line) {
        if (strpos($line, 'APP_KEY=') === 0) {
            $appKey = trim(substr($line, 8));
            if ($appKey && $appKey !== 'null' && $appKey !== '') {
                echo "✅ APP_KEY is set: " . substr($appKey, 0, 10) . "...\n";
            } else {
                echo "❌ APP_KEY is not set or is empty\n";
                echo "Run: php artisan key:generate\n";
            }
            break;
        }
    }
} else {
    echo "⚠️ .env file not found\n";
    echo "APP_KEY should be set in environment variables\n";
}

// Check if we can access environment variables
if (isset($_ENV['APP_KEY'])) {
    echo "✅ APP_KEY from environment: " . substr($_ENV['APP_KEY'], 0, 10) . "...\n";
} else {
    echo "❌ APP_KEY not found in environment variables\n";
}

echo "\n✅ APP_KEY check completed!\n"; 