#!/bin/bash

echo "🚀 Laravel Cloud Deployment Script"

# Create all required storage directories
echo "📁 Creating storage directories..."
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions  
mkdir -p storage/framework/views
mkdir -p storage/framework/testing
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# Set more permissive permissions for Laravel Cloud
echo "🔐 Setting permissions for Laravel Cloud..."
chmod -R 777 storage
chmod -R 777 bootstrap/cache

# Make sure the web server can write to these directories
echo "👤 Setting ownership (if possible)..."
if command -v chown &> /dev/null; then
    chown -R www-data:www-data storage 2>/dev/null || true
    chown -R www-data:www-data bootstrap/cache 2>/dev/null || true
fi

# Clear all caches first
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

# Final permission check
echo "🔍 Final permission check..."
ls -la storage/framework/
ls -la bootstrap/cache/

echo "✅ Laravel Cloud deployment completed!" 