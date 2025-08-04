<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\SocialAccount;

class FacebookPostingService
{
    protected $baseUrl = 'https://graph.facebook.com/v18.0';

    public function publishPost(Post $post)
    {
        try {
            $socialAccount = $post->socialAccount;
            
            if (!$socialAccount || $socialAccount->platform !== 'facebook') {
                throw new \Exception('No Facebook social account associated with this post');
            }

            $accessToken = $socialAccount->access_token;
            $pageId = $socialAccount->account_id;

            // Prepare the post data
            $postData = [
                'message' => $post->content,
                'access_token' => $accessToken,
            ];

            // Handle media upload if present
            if ($post->media_path) {
                $mediaId = $this->uploadMedia($pageId, $accessToken, $post->media_path, $post->alt_text);
                if ($mediaId) {
                    $postData['attached_media'] = json_encode([['media_fbid' => $mediaId]]);
                }
            }

            // Publish the post
            $response = Http::post("{$this->baseUrl}/{$pageId}/feed", $postData);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Update post with external ID
                $post->update([
                    'external_id' => $responseData['id'] ?? null,
                    'status' => 'published',
                    'published_at' => now(),
                ]);

                Log::info('Facebook post published successfully', [
                    'post_id' => $post->id,
                    'external_id' => $responseData['id'] ?? null,
                    'page_id' => $pageId
                ]);

                return $responseData;

            } else {
                Log::error('Facebook post failed', [
                    'post_id' => $post->id,
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);

                throw new \Exception('Failed to publish to Facebook: ' . ($response->json()['error']['message'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            Log::error('Facebook posting service error', [
                'error' => $e->getMessage(),
                'post_id' => $post->id
            ]);

            throw $e;
        }
    }

    protected function uploadMedia($pageId, $accessToken, $mediaPath, $altText = null)
    {
        try {
            // Check if file exists
            if (!Storage::exists($mediaPath)) {
                Log::warning('Media file not found', ['path' => $mediaPath]);
                return null;
            }

            $file = Storage::get($mediaPath);
            $mimeType = Storage::mimeType($mediaPath);

            // Upload to Facebook
            $response = Http::attach(
                'source',
                $file,
                basename($mediaPath)
            )->post("{$this->baseUrl}/{$pageId}/photos", [
                'access_token' => $accessToken,
                'message' => $altText,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['id'] ?? null;
            }

            Log::error('Facebook media upload failed', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Facebook media upload error', [
                'error' => $e->getMessage(),
                'media_path' => $mediaPath
            ]);

            return null;
        }
    }

    public function getPostInsights(Post $post)
    {
        try {
            if (!$post->external_id) {
                return null;
            }

            $socialAccount = $post->socialAccount;
            $accessToken = $socialAccount->access_token;

            $response = Http::get("{$this->baseUrl}/{$post->external_id}/insights", [
                'access_token' => $accessToken,
                'metric' => 'post_impressions,post_engagements,post_reactions_by_type_total',
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Facebook insights fetch failed', [
                'post_id' => $post->id,
                'external_id' => $post->external_id,
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Facebook insights error', [
                'error' => $e->getMessage(),
                'post_id' => $post->id
            ]);

            return null;
        }
    }

    public function deletePost(Post $post)
    {
        try {
            if (!$post->external_id) {
                return false;
            }

            $socialAccount = $post->socialAccount;
            $accessToken = $socialAccount->access_token;

            $response = Http::delete("{$this->baseUrl}/{$post->external_id}", [
                'access_token' => $accessToken,
            ]);

            if ($response->successful()) {
                Log::info('Facebook post deleted successfully', [
                    'post_id' => $post->id,
                    'external_id' => $post->external_id
                ]);

                return true;
            }

            Log::error('Facebook post deletion failed', [
                'post_id' => $post->id,
                'external_id' => $post->external_id,
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Facebook post deletion error', [
                'error' => $e->getMessage(),
                'post_id' => $post->id
            ]);

            return false;
        }
    }
} 