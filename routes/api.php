<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SchedulerController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\MentionController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\SocialAccountController;
use App\Http\Controllers\CalendarController;

Route::middleware(['auth:sanctum', App\Http\Middleware\SetCurrentTenant::class])->group(function () {
    // Post management
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{post}', [PostController::class, 'show']);
    Route::patch('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    Route::post('/posts/{post}/schedule', [SchedulerController::class, 'schedule']);
    Route::post('/posts/{post}/duplicate', [PostController::class, 'duplicate']);
    Route::post('/posts/{post}/publish', [PostController::class, 'publish']);
    Route::get('/posts/{post}/analytics', [PostController::class, 'analytics']);

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'overview']);

    // Mentions
    Route::get('/mentions', [MentionController::class, 'fetch']);

    // Approvals
    Route::post('/approvals/{post}', [ApprovalController::class, 'requestApproval']);
    Route::patch('/approvals/{approval}/approve', [ApprovalController::class, 'approve']);
    Route::patch('/approvals/{approval}/reject', [ApprovalController::class, 'reject']);

    // Social accounts
    Route::post('/social-accounts', [SocialAccountController::class, 'store']);
    Route::delete('/social-accounts/{id}', [SocialAccountController::class, 'destroy']);

    // Campaigns
    Route::get('/campaigns', [\App\Http\Controllers\CampaignController::class, 'index']);
    Route::post('/campaigns', [\App\Http\Controllers\CampaignController::class, 'store']);
    Route::get('/campaigns/{campaign}', [\App\Http\Controllers\CampaignController::class, 'show']);
    Route::patch('/campaigns/{campaign}', [\App\Http\Controllers\CampaignController::class, 'update']);
    Route::delete('/campaigns/{campaign}', [\App\Http\Controllers\CampaignController::class, 'destroy']);

    // Billing / Plans
    Route::get('/plans', [\App\Http\Controllers\BillingController::class, 'index']);
    Route::post('/subscribe', [\App\Http\Controllers\BillingController::class, 'subscribe']);

    // Calendar events
    Route::get('/calendar/events', [CalendarController::class, 'events']);

    // Comments
    Route::get('/posts/{post}/comments', [\App\Http\Controllers\CommentController::class, 'index']);
    Route::post('/posts/{post}/comments', [\App\Http\Controllers\CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [\App\Http\Controllers\CommentController::class, 'destroy']);

    // AI endpoints
    Route::post('/ai/generate-captions', [\App\Http\Controllers\AIController::class, 'generateCaptions']);
    Route::post('/ai/generate-alt-text', [\App\Http\Controllers\AIController::class, 'generateAltText']);
    Route::post('/ai/content-suggestions', [\App\Http\Controllers\AIController::class, 'contentSuggestions']);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'getUnreadCount']);

    // Compliance / GDPR
    Route::get('/compliance/export', [\App\Http\Controllers\ComplianceController::class, 'export']);
    Route::delete('/compliance/delete', [\App\Http\Controllers\ComplianceController::class, 'delete']);
});