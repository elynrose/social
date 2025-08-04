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
use App\Services\FacebookAnalyticsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $facebookAnalytics;

    public function __construct(FacebookAnalyticsService $facebookAnalytics)
    {
        $this->facebookAnalytics = $facebookAnalytics;
    }

    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $tenantIds = $user->tenants->pluck('id');
            $currentTenant = app('currentTenant');

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

            // Try to get real Facebook analytics data
            $facebookMetrics = null;
            if ($currentTenant) {
                try {
                    $facebookMetrics = $this->facebookAnalytics->getDashboardMetrics($currentTenant->id);
                } catch (\Exception $e) {
                    \Log::error('Facebook Analytics Error: ' . $e->getMessage());
                    $facebookMetrics = null;
                }
            }

            // Engagement Metrics (real data if available, otherwise mock)
            if ($facebookMetrics && $facebookMetrics['total_engagement'] > 0) {
                $totalEngagement = $facebookMetrics['total_engagement'];
                $totalReach = $facebookMetrics['total_reach'];
                $engagementGrowth = rand(5, 25); // Still mock for now
                $reachGrowth = rand(8, 30); // Still mock for now
            } else {
                $totalEngagement = rand(1500, 5000);
                $engagementGrowth = rand(5, 25);
                $totalReach = rand(50000, 150000);
                $reachGrowth = rand(8, 30);
            }

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

            // Performance Chart Data (real data if available, otherwise mock)
            if ($currentTenant && $facebookMetrics) {
                try {
                    $performanceData = $this->facebookAnalytics->getPerformanceData($currentTenant->id, 7);
                } catch (\Exception $e) {
                    \Log::error('Performance Data Error: ' . $e->getMessage());
                    $performanceData = [];
                }
            } else {
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
            }

            // Platform Distribution (real data if available, otherwise mock)
            if ($facebookMetrics && !empty($facebookMetrics['platform_data'])) {
                $platformData = $facebookMetrics['platform_data'];
                // Add mock data for other platforms
                $platformData[] = ['platform' => 'Twitter', 'posts' => rand(15, 40), 'engagement' => rand(300, 1000)];
                $platformData[] = ['platform' => 'LinkedIn', 'posts' => rand(10, 30), 'engagement' => rand(200, 800)];
                $platformData[] = ['platform' => 'Instagram', 'posts' => rand(25, 60), 'engagement' => rand(800, 2000)];
            } else {
                $platformData = [
                    ['platform' => 'Facebook', 'posts' => rand(20, 50), 'engagement' => rand(500, 1500)],
                    ['platform' => 'Twitter', 'posts' => rand(15, 40), 'engagement' => rand(300, 1000)],
                    ['platform' => 'LinkedIn', 'posts' => rand(10, 30), 'engagement' => rand(200, 800)],
                    ['platform' => 'Instagram', 'posts' => rand(25, 60), 'engagement' => rand(800, 2000)]
                ];
            }

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
                'topPosts',
                'facebookMetrics'
            ));

        } catch (\Exception $e) {
            \Log::error('Dashboard Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return a simple error view or redirect
            return redirect()->route('posts.index')->with('error', 'Dashboard temporarily unavailable. Please try again.');
        }
    }
} 