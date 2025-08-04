<?php
header('Content-Type: text/plain');
echo "🔍 OAuth Configuration Check\n";
echo "===========================\n\n";

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "✅ Laravel application loaded\n\n";
    
    // Check if we have a current tenant
    try {
        $tenant = app('currentTenant');
        echo "✅ Current tenant: " . $tenant->name . " (ID: " . $tenant->id . ")\n";
    } catch (Exception $e) {
        echo "❌ No current tenant found\n";
        echo "Error: " . $e->getMessage() . "\n";
        return;
    }
    
    // Check API configurations
    echo "\n📋 API Configurations:\n";
    $configs = \App\Models\ApiConfiguration::where('tenant_id', $tenant->id)->get();
    
    if ($configs->isEmpty()) {
        echo "❌ No API configurations found for this tenant\n";
        echo "\n🔧 To fix this:\n";
        echo "1. Go to /admin/api-configurations\n";
        echo "2. Create a new API configuration for Facebook\n";
        echo "3. Add your Facebook App credentials\n";
        echo "4. Set the platform to 'facebook'\n";
        echo "5. Make sure is_active is set to true\n";
    } else {
        foreach ($configs as $config) {
            $status = $config->is_active ? '✅' : '❌';
            $configured = $config->isConfigured() ? 'Configured' : 'Not Configured';
            echo "$status {$config->platform} - $configured\n";
        }
    }
    
    // Check social accounts
    echo "\n📱 Social Accounts:\n";
    $accounts = \App\Models\SocialAccount::where('tenant_id', $tenant->id)->get();
    
    if ($accounts->isEmpty()) {
        echo "❌ No social accounts connected\n";
    } else {
        foreach ($accounts as $account) {
            $status = $account->is_active ? '✅' : '❌';
            echo "$status {$account->platform} - {$account->username}\n";
        }
    }
    
    // Test OAuth redirect for Facebook
    echo "\n🧪 Testing Facebook OAuth:\n";
    try {
        $config = \App\Models\ApiConfiguration::where('tenant_id', $tenant->id)
            ->where('platform', 'facebook')
            ->where('is_active', true)
            ->first();
            
        if (!$config) {
            echo "❌ Facebook API configuration not found\n";
        } elseif (!$config->isConfigured()) {
            echo "❌ Facebook API configuration incomplete\n";
        } else {
            echo "✅ Facebook API configuration found and complete\n";
            echo "   Client ID: " . substr($config->client_id, 0, 10) . "...\n";
            echo "   Redirect URI: " . $config->redirect_uri . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Error checking Facebook configuration: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎯 Next Steps:\n";
    echo "1. Visit /admin/api-configurations to configure Facebook\n";
    echo "2. Add your Facebook App credentials\n";
    echo "3. Test the configuration\n";
    echo "4. Try connecting Facebook again\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} 