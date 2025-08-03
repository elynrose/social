# Laravel Cloud Deployment Guide

This guide will help you deploy your Social Media Management Platform to Laravel Cloud and resolve the common `bootstrap/cache` directory error.

## 🚀 Quick Fix for Bootstrap/Cache Error

The error you're seeing is because Laravel Cloud needs certain directories to exist and be writable. We've already created these directories locally, but you need to ensure they're included in your Git repository.

### Step 1: Commit the Deployment Directories

```bash
git add .
git commit -m "Add Laravel Cloud deployment directories and configuration"
git push origin main
```

### Step 2: Laravel Cloud Environment Variables

In your Laravel Cloud dashboard, set these environment variables:

```env
APP_NAME="Social Media OS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.laravelcloud.com
APP_KEY=base64:your-generated-key

DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=your-database-name
DB_USERNAME=your-database-username
DB_PASSWORD=your-database-password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

LOG_CHANNEL=stack
LOG_LEVEL=debug

MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your-mail-username
MAIL_PASSWORD=your-mail-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Social Media OS"
```

### Step 3: Laravel Cloud Build Commands

In your Laravel Cloud dashboard, set these build commands:

```bash
# Pre-build commands
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/public

# Set permissions
chmod -R 755 bootstrap/cache
chmod -R 755 storage
chmod -R 775 storage/framework/cache
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/views
chmod -R 775 storage/logs

# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate application key (if not set)
php artisan key:generate --force

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize for production
php artisan optimize
```

### Step 4: Laravel Cloud Deploy Commands

Set these deploy commands in your Laravel Cloud dashboard:

```bash
# Clear any existing caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Re-cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize
php artisan optimize

# Restart queue workers
php artisan queue:restart
```

## 📁 Required Directory Structure

Your repository now includes these directories with `.gitkeep` files:

```
bootstrap/
├── cache/
│   └── .gitkeep

storage/
├── framework/
│   ├── cache/
│   │   └── .gitkeep
│   ├── sessions/
│   │   └── .gitkeep
│   └── views/
│       └── .gitkeep
├── logs/
│   └── .gitkeep
└── app/
    └── public/
```

## 🔧 Troubleshooting

### If you still get the bootstrap/cache error:

1. **Check if directories exist in Git:**
   ```bash
   git ls-files | grep -E "(bootstrap/cache|storage/framework)"
   ```

2. **Force create directories on Laravel Cloud:**
   Add this to your build commands:
   ```bash
   mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
   chmod -R 775 bootstrap/cache storage/framework
   ```

3. **Check Laravel Cloud logs:**
   - Go to your Laravel Cloud dashboard
   - Check the deployment logs for specific error messages
   - Look for permission or directory creation issues

### Common Laravel Cloud Issues:

1. **Permission Denied:**
   - Make sure your build commands include proper `chmod` commands
   - Laravel Cloud runs as a specific user, so permissions matter

2. **Missing Environment Variables:**
   - Double-check all required environment variables are set
   - Use the `laravel-cloud-config.php` file as a reference

3. **Database Connection Issues:**
   - Verify your database credentials in Laravel Cloud
   - Make sure the database is accessible from Laravel Cloud

## 🎯 Success Checklist

- [ ] All required directories are committed to Git
- [ ] Environment variables are set in Laravel Cloud
- [ ] Build commands include directory creation and permissions
- [ ] Deploy commands include cache optimization
- [ ] Database migrations run successfully
- [ ] Application key is generated
- [ ] Storage link is created
- [ ] All caches are optimized for production

## 📞 Support

If you continue to have issues:

1. Check the Laravel Cloud documentation
2. Review the deployment logs in your Laravel Cloud dashboard
3. Ensure all files from this repository are properly committed and pushed
4. Verify your Laravel Cloud plan supports the features you're using

## 🚀 Next Steps After Deployment

1. **Test your application** at your Laravel Cloud URL
2. **Set up your database** with sample data
3. **Configure your domain** (if using a custom domain)
4. **Set up monitoring** and error tracking
5. **Configure backups** for your database
6. **Set up SSL certificates** (usually automatic with Laravel Cloud)

Your Social Media Management Platform should now deploy successfully to Laravel Cloud! 🎉 