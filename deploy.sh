#!/bin/bash

# Production Deployment Script for Social Media OS
# Run this script on your production server

set -e

echo "🚀 Starting production deployment..."

# 1. Pull latest changes
echo "📥 Pulling latest changes..."
git pull origin main

# 2. Install/update dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# 3. Set production environment
echo "⚙️ Setting production environment..."
export APP_ENV=production
export APP_DEBUG=false

# 4. Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 5. Clear and cache configurations
echo "⚡ Optimizing for production..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# 6. Create storage link if not exists
echo "📁 Setting up storage..."
php artisan storage:link

# 7. Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 8. Restart queue workers
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# 9. Create database backup
echo "💾 Creating database backup..."
php artisan backup:database

# 10. Run tests (optional - remove in production if not needed)
echo "🧪 Running tests..."
php artisan test --stop-on-failure

# 11. Clear application cache
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan optimize:clear

# 12. Optimize for production
echo "🚀 Final optimization..."
php artisan optimize

echo "✅ Deployment completed successfully!"
echo "🌐 Your application is now live at: $(php artisan config:show app.url)"
echo "📊 Monitor your application with: php artisan horizon" 