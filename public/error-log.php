<?php
header('Content-Type: text/plain');
echo "📋 Laravel Error Log Viewer\n";
echo "==========================\n\n";

$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    echo "✅ Laravel log file found\n";
    echo "📄 Last 50 lines of laravel.log:\n";
    echo "--------------------------------\n";
    
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    foreach ($lastLines as $line) {
        echo $line;
    }
} else {
    echo "❌ Laravel log file not found at: $logFile\n";
}

echo "\n📄 PHP Error Log:\n";
echo "----------------\n";
$phpLog = ini_get('error_log');
if ($phpLog && file_exists($phpLog)) {
    $lines = file($phpLog);
    $lastLines = array_slice($lines, -20);
    foreach ($lastLines as $line) {
        echo $line;
    }
} else {
    echo "❌ PHP error log not found or not configured\n";
}

echo "\n🔧 PHP Error Reporting:\n";
echo "----------------------\n";
echo "display_errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "\n";
echo "log_errors: " . (ini_get('log_errors') ? 'On' : 'Off') . "\n";
echo "error_log: " . ini_get('error_log') . "\n";
echo "error_reporting: " . ini_get('error_reporting') . "\n"; 