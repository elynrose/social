#!/bin/bash

echo "📁 Creating Laravel Cloud Directories"

# Create all required directories
echo "Creating storage directories..."
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/testing
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# Set permissions
echo "Setting permissions..."
chmod -R 777 storage
chmod -R 777 bootstrap/cache

# Verify directories exist
echo "Verifying directories..."
for dir in storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache; do
    if [ -d "$dir" ]; then
        echo "✅ $dir exists"
    else
        echo "❌ $dir missing"
    fi
done

echo "✅ Directory creation completed!" 