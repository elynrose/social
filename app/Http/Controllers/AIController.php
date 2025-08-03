<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIService;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => 'AI features available']);
        }

        return view('ai.index');
    }

    public function create(Request $request)
    {
        $platforms = [
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'linkedin' => 'LinkedIn',
            'instagram' => 'Instagram',
            'youtube' => 'YouTube'
        ];

        $tones = [
            'professional' => 'Professional',
            'casual' => 'Casual',
            'humorous' => 'Humorous',
            'formal' => 'Formal'
        ];

        if ($request->wantsJson()) {
            return response()->json(compact('platforms', 'tones'));
        }

        return view('ai.create', compact('platforms', 'tones'));
    }

    public function generateCaptions(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'platform' => 'nullable|string|in:facebook,twitter,linkedin,instagram,youtube',
            'tone' => 'nullable|string|in:professional,casual,humorous,formal',
        ]);

        try {
            $content = $request->input('content');
            $platform = $request->input('platform', 'general');
            $tone = $request->input('tone', 'professional');

            $captions = $this->aiService->generateCaptions($content, $platform, $tone);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'captions' => $captions,
                ]);
            }

            return view('ai.captions', compact('captions', 'content', 'platform', 'tone'));
        } catch (\Exception $e) {
            Log::error('Caption generation failed', [
                'error' => $e->getMessage(),
                'content' => $request->input('content'),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate captions. Please try again.',
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to generate captions. Please try again.']);
        }
    }

    public function generateAltText(Request $request)
    {
        $request->validate([
            'media_path' => 'required|string',
            'context' => 'nullable|string|max:500',
        ]);

        try {
            $mediaPath = $request->input('media_path');
            $context = $request->input('context', '');

            $altText = $this->aiService->generateAltText($mediaPath, $context);

            if ($altText) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'alt_text' => $altText,
                    ]);
                }

                return view('ai.alt-text', compact('altText', 'mediaPath', 'context'));
            } else {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to generate alt text.',
                    ], 500);
                }

                return back()->withErrors(['error' => 'Failed to generate alt text.']);
            }
        } catch (\Exception $e) {
            Log::error('Alt text generation failed', [
                'error' => $e->getMessage(),
                'media_path' => $request->input('media_path'),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate alt text. Please try again.',
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to generate alt text. Please try again.']);
        }
    }

    public function contentSuggestions(Request $request)
    {
        $request->validate([
            'brand_voice' => 'required|string|max:200',
            'topics' => 'nullable|array',
            'topics.*' => 'string|max:100',
            'platform' => 'nullable|string|in:facebook,twitter,linkedin,instagram,youtube',
        ]);

        try {
            $brandVoice = $request->input('brand_voice');
            $topics = $request->input('topics', []);
            $platform = $request->input('platform', 'general');

            $suggestions = $this->aiService->generateContentSuggestions($brandVoice, $topics, $platform);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'suggestions' => $suggestions,
                ]);
            }

            return view('ai.suggestions', compact('suggestions', 'brandVoice', 'topics', 'platform'));
        } catch (\Exception $e) {
            Log::error('Content suggestions generation failed', [
                'error' => $e->getMessage(),
                'brand_voice' => $request->input('brand_voice'),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate content suggestions. Please try again.',
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to generate content suggestions. Please try again.']);
        }
    }

    public function analyzeSentiment(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        try {
            $text = $request->input('text');
            $sentiment = $this->aiService->analyzeSentiment($text);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'sentiment' => $sentiment,
                ]);
            }

            return view('ai.sentiment', compact('sentiment', 'text'));
        } catch (\Exception $e) {
            Log::error('Sentiment analysis failed', [
                'error' => $e->getMessage(),
                'text' => $request->input('text'),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to analyze sentiment. Please try again.',
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to analyze sentiment. Please try again.']);
        }
    }

    public function optimizePostTiming(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'audience_data' => 'nullable|array',
        ]);

        try {
            $content = $request->input('content');
            $audienceData = $request->input('audience_data', []);

            $timing = $this->aiService->optimizePostTiming($content, $audienceData);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'timing' => $timing,
                ]);
            }

            return view('ai.timing', compact('timing', 'content', 'audienceData'));
        } catch (\Exception $e) {
            Log::error('Post timing optimization failed', [
                'error' => $e->getMessage(),
                'content' => $request->input('content'),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to optimize post timing. Please try again.',
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to optimize post timing. Please try again.']);
        }
    }
} 