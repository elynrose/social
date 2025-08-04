#!/bin/bash

echo "🔍 Laravel Cloud Debug Script"

# Check if APP_KEY is set
echo "🔑 Checking APP_KEY..."
if [ -z "$APP_KEY" ]; then
    echo "❌ APP_KEY is not set!"
    echo "Run: php artisan key:generate"
else
    echo "✅ APP_KEY is set"
fi

# Check database connection
echo "🗄️ Testing database connection..."
php artisan tinker --execute="try { DB::connection()->getPdo(); echo '✅ Database connection successful'; } catch (Exception \$e) { echo '❌ Database connection failed: ' . \$e->getMessage(); }"

# Check storage directories
echo "📁 Checking storage directories..."
for dir in storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache; do
    if [ -d "$dir" ]; then
        echo "✅ $dir exists"
    else
        echo "❌ $dir missing"
    fi
done

# Check permissions
echo "🔐 Checking permissions..."
ls -la storage/framework/
ls -la bootstrap/cache/

# Check if migrations can run
echo "🔄 Testing migrations..."
php artisan migrate:status

# Check if routes can be cached
echo "🛣️ Testing route caching..."
php artisan route:cache

echo "✅ Debug check completed!" 