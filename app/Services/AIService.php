<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AIService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    public function generateAltText($imagePath, $context = '')
    {
        try {
            // For now, we'll use a placeholder. In production, you'd upload the image
            // to OpenAI's vision API or use a local image analysis service
            $prompt = "Generate descriptive alt text for a social media image. Context: {$context}";
            
            $response = $this->makeRequest('/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert at writing accessible alt text for social media images. Provide concise, descriptive alt text that captures the key elements of the image.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 150,
                'temperature' => 0.7,
            ]);

            return $response['choices'][0]['message']['content'] ?? null;
        } catch (\Exception $e) {
            Log::error('Alt text generation failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function generateCaptions($content, $platform, $tone = 'professional')
    {
        try {
            $prompt = "Generate engaging social media captions for the following content. Platform: {$platform}, Tone: {$tone}. Content: {$content}";
            
            $response = $this->makeRequest('/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a social media expert. Generate 3 different caption variations for the given content. Each caption should be optimized for the specified platform and tone. Include relevant hashtags where appropriate."
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.8,
            ]);

            $captions = $response['choices'][0]['message']['content'] ?? '';
            return $this->parseCaptions($captions);
        } catch (\Exception $e) {
            Log::error('Caption generation failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function generateContentSuggestions($brandVoice, $topics = [], $platform = 'general')
    {
        try {
            $topicsText = implode(', ', $topics);
            $prompt = "Generate content ideas for social media. Brand voice: {$brandVoice}, Topics: {$topicsText}, Platform: {$platform}";
            
            $response = $this->makeRequest('/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a social media content strategist. Generate 5 creative content ideas that align with the brand voice and topics provided.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 800,
                'temperature' => 0.9,
            ]);

            return $response['choices'][0]['message']['content'] ?? '';
        } catch (\Exception $e) {
            Log::error('Content suggestions generation failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

    public function analyzeSentiment($text)
    {
        try {
            $response = $this->makeRequest('/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Analyze the sentiment of the given text. Return only: positive, negative, or neutral.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $text
                    ]
                ],
                'max_tokens' => 10,
                'temperature' => 0.1,
            ]);

            return strtolower(trim($response['choices'][0]['message']['content'] ?? 'neutral'));
        } catch (\Exception $e) {
            Log::error('Sentiment analysis failed', ['error' => $e->getMessage()]);
            return 'neutral';
        }
    }

    public function optimizePostTiming($content, $audienceData = [])
    {
        try {
            $audienceInfo = json_encode($audienceData);
            $prompt = "Based on the content and audience data, suggest the best posting times. Content: {$content}, Audience data: {$audienceInfo}";
            
            $response = $this->makeRequest('/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a social media optimization expert. Suggest the best posting times based on content type and audience data. Return specific times in 24-hour format.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 200,
                'temperature' => 0.5,
            ]);

            return $response['choices'][0]['message']['content'] ?? '';
        } catch (\Exception $e) {
            Log::error('Post timing optimization failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

    protected function makeRequest($endpoint, $data)
    {
        $cacheKey = 'ai_request_' . md5(json_encode($data));
        
        return Cache::remember($cacheKey, 3600, function () use ($endpoint, $data) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . $endpoint, $data);

            if (!$response->successful()) {
                throw new \Exception('OpenAI API request failed: ' . $response->body());
            }

            return $response->json();
        });
    }

    protected function parseCaptions($captions)
    {
        // Simple parsing - in production you might want more sophisticated parsing
        $lines = explode("\n", $captions);
        $parsed = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && !str_starts_with($line, '#')) {
                $parsed[] = $line;
            }
        }
        
        return array_slice($parsed, 0, 3); // Return max 3 captions
    }
} 