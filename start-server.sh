#!/bin/bash

# Stop any existing PHP servers
echo "Stopping existing PHP servers..."
pkill -f "php.*artisan serve" 2>/dev/null
pkill -f "php.*server.php" 2>/dev/null
sleep 2

# Clear Laravel caches
echo "Clearing Laravel caches..."
php artisan cache:clear 2>/dev/null
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Clear framework cache files
echo "Clearing framework cache files..."
find storage/framework/cache -type f -delete 2>/dev/null
find storage/framework/views -type f -delete 2>/dev/null

# Check if port 8001 is free
if lsof -ti:8001 >/dev/null 2>&1; then
    echo "Port 8001 is still in use. Killing process..."
    lsof -ti:8001 | xargs kill -9 2>/dev/null
    sleep 2
fi

# Start the server with optimal settings
echo "Starting Laravel development server..."
php -d memory_limit=2G \
   -d max_execution_time=300 \
   -d max_input_time=300 \
   -d post_max_size=100M \
   -d upload_max_filesize=100M \
   -d max_file_uploads=20 \
   -d default_socket_timeout=300 \
   artisan serve --host=127.0.0.1 --port=8001

echo "Server started at http://127.0.0.1:8001" 