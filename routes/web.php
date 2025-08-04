<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialAccountController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\WebhookController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect('/');
})->name('home');

Route::middleware(['auth', App\Http\Middleware\SetCurrentTenant::class])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    // Calendar routes
    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/create', [\App\Http\Controllers\CalendarController::class, 'create'])->name('calendar.create');
    Route::post('/calendar', [\App\Http\Controllers\CalendarController::class, 'store'])->name('calendar.store');
    Route::get('/calendar/{scheduledPost}/edit', [\App\Http\Controllers\CalendarController::class, 'edit'])->name('calendar.edit');
    Route::patch('/calendar/{scheduledPost}', [\App\Http\Controllers\CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{scheduledPost}', [\App\Http\Controllers\CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::get('/api/calendar/events', [\App\Http\Controllers\CalendarController::class, 'events'])->name('calendar.events');
    
    // Scheduler routes
    Route::get('/scheduler', [\App\Http\Controllers\SchedulerController::class, 'index'])->name('scheduler.index');
    Route::get('/scheduler/create', [\App\Http\Controllers\SchedulerController::class, 'create'])->name('scheduler.create');
    Route::post('/scheduler', [\App\Http\Controllers\SchedulerController::class, 'store'])->name('scheduler.store');
    Route::get('/scheduler/{scheduledPost}/edit', [\App\Http\Controllers\SchedulerController::class, 'edit'])->name('scheduler.edit');
    Route::patch('/scheduler/{scheduledPost}', [\App\Http\Controllers\SchedulerController::class, 'update'])->name('scheduler.update');
    Route::delete('/scheduler/{scheduledPost}', [\App\Http\Controllers\SchedulerController::class, 'destroy'])->name('scheduler.destroy');
    
    // Comment routes
    Route::get('/comments', [\App\Http\Controllers\CommentController::class, 'index'])->name('comments.index');
    Route::get('/comments/create', [\App\Http\Controllers\CommentController::class, 'create'])->name('comments.create');
    Route::post('/comments', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::get('/comments/{comment}/edit', [\App\Http\Controllers\CommentController::class, 'edit'])->name('comments.edit');
    Route::patch('/comments/{comment}', [\App\Http\Controllers\CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [\App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy');
    Route::get('/posts/{post}/comments', [\App\Http\Controllers\CommentController::class, 'postComments'])->name('comments.post');
    
    // Mention routes
    Route::get('/mentions', [\App\Http\Controllers\MentionController::class, 'index'])->name('mentions.index');
    Route::get('/mentions/{mention}', [\App\Http\Controllers\MentionController::class, 'show'])->name('mentions.show');
    Route::patch('/mentions/{mention}', [\App\Http\Controllers\MentionController::class, 'update'])->name('mentions.update');
    Route::delete('/mentions/{mention}', [\App\Http\Controllers\MentionController::class, 'destroy'])->name('mentions.destroy');
    Route::get('/mentions/fetch', [\App\Http\Controllers\MentionController::class, 'fetch'])->name('mentions.fetch');
    Route::get('/mentions/analytics', [\App\Http\Controllers\MentionController::class, 'analytics'])->name('mentions.analytics');
    
    // Compliance routes
    Route::get('/compliance', [\App\Http\Controllers\ComplianceController::class, 'index'])->name('compliance.index');
    Route::get('/compliance/settings', [\App\Http\Controllers\ComplianceController::class, 'settings'])->name('compliance.settings');
    Route::get('/compliance/export', [\App\Http\Controllers\ComplianceController::class, 'export'])->name('compliance.export');
    Route::get('/compliance/delete', [\App\Http\Controllers\ComplianceController::class, 'deleteConfirm'])->name('compliance.delete-confirm');
    Route::post('/compliance/delete', [\App\Http\Controllers\ComplianceController::class, 'delete'])->name('compliance.delete');
    Route::get('/compliance/retention', [\App\Http\Controllers\ComplianceController::class, 'retention'])->name('compliance.retention');
    // Analytics routes
    Route::get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/overview', [\App\Http\Controllers\AnalyticsController::class, 'overview'])->name('analytics.overview');
    Route::get('/analytics/platform/{platform}', [\App\Http\Controllers\AnalyticsController::class, 'platform'])->name('analytics.platform');
    Route::get('/analytics/posts', [\App\Http\Controllers\AnalyticsController::class, 'posts'])->name('analytics.posts');
    Route::get('/analytics/engagement', [\App\Http\Controllers\AnalyticsController::class, 'engagement'])->name('analytics.engagement');
    Route::view('/approval', 'approval')->name('approval');

    // OAuth routes
    Route::get('/oauth/{provider}', [OAuthController::class, 'redirect'])->name('oauth.redirect');
    Route::get('/oauth/{provider}/callback', [OAuthController::class, 'callback'])->name('oauth.callback');
    // Tenant management routes
    Route::get('/tenants', [\App\Http\Controllers\TenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/create', [\App\Http\Controllers\TenantController::class, 'create'])->name('tenants.create');
    Route::post('/tenants', [\App\Http\Controllers\TenantController::class, 'store'])->name('tenants.store');
    Route::get('/tenants/{tenant}/edit', [\App\Http\Controllers\TenantController::class, 'edit'])->name('tenants.edit');
    Route::patch('/tenants/{tenant}', [\App\Http\Controllers\TenantController::class, 'update'])->name('tenants.update');
    Route::delete('/tenants/{tenant}', [\App\Http\Controllers\TenantController::class, 'destroy'])->name('tenants.destroy');
    Route::post('/tenant/switch', [\App\Http\Controllers\TenantController::class, 'switch'])->name('tenant.switch');

    // Billing routes
    Route::get('/billing', [\App\Http\Controllers\BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/subscribe', [\App\Http\Controllers\BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::post('/billing/cancel', [\App\Http\Controllers\BillingController::class, 'cancelSubscription'])->name('billing.cancel');
    Route::post('/billing/payment-method', [\App\Http\Controllers\BillingController::class, 'updatePaymentMethod'])->name('billing.payment-method');
    Route::get('/billing/invoices', [\App\Http\Controllers\BillingController::class, 'invoices'])->name('billing.invoices');
    Route::post('/billing/setup-intent', [\App\Http\Controllers\BillingController::class, 'createSetupIntent'])->name('billing.setup-intent');

    // Webhook routes
    Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');

    // Post routes
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::patch('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    
    // Campaign routes
    Route::get('/campaigns', [\App\Http\Controllers\CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/create', [\App\Http\Controllers\CampaignController::class, 'create'])->name('campaigns.create');
    Route::post('/campaigns', [\App\Http\Controllers\CampaignController::class, 'store'])->name('campaigns.store');
    Route::get('/campaigns/{campaign}/edit', [\App\Http\Controllers\CampaignController::class, 'edit'])->name('campaigns.edit');
    Route::patch('/campaigns/{campaign}', [\App\Http\Controllers\CampaignController::class, 'update'])->name('campaigns.update');
    Route::delete('/campaigns/{campaign}', [\App\Http\Controllers\CampaignController::class, 'destroy'])->name('campaigns.destroy');
    
    // Approval routes
    Route::get('/approval', [\App\Http\Controllers\ApprovalController::class, 'index'])->name('approval.index');
    Route::post('/approval/{approval}/approve', [\App\Http\Controllers\ApprovalController::class, 'approve'])->name('approval.approve');
    Route::post('/approval/{approval}/reject', [\App\Http\Controllers\ApprovalController::class, 'reject'])->name('approval.reject');
    
    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    
    // Social Account routes
    Route::get('/social-accounts', [\App\Http\Controllers\SocialAccountController::class, 'index'])->name('social-accounts.index');
    Route::get('/social-accounts/create', [\App\Http\Controllers\SocialAccountController::class, 'create'])->name('social-accounts.create');
    Route::post('/social-accounts', [\App\Http\Controllers\SocialAccountController::class, 'store'])->name('social-accounts.store');
    Route::get('/social-accounts/{socialAccount}/edit', [\App\Http\Controllers\SocialAccountController::class, 'edit'])->name('social-accounts.edit');
    Route::patch('/social-accounts/{socialAccount}', [\App\Http\Controllers\SocialAccountController::class, 'update'])->name('social-accounts.update');
    Route::delete('/social-accounts/{socialAccount}', [\App\Http\Controllers\SocialAccountController::class, 'destroy'])->name('social-accounts.destroy');
    
    // AI routes
    Route::get('/ai', [\App\Http\Controllers\AIController::class, 'index'])->name('ai.index');
    Route::get('/ai/create', [\App\Http\Controllers\AIController::class, 'create'])->name('ai.create');
    Route::post('/ai/captions', [\App\Http\Controllers\AIController::class, 'generateCaptions'])->name('ai.captions');
    Route::post('/ai/alt-text', [\App\Http\Controllers\AIController::class, 'generateAltText'])->name('ai.alt-text');
    Route::post('/ai/suggestions', [\App\Http\Controllers\AIController::class, 'contentSuggestions'])->name('ai.suggestions');
    Route::post('/ai/sentiment', [\App\Http\Controllers\AIController::class, 'analyzeSentiment'])->name('ai.sentiment');
    Route::post('/ai/timing', [\App\Http\Controllers\AIController::class, 'optimizePostTiming'])->name('ai.timing');
    
    // Admin API Configurations
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/api-configurations', [\App\Http\Controllers\Admin\ApiConfigurationController::class, 'index'])->name('api-configurations.index');
        Route::get('/api-configurations/create', [\App\Http\Controllers\Admin\ApiConfigurationController::class, 'create'])->name('api-configurations.create');
        Route::post('/api-configurations', [\App\Http\Controllers\Admin\ApiConfigurationController::class, 'store'])->name('api-configurations.store');
        Route::get('/api-configurations/{apiConfiguration}/edit', [\App\Http\Controllers\Admin\ApiConfigurationController::class, 'edit'])->name('api-configurations.edit');
        Route::patch('/api-configurations/{apiConfiguration}', [\App\Http\Controllers\Admin\ApiConfigurationController::class, 'update'])->name('api-configurations.update');
        Route::delete('/api-configurations/{apiConfiguration}', [\App\Http\Controllers\Admin\ApiConfigurationController::class, 'destroy'])->name('api-configurations.destroy');
        Route::get('/api-configurations/scopes', [\App\Http\Controllers\Admin\ApiConfigurationController::class, 'getScopes'])->name('api-configurations.scopes');
        Route::get('/api-configurations/{apiConfiguration}/test', [\App\Http\Controllers\Admin\ApiConfigurationController::class, 'test'])->name('api-configurations.test');
    });
});
