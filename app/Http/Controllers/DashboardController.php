<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\ScheduledPost;
use App\Models\SocialAccount;
use App\Models\Campaign;
use App\Models\Approval;
use App\Models\Notification;
use App\Models\AnalyticsReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $tenantIds = $user->tenants->pluck('id');

        // Get current date range
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $lastMonth = $now->copy()->subMonth();

        // Content Metrics
        $totalPosts = Post::whereIn('tenant_id', $tenantIds)->count();
        $draftPosts = Post::whereIn('tenant_id', $tenantIds)->where('status', 'draft')->count();
        $publishedPosts = Post::whereIn('tenant_id', $tenantIds)->where('status', 'published')->count();
        $scheduledPosts = ScheduledPost::whereHas('post', function($query) use ($tenantIds) {
            $query->whereIn('tenant_id', $tenantIds);
        })->count();

        // Social Media Metrics
        $connectedAccounts = SocialAccount::whereIn('tenant_id', $tenantIds)->count();
        $activeCampaigns = Campaign::whereIn('tenant_id', $tenantIds)->where('status', 'active')->count();

        // Engagement Metrics (mock data for now)
        $totalEngagement = rand(1500, 5000);
        $engagementGrowth = rand(5, 25);
        $totalReach = rand(50000, 150000);
        $reachGrowth = rand(8, 30);

        // Approval Metrics
        $pendingApprovals = Approval::whereHas('post', function($query) use ($tenantIds) {
            $query->whereIn('tenant_id', $tenantIds);
        })->where('status', 'pending')->count();

        // Recent Activity
        $recentPosts = Post::whereIn('tenant_id', $tenantIds)
            ->with(['campaign', 'socialAccount'])
            ->latest()
            ->take(5)
            ->get();

        $upcomingPosts = ScheduledPost::whereHas('post', function($query) use ($tenantIds) {
            $query->whereIn('tenant_id', $tenantIds);
        })
        ->with(['post.campaign'])
        ->where('publish_at', '>', $now)
        ->orderBy('publish_at')
        ->take(5)
        ->get();

        // Notifications
        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('read_at', null)
            ->count();

        $recentNotifications = Notification::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Performance Chart Data (last 7 days)
        $performanceData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $performanceData[] = [
                'date' => $date->format('M j'),
                'engagement' => rand(50, 200),
                'reach' => rand(1000, 5000),
                'clicks' => rand(10, 100)
            ];
        }

        // Platform Distribution
        $platformData = [
            ['platform' => 'Facebook', 'posts' => rand(20, 50), 'engagement' => rand(500, 1500)],
            ['platform' => 'Twitter', 'posts' => rand(15, 40), 'engagement' => rand(300, 1000)],
            ['platform' => 'LinkedIn', 'posts' => rand(10, 30), 'engagement' => rand(200, 800)],
            ['platform' => 'Instagram', 'posts' => rand(25, 60), 'engagement' => rand(800, 2000)]
        ];

        // Top Performing Posts
        $topPosts = Post::whereIn('tenant_id', $tenantIds)
            ->where('status', 'published')
            ->with(['campaign', 'socialAccount'])
            ->take(3)
            ->get()
            ->map(function($post) {
                $post->engagement = rand(100, 500);
                $post->reach = rand(1000, 5000);
                return $post;
            })
            ->sortByDesc('engagement');

        return view('dashboard', compact(
            'totalPosts',
            'draftPosts',
            'publishedPosts',
            'scheduledPosts',
            'connectedAccounts',
            'activeCampaigns',
            'totalEngagement',
            'engagementGrowth',
            'totalReach',
            'reachGrowth',
            'pendingApprovals',
            'recentPosts',
            'upcomingPosts',
            'unreadNotifications',
            'recentNotifications',
            'performanceData',
            'platformData',
            'topPosts'
        ));
    }
} 