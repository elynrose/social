<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [
    /*
     * Application Name
     *
     * This value is the name of your application. This value is used when the
     * framework needs to place the application in the environment file.
     */
    'name' => env('APP_NAME', 'Laravel'),

    /*
     * Application Environment
     *
     * This value determines the "environment" your application is currently
     * running in. This may determine how your application configures itself
     * and its dependencies. Set this in your ".env" file.
     */
    'env' => env('APP_ENV', 'production'),

    /*
     * Application Debug Mode
     *
     * When your application is in debug mode, detailed error messages with
     * stack traces will be shown on every error that occurs within your
     * application. We recommend adjusting this for production environments.
     */
    'debug' => (bool) env('APP_DEBUG', false),

    /*
     * Application URL
     *
     * This URL is used by the console to properly generate URLs when using
     * the Artisan command line tool. You should set this to the root of
     * your application so that it is used when running Artisan tasks.
     */
    'url' => env('APP_URL'),

    /*
     * Application Timezone
     *
     * Here you may specify the default timezone for your application, which
     * will be used by the PHP date and date-time functions. We have gone
     * ahead and set the default timezone to UTC for you.
     */
    'timezone' => 'UTC',

    /*
     * Application Locale Configuration
     *
     * The application locale determines the default locale that will be used
     * by the translation files within the application. You may change the
     * locale by setting the "locale" key in the "config/app.php" file.
     */
    'locale' => 'en',

    /*
     * Application Fallback Locale
     *
     * This locale will be used when the application is unable to detect the
     * user's locale. You may change the value to correspond to any of the
     * language folders that are inside the "resources/lang" directory.
     */
    'fallback_locale' => 'en',

    /*
     * Faker Locale
     *
     * This locale will be used to generate localized fake data for your
     * database seeds. For example, you can use Spanish for Spanish data
     * or Turkish for Turkish data.
     */
    'faker_locale' => 'en_US',

    /*
     * Application Providers
     *
     * This array defines the providers that are available within your
     * application.
     */
    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\FortifyServiceProvider::class,
    ])->toArray(),

    /*
     * Application Aliases
     *
     * This array defines the aliases that are available within your
     * application.
     */
    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
    ])->toArray(),

    /*
     * Application Middleware
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    'middleware' => [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\HttpsRedirect::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ],
]; 