#!/bin/bash

echo "🚀 Starting deployment..."

# Set environment
export APP_ENV=production

# Create required directories if they don't exist
echo "📁 Creating storage directories..."
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/testing
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Clear and cache configuration
echo "⚙️ Optimizing configuration..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Run database migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Clear application cache
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan optimize:clear
php artisan optimize

# Create storage link if it doesn't exist
echo "🔗 Creating storage link..."
php artisan storage:link

echo "✅ Deployment completed successfully!" 