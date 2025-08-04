<?php

echo "🔑 Laravel Cloud APP_KEY Generator\n";

// Generate a new APP_KEY
$appKey = 'base64:' . base64_encode(random_bytes(32));

echo "Generated APP_KEY: $appKey\n";

// Check if .env file exists
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "\n📝 Updating .env file...\n";
    
    $envContent = file_get_contents($envFile);
    
    // Replace existing APP_KEY or add new one
    if (strpos($envContent, 'APP_KEY=') !== false) {
        $envContent = preg_replace('/APP_KEY=.*/', "APP_KEY=$appKey", $envContent);
        echo "✅ Updated existing APP_KEY in .env\n";
    } else {
        $envContent .= "\nAPP_KEY=$appKey\n";
        echo "✅ Added new APP_KEY to .env\n";
    }
    
    file_put_contents($envFile, $envContent);
    echo "✅ .env file updated successfully\n";
} else {
    echo "\n❌ .env file not found\n";
    echo "Please create .env file with the following line:\n";
    echo "APP_KEY=$appKey\n";
}

// Set environment variable
putenv("APP_KEY=$appKey");
$_ENV['APP_KEY'] = $appKey;
$_SERVER['APP_KEY'] = $appKey;

echo "\n✅ APP_KEY generated and set!\n";
echo "You can now restart your application.\n"; 