#!/bin/bash

echo "🔧 Laravel Cloud Permissions Fix Script"

# Stop on any error
set -e

echo "📁 Creating directories with proper permissions..."
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/testing
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p bootstrap/cache

echo "🔐 Setting 777 permissions for Laravel Cloud..."
chmod -R 777 storage
chmod -R 777 bootstrap/cache

echo "👤 Attempting to set ownership..."
# Try to set ownership to web server user
if command -v chown &> /dev/null; then
    chown -R www-data:www-data storage 2>/dev/null || echo "Could not set ownership to www-data"
    chown -R www-data:www-data bootstrap/cache 2>/dev/null || echo "Could not set ownership to www-data"
else
    echo "chown command not available"
fi

echo "🧹 Clearing all caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo "🔗 Creating storage link..."
php artisan storage:link

echo "🔍 Verifying permissions..."
echo "Storage framework permissions:"
ls -la storage/framework/
echo "Bootstrap cache permissions:"
ls -la bootstrap/cache/

echo "✅ Permissions fix completed!" 