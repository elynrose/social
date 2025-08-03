#!/bin/bash

# Laravel Cloud Debug Script
# This script helps identify and fix deployment issues

echo "🔍 Starting Laravel Cloud debug process..."

# Check if we're in production
if [ "$APP_ENV" = "production" ]; then
    echo "🚀 Production environment detected"
    
    # Clear all caches
    echo "🧹 Clearing all caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    
    # Check application key
    echo "🔑 Checking application key..."
    if [ -z "$(php artisan config:show app.key | grep -v 'No value set')" ]; then
        echo "⚠️ Application key not set, generating..."
        php artisan key:generate --force
    fi
    
    # Check database connection
    echo "🗄️ Testing database connection..."
    php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'Database connection OK'; } catch (Exception \$e) { echo 'Database connection failed: ' . \$e->getMessage(); }"
    
    # Check migration status
    echo "📊 Checking migration status..."
    php artisan migrate:status
    
    # Check for any pending migrations
    echo "🔄 Running any pending migrations..."
    php artisan migrate --force --no-interaction || {
        echo "⚠️ Some migrations may have failed"
    }
    
    # Check storage permissions
    echo "📁 Checking storage permissions..."
    ls -la storage/
    ls -la bootstrap/cache/
    
    # Test basic Laravel functionality
    echo "🧪 Testing basic Laravel functionality..."
    php artisan --version
    php artisan route:list --compact
    
    # Check for any errors in logs
    echo "📋 Checking recent log entries..."
    if [ -f "storage/logs/laravel.log" ]; then
        tail -n 20 storage/logs/laravel.log
    else
        echo "No log file found"
    fi
    
else
    echo "🛠️ Development environment detected"
    
    # For development, run basic checks
    php artisan --version
    php artisan route:list --compact
fi

echo "✅ Laravel Cloud debug process complete!" 