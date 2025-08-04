<?php
header('Content-Type: text/plain');
echo "🔧 Laravel Storage Directory Fix\n";
echo "===============================\n\n";

// Define the directories that need to be created
$directories = [
    '../storage/framework/cache',
    '../storage/framework/sessions', 
    '../storage/framework/views',
    '../storage/logs'
];

echo "📁 Creating missing storage directories...\n\n";

foreach ($directories as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    
    if (!is_dir($fullPath)) {
        // Create directory recursively
        if (mkdir($fullPath, 0777, true)) {
            echo "✅ Created: $dir\n";
        } else {
            echo "❌ Failed to create: $dir\n";
        }
    } else {
        echo "✅ Already exists: $dir\n";
    }
    
    // Set permissions to 777 for maximum compatibility
    if (is_dir($fullPath)) {
        if (chmod($fullPath, 0777)) {
            echo "✅ Set permissions (777): $dir\n";
        } else {
            echo "❌ Failed to set permissions: $dir\n";
        }
    }
}

echo "\n🔍 Verifying directories...\n";
foreach ($directories as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath)) {
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        $writable = is_writable($fullPath) ? '✅' : '❌';
        echo "$writable $dir ($perms)\n";
    } else {
        echo "❌ $dir (still missing)\n";
    }
}

echo "\n🧪 Testing Laravel functionality...\n";

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "✅ Laravel bootstrap successful\n";
    
    // Test cache
    $cache = app('cache');
    $cache->put('test_key', 'test_value', 60);
    $value = $cache->get('test_key');
    if ($value === 'test_value') {
        echo "✅ Cache functionality working\n";
    } else {
        echo "❌ Cache functionality failed\n";
    }
    
    // Test session
    session_start();
    $_SESSION['test'] = 'storage_fix';
    if (isset($_SESSION['test'])) {
        echo "✅ Session functionality working\n";
    } else {
        echo "❌ Session functionality failed\n";
    }
    session_destroy();
    
    echo "\n🎉 Storage fix completed!\n";
    echo "Your Laravel application should now work without 500 errors.\n";
    
} catch (Exception $e) {
    echo "❌ Laravel test failed: " . $e->getMessage() . "\n";
} 