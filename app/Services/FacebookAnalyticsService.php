<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SocialAccount;
use Carbon\Carbon;

class FacebookAnalyticsService
{
    protected $baseUrl = 'https://graph.facebook.com/v18.0';

    public function getPageInsights($pageId, $accessToken, $metrics = [], $period = 'day', $days = 7)
    {
        try {
            $endTime = Carbon::now();
            $startTime = $endTime->copy()->subDays($days);

            $params = [
                'access_token' => $accessToken,
                'metric' => implode(',', $metrics ?: ['page_impressions', 'page_engaged_users', 'page_post_engagements']),
                'period' => $period,
                'since' => $startTime->getTimestamp(),
                'until' => $endTime->getTimestamp(),
            ];

            $response = Http::get("{$this->baseUrl}/{$pageId}/insights", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Facebook API error', [
                'page_id' => $pageId,
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Facebook Analytics Service Error', [
                'error' => $e->getMessage(),
                'page_id' => $pageId
            ]);

            return null;
        }
    }

    public function getPagePosts($pageId, $accessToken, $limit = 10)
    {
        try {
            $params = [
                'access_token' => $accessToken,
                'fields' => 'id,message,created_time,insights.metric(post_impressions,post_engagements,post_reactions_by_type_total)',
                'limit' => $limit
            ];

            $response = Http::get("{$this->baseUrl}/{$pageId}/posts", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Facebook Posts API error', [
                'page_id' => $pageId,
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Facebook Posts Service Error', [
                'error' => $e->getMessage(),
                'page_id' => $pageId
            ]);

            return null;
        }
    }

    public function getDashboardMetrics($tenantId)
    {
        $accounts = SocialAccount::where('tenant_id', $tenantId)
            ->where('platform', 'facebook')
            ->whereNotNull('access_token')
            ->get();

        $totalEngagement = 0;
        $totalReach = 0;
        $totalPosts = 0;
        $platformData = [];

        foreach ($accounts as $account) {
            // Get page insights
            $insights = $this->getPageInsights(
                $account->account_id,
                $account->access_token,
                ['page_impressions', 'page_engaged_users', 'page_post_engagements']
            );

            if ($insights && isset($insights['data'])) {
                foreach ($insights['data'] as $metric) {
                    if ($metric['name'] === 'page_impressions') {
                        $totalReach += $metric['values'][0]['value'] ?? 0;
                    } elseif ($metric['name'] === 'page_engaged_users') {
                        $totalEngagement += $metric['values'][0]['value'] ?? 0;
                    }
                }
            }

            // Get posts data
            $posts = $this->getPagePosts($account->account_id, $account->access_token, 5);
            if ($posts && isset($posts['data'])) {
                $totalPosts += count($posts['data']);
                
                $postEngagement = 0;
                foreach ($posts['data'] as $post) {
                    if (isset($post['insights']['data'])) {
                        foreach ($post['insights']['data'] as $insight) {
                            if ($insight['name'] === 'post_engagements') {
                                $postEngagement += $insight['values'][0]['value'] ?? 0;
                            }
                        }
                    }
                }

                $platformData[] = [
                    'platform' => 'Facebook',
                    'posts' => count($posts['data']),
                    'engagement' => $postEngagement
                ];
            }
        }

        return [
            'total_engagement' => $totalEngagement,
            'total_reach' => $totalReach,
            'total_posts' => $totalPosts,
            'platform_data' => $platformData
        ];
    }

    public function getPerformanceData($tenantId, $days = 7)
    {
        $accounts = SocialAccount::where('tenant_id', $tenantId)
            ->where('platform', 'facebook')
            ->whereNotNull('access_token')
            ->get();

        $performanceData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayEngagement = 0;
            $dayReach = 0;
            $dayClicks = 0;

            foreach ($accounts as $account) {
                $insights = $this->getPageInsights(
                    $account->account_id,
                    $account->access_token,
                    ['page_impressions', 'page_engaged_users'],
                    'day',
                    1
                );

                if ($insights && isset($insights['data'])) {
                    foreach ($insights['data'] as $metric) {
                        if ($metric['name'] === 'page_impressions') {
                            $dayReach += $metric['values'][0]['value'] ?? 0;
                        } elseif ($metric['name'] === 'page_engaged_users') {
                            $dayEngagement += $metric['values'][0]['value'] ?? 0;
                        }
                    }
                }
            }

            $performanceData[] = [
                'date' => $date->format('M j'),
                'engagement' => $dayEngagement,
                'reach' => $dayReach,
                'clicks' => $dayClicks
            ];
        }

        return $performanceData;
    }
} 