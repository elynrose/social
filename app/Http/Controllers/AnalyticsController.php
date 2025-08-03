<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Engagement;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Analytics dashboard available']);
        }

        return view('analytics.index');
    }

    public function overview(Request $request)
    {
        $user = $request->user();
        $tenantIds = $user->tenants->pluck('id');
        
        $stats = Engagement::select('platform',
                DB::raw('SUM(likes) as likes'),
                DB::raw('SUM(comments) as comments'),
                DB::raw('SUM(shares) as shares'))
            ->whereHas('post', function ($query) use ($tenantIds) {
                $query->whereIn('tenant_id', $tenantIds);
            })
            ->groupBy('platform')
            ->get();

        if ($request->wantsJson()) {
            return response()->json($stats);
        }

        return view('analytics.overview', compact('stats'));
    }

    public function platform(Request $request, $platform)
    {
        $user = $request->user();
        $tenantIds = $user->tenants->pluck('id');
        
        $platformStats = Engagement::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(likes) as likes'),
                DB::raw('SUM(comments) as comments'),
                DB::raw('SUM(shares) as shares'),
                DB::raw('COUNT(*) as posts')
            )
            ->whereHas('post', function ($query) use ($tenantIds) {
                $query->whereIn('tenant_id', $tenantIds);
            })
            ->where('platform', $platform)
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        if ($request->wantsJson()) {
            return response()->json($platformStats);
        }

        return view('analytics.platform', compact('platformStats', 'platform'));
    }

    public function posts(Request $request)
    {
        $user = $request->user();
        $tenantIds = $user->tenants->pluck('id');
        
        $topPosts = Post::with(['engagement', 'socialAccount'])
            ->whereIn('tenant_id', $tenantIds)
            ->whereHas('engagement')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($post) {
                $engagement = $post->engagement;
                $post->total_engagement = ($engagement->likes ?? 0) + ($engagement->comments ?? 0) + ($engagement->shares ?? 0);
                return $post;
            })
            ->sortByDesc('total_engagement');

        if ($request->wantsJson()) {
            return response()->json($topPosts);
        }

        return view('analytics.posts', compact('topPosts'));
    }

    public function engagement(Request $request)
    {
        $user = $request->user();
        $tenantIds = $user->tenants->pluck('id');
        
        $engagementData = Engagement::select(
                'platform',
                DB::raw('AVG(likes) as avg_likes'),
                DB::raw('AVG(comments) as avg_comments'),
                DB::raw('AVG(shares) as avg_shares'),
                DB::raw('COUNT(*) as total_posts')
            )
            ->whereHas('post', function ($query) use ($tenantIds) {
                $query->whereIn('tenant_id', $tenantIds);
            })
            ->groupBy('platform')
            ->get();

        if ($request->wantsJson()) {
            return response()->json($engagementData);
        }

        return view('analytics.engagement', compact('engagementData'));
    }
}