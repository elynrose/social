# Laravel Cloud Deployment Guide

This guide will help you deploy your Social Media Management Platform to Laravel Cloud and resolve all common deployment issues including PostgreSQL duplicate table errors.

## 🚀 Comprehensive Fix for All PostgreSQL Issues

The errors you're seeing are because Laravel Cloud is trying to create tables that already exist in your PostgreSQL database. We've updated ALL migrations to handle this gracefully.

### Step 1: Updated Build Commands for Laravel Cloud

In your Laravel Cloud dashboard, use these updated build commands:

```bash
# Create required directories
mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs

# Set permissions
chmod -R 755 bootstrap/cache storage
chmod -R 775 storage/framework/cache storage/framework/sessions storage/framework/views storage/logs

# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate --force

# Clear caches before migration
php artisan config:clear
php artisan cache:clear

# Use the comprehensive safe migration script
chmod +x laravel-cloud-safe-migrate.sh
./laravel-cloud-safe-migrate.sh

# Create storage link
php artisan storage:link

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize
php artisan optimize
```

### Step 2: Laravel Cloud Environment Variables

Set these environment variables in your Laravel Cloud dashboard:

```env
APP_NAME="Social Media OS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.laravelcloud.com
APP_KEY=base64:your-generated-key

# Database Configuration (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=your-postgresql-host
DB_PORT=5432
DB_DATABASE=your-database-name
DB_USERNAME=your-database-username
DB_PASSWORD=your-database-password

# Cache and Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your-mail-username
MAIL_PASSWORD=your-mail-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Social Media OS"
```

## 🔧 Comprehensive Migration Fixes

### Updated ALL Migrations with Safety Checks

We've updated ALL table creation migrations to include safety checks:

1. **Users Migration**: `Schema::hasTable('users')` check
2. **Tenants Migration**: `Schema::hasTable('tenants')` check
3. **Social Accounts Migration**: `Schema::hasTable('social_accounts')` check
4. **Posts Migration**: `Schema::hasTable('posts')` check
5. **Campaigns Migration**: `Schema::hasTable('campaigns')` check
6. **Scheduled Posts Migration**: `Schema::hasTable('scheduled_posts')` check
7. **Approvals Migration**: `Schema::hasTable('approvals')` check
8. **Mentions Migration**: `Schema::hasTable('mentions')` check
9. **Engagements Migration**: `Schema::hasTable('engagements')` check
10. **Analytics Reports Migration**: `Schema::hasTable('analytics_reports')` check
11. **Personal Access Tokens Migration**: `Schema::hasTable('personal_access_tokens')` check
12. **All other table migrations**: Safety checks added

### Foreign Key Handling

- **Posts Table**: Foreign keys added in separate migration after all tables exist
- **All Foreign Keys**: Properly ordered to prevent dependency issues

### Column Addition Safety

- **Status Column**: `Schema::hasColumn()` check to prevent duplicate column errors
- **All Column Additions**: Safety checks implemented

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

### If you still get PostgreSQL errors:

1. **Use the Safe Migration Script:**
   ```bash
   chmod +x laravel-cloud-safe-migrate.sh
   ./laravel-cloud-safe-migrate.sh
   ```

2. **Check Migration Status:**
   ```bash
   php artisan migrate:status
   ```

3. **Manual Database Reset (if needed):**
   ```sql
   -- Connect to your PostgreSQL database and run:
   DROP SCHEMA public CASCADE;
   CREATE SCHEMA public;
   GRANT ALL ON SCHEMA public TO your_username;
   GRANT ALL ON SCHEMA public TO public;
   ```

4. **Check PostgreSQL Logs:**
   - Connect to your PostgreSQL database
   - Check for any constraint violations or permission issues

### Common PostgreSQL Issues:

1. **Permission Denied:**
   - Make sure your database user has CREATE, INSERT, UPDATE, DELETE permissions
   - Check if the database exists and is accessible

2. **Connection Issues:**
   - Verify your database host, port, and credentials
   - Ensure your Laravel Cloud app can reach the PostgreSQL server

3. **Schema Issues:**
   - Make sure you're using the correct schema (usually 'public')
   - Check for any existing tables that might conflict

## 🎯 Success Checklist

- [ ] All required directories are committed to Git
- [ ] Environment variables are set in Laravel Cloud
- [ ] PostgreSQL connection details are correct
- [ ] Build commands include comprehensive safe migration strategy
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
4. Verify your PostgreSQL database is accessible from Laravel Cloud
5. Check your Laravel Cloud plan supports PostgreSQL

## 🚀 Next Steps After Deployment

1. **Test your application** at your Laravel Cloud URL
2. **Set up your database** with sample data
3. **Configure your domain** (if using a custom domain)
4. **Set up monitoring** and error tracking
5. **Configure backups** for your PostgreSQL database
6. **Set up SSL certificates** (usually automatic with Laravel Cloud)

Your Social Media Management Platform should now deploy successfully to Laravel Cloud with PostgreSQL! 🎉 