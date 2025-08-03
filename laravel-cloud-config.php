<?php

/**
 * Laravel Cloud Deployment Configuration
 * 
 * This file contains configuration settings specifically for Laravel Cloud deployment.
 * Copy these settings to your .env file on Laravel Cloud.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel Cloud Environment Variables
    |--------------------------------------------------------------------------
    |
    | These are the recommended environment variables for Laravel Cloud deployment.
    | Make sure to set these in your Laravel Cloud dashboard.
    |
    */

    'app' => [
        'name' => env('APP_NAME', 'Social Media OS'),
        'env' => env('APP_ENV', 'production'),
        'debug' => env('APP_DEBUG', false),
        'url' => env('APP_URL', 'https://your-app-name.laravelcloud.com'),
        'timezone' => env('APP_TIMEZONE', 'UTC'),
        'locale' => env('APP_LOCALE', 'en'),
        'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
        'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),
        'key' => env('APP_KEY'),
        'cipher' => env('APP_CIPHER', 'AES-256-CBC'),
    ],

    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'laravel'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
    ],

    'cache' => [
        'driver' => env('CACHE_DRIVER', 'file'),
        'prefix' => env('CACHE_PREFIX', 'laravel_cache'),
    ],

    'session' => [
        'driver' => env('SESSION_DRIVER', 'file'),
        'lifetime' => env('SESSION_LIFETIME', 120),
        'expire_on_close' => false,
        'encrypt' => false,
        'files' => storage_path('framework/sessions'),
        'connection' => env('SESSION_CONNECTION'),
        'table' => 'sessions',
        'store' => env('SESSION_STORE'),
        'lottery' => [2, 100],
        'cookie' => env('SESSION_COOKIE', 'laravel_session'),
        'path' => '/',
        'domain' => env('SESSION_DOMAIN'),
        'secure' => env('SESSION_SECURE_COOKIE', true),
        'http_only' => env('SESSION_HTTP_ONLY', true),
        'same_site' => env('SESSION_SAME_SITE', 'lax'),
    ],

    'queue' => [
        'default' => env('QUEUE_CONNECTION', 'database'),
        'connections' => [
            'database' => [
                'driver' => 'database',
                'table' => 'jobs',
                'queue' => 'default',
                'retry_after' => 90,
                'after_commit' => false,
            ],
        ],
        'failed' => [
            'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
            'database' => env('DB_CONNECTION', 'mysql'),
            'table' => 'failed_jobs',
        ],
    ],

    'filesystem' => [
        'default' => env('FILESYSTEM_DISK', 'local'),
        'disks' => [
            'local' => [
                'driver' => 'local',
                'root' => storage_path('app'),
            ],
            'public' => [
                'driver' => 'local',
                'root' => storage_path('app/public'),
                'url' => env('APP_URL').'/storage',
                'visibility' => 'public',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Required Environment Variables for Laravel Cloud
    |--------------------------------------------------------------------------
    |
    | Make sure these are set in your Laravel Cloud dashboard:
    |
    | APP_NAME="Social Media OS"
    | APP_ENV=production
    | APP_DEBUG=false
    | APP_URL=https://your-app-name.laravelcloud.com
    | APP_KEY=base64:your-app-key-here
    | 
    | DB_CONNECTION=mysql
    | DB_HOST=your-database-host
    | DB_PORT=3306
    | DB_DATABASE=your-database-name
    | DB_USERNAME=your-database-username
    | DB_PASSWORD=your-database-password
    |
    | CACHE_DRIVER=file
    | SESSION_DRIVER=file
    | QUEUE_CONNECTION=database
    | FILESYSTEM_DISK=local
    |
    | LOG_CHANNEL=stack
    | LOG_LEVEL=debug
    |
    | MAIL_MAILER=smtp
    | MAIL_HOST=your-mail-host
    | MAIL_PORT=587
    | MAIL_USERNAME=your-mail-username
    | MAIL_PASSWORD=your-mail-password
    | MAIL_ENCRYPTION=tls
    | MAIL_FROM_ADDRESS=noreply@yourdomain.com
    | MAIL_FROM_NAME="${APP_NAME}"
    |
    */

    'deployment_checklist' => [
        'directories' => [
            'bootstrap/cache' => 'Must exist and be writable',
            'storage/framework/cache' => 'Must exist and be writable',
            'storage/framework/sessions' => 'Must exist and be writable',
            'storage/framework/views' => 'Must exist and be writable',
            'storage/logs' => 'Must exist and be writable',
            'storage/app/public' => 'Must exist and be writable',
        ],
        'commands' => [
            'php artisan key:generate' => 'Generate application key',
            'php artisan migrate --force' => 'Run database migrations',
            'php artisan storage:link' => 'Create storage symlink',
            'php artisan config:cache' => 'Cache configuration',
            'php artisan route:cache' => 'Cache routes',
            'php artisan view:cache' => 'Cache views',
            'php artisan optimize' => 'Optimize for production',
        ],
        'permissions' => [
            'bootstrap/cache' => '775',
            'storage' => '755',
            'storage/framework' => '755',
            'storage/logs' => '775',
        ],
    ],
]; 