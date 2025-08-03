<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SocialAccount;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OAuthController extends Controller
{
    public function redirect($provider)
    {
        $tenant = app('currentTenant');
        
        // Get API configuration for this tenant and platform
        $config = \App\Models\ApiConfiguration::where('tenant_id', $tenant->id)
            ->where('platform', $provider)
            ->where('is_active', true)
            ->first();
            
        if (!$config || !$config->isConfigured()) {
            return redirect()->back()->with('error', ucfirst($provider) . ' API configuration not found or incomplete. Please configure it in the admin panel.');
        }
        
        // Set the configuration for Socialite
        config([
            "services.{$provider}.client_id" => $config->client_id,
            "services.{$provider}.client_secret" => $config->client_secret,
            "services.{$provider}.redirect" => $config->redirect_uri ?: url("/oauth/{$provider}/callback"),
        ]);
        
        $scopes = $config->scopes ?: \App\Models\ApiConfiguration::getDefaultScopes($provider);
        
        return Socialite::driver($provider)
            ->scopes($scopes)
            ->redirect();
    }



    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->user();

            $tenant = app('currentTenant');
            
            // Create or update the social account
            $account = SocialAccount::updateOrCreate([
                'tenant_id' => $tenant->id,
                'platform' => $provider,
                'account_id' => $socialUser->getId(),
            ], [
                'username' => $socialUser->getNickname() ?: $socialUser->getName() ?: '',
                'access_token' => encrypt($socialUser->token),
                'refresh_token' => $socialUser->refreshToken ? encrypt($socialUser->refreshToken) : null,
                'token_expires_at' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn) : null,
            ]);

            return redirect('/dashboard')->with('status', ucfirst($provider) . ' account connected successfully.');

        } catch (\Exception $e) {
            Log::error('OAuth callback error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
            
            return redirect('/dashboard')->with('error', 'Failed to connect ' . ucfirst($provider) . ' account.');
        }
    }

    public function refreshToken(SocialAccount $account)
    {
        try {
            $provider = $account->platform;
            $refreshToken = decrypt($account->refresh_token);

            $response = $this->refreshTokenForProvider($provider, $refreshToken);

            if ($response && isset($response['access_token'])) {
                $account->update([
                    'access_token' => encrypt($response['access_token']),
                    'token_expires_at' => isset($response['expires_in']) 
                        ? now()->addSeconds($response['expires_in']) 
                        : null,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'account_id' => $account->id,
                'provider' => $account->platform,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    protected function refreshTokenForProvider($provider, $refreshToken)
    {
        $config = config("services.{$provider}");
        
        switch ($provider) {
            case 'facebook':
                return $this->refreshFacebookToken($refreshToken, $config);
            case 'twitter':
                return $this->refreshTwitterToken($refreshToken, $config);
            case 'linkedin':
                return $this->refreshLinkedInToken($refreshToken, $config);
            case 'youtube':
                return $this->refreshYouTubeToken($refreshToken, $config);
            default:
                return null;
        }
    }

    protected function refreshFacebookToken($refreshToken, $config)
    {
        $response = Http::get('https://graph.facebook.com/v18.0/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'fb_exchange_token' => $refreshToken,
        ]);

        return $response->json();
    }

    protected function refreshTwitterToken($refreshToken, $config)
    {
        $response = Http::asForm()->post('https://api.twitter.com/2/oauth2/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $config['client_id'],
        ])->withBasicAuth($config['client_id'], $config['client_secret']);

        return $response->json();
    }

    protected function refreshLinkedInToken($refreshToken, $config)
    {
        $response = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
        ]);

        return $response->json();
    }

    protected function refreshYouTubeToken($refreshToken, $config)
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
        ]);

        return $response->json();
    }
} 