#!/bin/bash

# Laravel Development Server Startup Script
# This script safely starts the Laravel development server

echo "🚀 Starting Laravel Development Server..."

# Kill any existing PHP artisan serve processes
echo "🛑 Stopping existing PHP servers..."
pkill -f "php.*artisan serve" 2>/dev/null || true

# Kill any processes using port 8001
echo "🔌 Freeing port 8001..."
lsof -ti:8001 | xargs kill -9 2>/dev/null || true

# Wait a moment for processes to fully terminate
sleep 2

# Clear Laravel caches
echo "🧹 Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Check if port is available
if lsof -Pi :8001 -sTCP:LISTEN -t >/dev/null ; then
    echo "⚠️ Port 8001 is still in use. Trying port 8002..."
    PORT=8002
else
    PORT=8001
fi

echo "✅ Starting server on port $PORT with increased memory limits..."

# Start the server with proper settings
php -d memory_limit=2G \
   -d max_execution_time=300 \
   -d max_input_time=300 \
   -d post_max_size=100M \
   -d upload_max_filesize=100M \
   artisan serve --host=127.0.0.1 --port=$PORT

echo "🎉 Laravel server started on http://127.0.0.1:$PORT"
echo "Press Ctrl+C to stop the server" 