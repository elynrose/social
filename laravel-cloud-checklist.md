# Laravel Cloud Deployment Checklist

## 🔧 Pre-Deployment Setup

### 1. Environment Variables
- [ ] `APP_KEY` is set (run `php artisan key:generate`)
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] Database connection variables are set
- [ ] Cache and session drivers configured

### 2. Directory Structure
- [ ] Run `./create-directories.sh` to create required directories
- [ ] Verify storage directories exist and are writable
- [ ] Check bootstrap/cache permissions

### 3. Database Setup
- [ ] Run `php artisan migrate --force`
- [ ] Verify all tables are created
- [ ] Check for any migration errors

## 🚀 Deployment Steps

### 1. Create Directories
```bash
chmod +x create-directories.sh
./create-directories.sh
```

### 2. Set Permissions
```bash
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

### 3. Clear and Cache
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 4. Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 5. Create Storage Link
```bash
php artisan storage:link
```

## 🔍 Testing

### 1. Test Permissions
```bash
php test-permissions.php
# or
php artisan test:permissions
```

### 2. Test Application
- [ ] Homepage loads without 500 errors
- [ ] Login/registration works
- [ ] Database operations work
- [ ] File uploads work

## 🐛 Common Issues

### 500 Errors
- Check APP_KEY is set
- Verify storage directories exist and are writable
- Check database connection
- Review Laravel logs

### Permission Issues
- Run `./fix-permissions.sh`
- Ensure directories have 777 permissions
- Check web server user permissions

### Database Issues
- Verify database credentials
- Check if migrations ran successfully
- Ensure database server is accessible

## 📞 Support

If issues persist:
1. Check Laravel Cloud logs
2. Run debug scripts
3. Verify environment configuration
4. Contact Laravel Cloud support 