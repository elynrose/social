#!/bin/bash

# Laravel Cloud Deployment Script
# This script ensures all necessary directories exist and have proper permissions

echo "🚀 Starting Laravel Cloud deployment setup..."

# Create necessary directories
echo "📁 Creating required directories..."
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/public

# Set proper permissions
echo "🔐 Setting directory permissions..."
chmod -R 755 bootstrap/cache
chmod -R 755 storage
chmod -R 755 storage/framework
chmod -R 755 storage/logs
chmod -R 755 storage/app

# Make sure the web server can write to these directories
chmod 775 bootstrap/cache
chmod 775 storage/framework/cache
chmod 775 storage/framework/sessions
chmod 775 storage/framework/views
chmod 775 storage/logs

# Clear any existing cache files
echo "🧹 Clearing existing cache files..."
find storage/framework/cache -type f -delete 2>/dev/null || true
find storage/framework/views -type f -delete 2>/dev/null || true
find bootstrap/cache -type f -delete 2>/dev/null || true

# Create .gitkeep files to ensure directories are tracked
echo "📝 Creating .gitkeep files..."
touch bootstrap/cache/.gitkeep
touch storage/framework/cache/.gitkeep
touch storage/framework/sessions/.gitkeep
touch storage/framework/views/.gitkeep
touch storage/logs/.gitkeep

echo "✅ Laravel Cloud deployment setup complete!"
echo "📋 Next steps:"
echo "   1. Commit these changes: git add . && git commit -m 'Add deployment directories'"
echo "   2. Push to your repository: git push origin main"
echo "   3. Deploy to Laravel Cloud" 