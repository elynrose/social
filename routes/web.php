<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BillingController;

// Health check route for debugging
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'environment' => app()->environment(),
        'database' => app()->environment('production') ? 'connected' : 'development'
    ]);
});

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Calendar routes
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/{scheduledPost}/edit', [CalendarController::class, 'edit'])->name('calendar.edit');
    Route::put('/calendar/{scheduledPost}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{scheduledPost}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::get('/api/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    
    // Analytics routes
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/posts', [AnalyticsController::class, 'posts'])->name('analytics.posts');
    Route::get('/api/analytics', [AnalyticsController::class, 'api'])->name('analytics.api');
    
    // Billing routes
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    
    // Other routes
    Route::view('/posts', 'posts.index')->name('posts.index');
    Route::view('/posts/create', 'posts.create')->name('posts.create');
    Route::view('/approval', 'approval.index')->name('approval.index');
    Route::view('/notifications', 'notifications.index')->name('notifications.index');
    Route::view('/admin/api-configurations', 'admin.api-configurations.index')->name('admin.api-configurations.index');
});
