<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AnalyticsReport;
use App\Models\SocialAccount;
use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class FetchAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [300, 600, 1200]; // Retry after 5, 10, 20 minutes
    public $timeout = 300;

    protected $accountId;
    protected $dateRange;

    public function __construct($accountId, $dateRange = 'last_7_days')
    {
        $this->accountId = $accountId;
        $this->dateRange = $dateRange;
    }

    public function handle()
    {
        try {
            $account = SocialAccount::findOrFail($this->accountId);
            
            // Check rate limits
            $rateLimitKey = "analytics_{$account->platform}_{$account->tenant_id}";
            if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
                $this->release(3600); // Release back to queue for 1 hour
                return;
            }

            RateLimiter::hit($rateLimitKey, 3600); // 1 hour window

            $analytics = $this->fetchAnalyticsForPlatform($account);
            
            if ($analytics) {
                AnalyticsReport::create([
                    'tenant_id' => $account->tenant_id,
                    'social_account_id' => $account->id,
                    'platform' => $account->platform,
                    'date_range' => $this->dateRange,
                    'metrics' => $analytics,
                    'generated_at' => now(),
                ]);

                Log::info('Analytics fetched successfully', [
                    'account_id' => $this->accountId,
                    'platform' => $account->platform,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Analytics fetch job failed', [
                'account_id' => $this->accountId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    protected function fetchAnalyticsForPlatform($account)
    {
        $cacheKey = "analytics_{$account->platform}_{$account->id}_{$this->dateRange}";
        
        return Cache::remember($cacheKey, 3600, function () use ($account) {
            switch ($account->platform) {
                case 'facebook':
                    return $this->fetchFacebookAnalytics($account);
                case 'twitter':
                    return $this->fetchTwitterAnalytics($account);
                case 'linkedin':
                    return $this->fetchLinkedInAnalytics($account);
                case 'youtube':
                    return $this->fetchYouTubeAnalytics($account);
                default:
                    return null;
            }
        });
    }

    protected function fetchFacebookAnalytics($account)
    {
        try {
            $token = decrypt($account->access_token);
            $response = Http::withToken($token)
                ->get("https://graph.facebook.com/v18.0/{$account->account_id}/insights", [
                    'metric' => 'page_impressions,page_engaged_users,page_post_engagements',
                    'period' => 'day',
                    'since' => now()->subDays(7)->timestamp,
                    'until' => now()->timestamp,
                ]);

            if ($response->successful()) {
                return $this->formatFacebookMetrics($response->json());
            }
        } catch (\Exception $e) {
            Log::error('Facebook analytics fetch failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    protected function fetchTwitterAnalytics($account)
    {
        try {
            $token = decrypt($account->access_token);
            $response = Http::withToken($token)
                ->get("https://api.twitter.com/2/users/{$account->account_id}/tweets", [
                    'max_results' => 100,
                    'tweet.fields' => 'public_metrics,created_at',
                ]);

            if ($response->successful()) {
                return $this->formatTwitterMetrics($response->json());
            }
        } catch (\Exception $e) {
            Log::error('Twitter analytics fetch failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    protected function fetchLinkedInAnalytics($account)
    {
        try {
            $token = decrypt($account->access_token);
            $response = Http::withToken($token)
                ->get("https://api.linkedin.com/v2/organizationalEntityShareStatistics", [
                    'q' => 'organizationalEntity',
                    'organizationalEntity' => "urn:li:organization:{$account->account_id}",
                ]);

            if ($response->successful()) {
                return $this->formatLinkedInMetrics($response->json());
            }
        } catch (\Exception $e) {
            Log::error('LinkedIn analytics fetch failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    protected function fetchYouTubeAnalytics($account)
    {
        try {
            $token = decrypt($account->access_token);
            $response = Http::withToken($token)
                ->get("https://www.googleapis.com/youtube/v3/channels", [
                    'part' => 'statistics',
                    'id' => $account->account_id,
                ]);

            if ($response->successful()) {
                return $this->formatYouTubeMetrics($response->json());
            }
        } catch (\Exception $e) {
            Log::error('YouTube analytics fetch failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    protected function formatFacebookMetrics($data)
    {
        $metrics = [];
        if (isset($data['data'])) {
            foreach ($data['data'] as $metric) {
                $metrics[$metric['name']] = $metric['values'][0]['value'] ?? 0;
            }
        }
        return $metrics;
    }

    protected function formatTwitterMetrics($data)
    {
        $metrics = [
            'total_tweets' => 0,
            'total_retweets' => 0,
            'total_likes' => 0,
            'total_replies' => 0,
        ];

        if (isset($data['data'])) {
            foreach ($data['data'] as $tweet) {
                $metrics['total_tweets']++;
                $metrics['total_retweets'] += $tweet['public_metrics']['retweet_count'] ?? 0;
                $metrics['total_likes'] += $tweet['public_metrics']['like_count'] ?? 0;
                $metrics['total_replies'] += $tweet['public_metrics']['reply_count'] ?? 0;
            }
        }

        return $metrics;
    }

    protected function formatLinkedInMetrics($data)
    {
        return [
            'total_shares' => $data['totalShareStatistics']['totalShareCount'] ?? 0,
            'unique_impressions' => $data['totalShareStatistics']['uniqueImpressionsCount'] ?? 0,
            'engagement' => $data['totalShareStatistics']['engagement'] ?? 0,
        ];
    }

    protected function formatYouTubeMetrics($data)
    {
        if (isset($data['items'][0]['statistics'])) {
            $stats = $data['items'][0]['statistics'];
            return [
                'subscriber_count' => $stats['subscriberCount'] ?? 0,
                'video_count' => $stats['videoCount'] ?? 0,
                'view_count' => $stats['viewCount'] ?? 0,
            ];
        }

        return null;
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Analytics fetch job failed permanently', [
            'account_id' => $this->accountId,
            'error' => $exception->getMessage(),
        ]);
    }
}