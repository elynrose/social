<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant;
use App\Models\Mention;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * CheckMentionsJob scans connected social accounts for new mentions and
 * persists them to the database.  You should implement API calls to each
 * supported platform here.  This job can be scheduled via the console
 * kernel to run periodically.
 */
class CheckMentionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Iterate through each tenant and their connected social accounts.
        foreach (Tenant::all() as $tenant) {
            // Fetch social accounts for the tenant.  These contain the
            // credentials needed to query each platform for mentions or
            // comments.
            $accounts = SocialAccount::where('tenant_id', $tenant->id)->get();
            foreach ($accounts as $account) {
                $token = decrypt($account->access_token);
                $platform = strtolower($account->platform);
                // Collect new mentions for this account.
                $mentions = $this->fetchMentions($platform, $token, $account);
                foreach ($mentions as $mention) {
                    Mention::create([
                        'tenant_id' => $tenant->id,
                        'platform' => $platform,
                        'author' => $mention['author'] ?? 'unknown',
                        'content' => $mention['content'] ?? '',
                        'sentiment' => $mention['sentiment'] ?? 'neutral',
                        'posted_at' => $mention['posted_at'] ?? now(),
                    ]);
                }
            }
        }
    }

    /**
     * Fetch recent mentions or comments for the given social account.  Each
     * platform exposes a different API to retrieve comments, replies or
     * tagged posts.  This method normalises the response into a simple
     * array of associative arrays with author, content and posted_at
     * fields.  Sentiment analysis is beyond the scope of this example
     * and a neutral value is returned by default.
     *
     * @param string $platform
     * @param string $token
     * @param SocialAccount $account
     * @return array<array<string,mixed>>
     */
    protected function fetchMentions(string $platform, string $token, SocialAccount $account): array
    {
        try {
            switch ($platform) {
                case 'twitter':
                case 'x':
                    // Use Twitter's recent search endpoint to find tweets that
                    // mention the account's username.  The endpoint requires a
                    // bearer token and returns tweets containing the query.
                    $query = '@' . ltrim($account->username, '@');
                    $response = Http::withToken($token)->get(
                        'https://api.twitter.com/2/tweets/search/recent',
                        [
                            'query' => $query,
                            'tweet.fields' => 'created_at,author_id,text',
                            'expansions' => 'author_id',
                            'user.fields' => 'name,username',
                            'max_results' => 10,
                        ]
                    );
                    if ($response->failed()) {
                        return [];
                    }
                    $usersById = collect($response->json('includes.users', []))->keyBy('id');
                    return collect($response->json('data', []))->map(function ($tweet) use ($usersById) {
                        $user = $usersById->get($tweet['author_id']);
                        return [
                            'author' => $user['username'] ?? 'unknown',
                            'content' => $tweet['text'] ?? '',
                            'sentiment' => 'neutral',
                            'posted_at' => $tweet['created_at'] ?? now(),
                        ];
                    })->all();

                case 'facebook':
                case 'instagram':
                    // Facebook Graph API: fetch posts or comments where the
                    // page has been tagged.  You need pages_read_user_content
                    // or similar permissions.  This example calls the
                    // '/tagged' edge on the page to retrieve posts where the
                    // page is mentioned.  For Instagram, you would call the
                    // Instagram Graph API edges for media/comments.
                    $pageId = $account->account_id;
                    $version = 'v18.0';
                    $response = Http::withToken($token)->get(
                        "https://graph.facebook.com/{$version}/{$pageId}/tagged",
                        [
                            'fields' => 'from,message,created_time',
                            'limit' => 10,
                        ]
                    );
                    if ($response->failed()) {
                        return [];
                    }
                    return collect($response->json('data', []))->map(function ($item) {
                        return [
                            'author' => data_get($item, 'from.name', 'unknown'),
                            'content' => $item['message'] ?? '',
                            'sentiment' => 'neutral',
                            'posted_at' => $item['created_time'] ?? now(),
                        ];
                    })->all();

                case 'linkedin':
                    // LinkedIn API: fetch comments on the most recent shares.
                    // We call the socialActions comments endpoint.  Note: you
                    // need the w_organization_social scope and marketing
                    // platform approval to access this endpoint.
                    $shareUrn = $account->account_id; // In LinkedIn the account_id could be the organization URN.
                    // Retrieve comments for the organization's latest share
                    // (this is a simplified example; in practice you might
                    // need to list recent posts and iterate).
                    $url = "https://api.linkedin.com/rest/socialActions/{$shareUrn}/comments";
                    $response = Http::withToken($token)->get($url, [
                        'count' => 10,
                    ]);
                    if ($response->failed()) {
                        return [];
                    }
                    return collect($response->json('elements', []))->map(function ($comment) {
                        return [
                            'author' => data_get($comment, 'actor.name', 'unknown'),
                            'content' => data_get($comment, 'message.text', ''),
                            'sentiment' => 'neutral',
                            'posted_at' => data_get($comment, 'created.time') ? now() : now(),
                        ];
                    })->all();

                case 'tiktok':
                    // TikTok API: there is no official endpoint for reading
                    // comments via the public API at this time, so we return
                    // an empty set.  In practice you might integrate with
                    // third-party services or use the TikTok Research API.
                    return [];

                case 'youtube':
                    // YouTube Data API: retrieve the latest comments for videos
                    // associated with this account.  We limit to the 10
                    // newest comments across all videos.
                    $url = 'https://www.googleapis.com/youtube/v3/commentThreads';
                    $response = Http::withToken($token)->get($url, [
                        'part' => 'snippet',
                        'allThreadsRelatedToChannelId' => $account->account_id,
                        'maxResults' => 10,
                        'order' => 'time',
                    ]);
                    if ($response->failed()) {
                        return [];
                    }
                    return collect($response->json('items', []))->map(function ($item) {
                        $snippet = $item['snippet']['topLevelComment']['snippet'] ?? [];
                        return [
                            'author' => $snippet['authorDisplayName'] ?? 'unknown',
                            'content' => $snippet['textDisplay'] ?? '',
                            'sentiment' => 'neutral',
                            'posted_at' => $snippet['publishedAt'] ?? now(),
                        ];
                    })->all();

                default:
                    return [];
            }
        } catch (\Throwable $e) {
            \Log::warning('Mentions fetch failed: ' . $e->getMessage());
            return [];
        }
    }
}