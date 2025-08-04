<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ApiConfigurationController extends Controller
{
    public function index()
    {
        $configurations = ApiConfiguration::where('tenant_id', app('currentTenant')->id)
            ->orderBy('platform')
            ->get();

        $platformOptions = ApiConfiguration::getPlatformOptions();

        return view('admin.api-configurations.index', compact('configurations', 'platformOptions'));
    }

    public function create()
    {
        $platformOptions = ApiConfiguration::getPlatformOptions();
        $selectedPlatform = request('platform');

        return view('admin.api-configurations.create', compact('platformOptions', 'selectedPlatform'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'platform' => 'required|string|in:' . implode(',', array_keys(ApiConfiguration::getPlatformOptions())),
            'client_id' => 'required|string|max:255',
            'client_secret' => 'required|string|max:1000',
            'redirect_uri' => 'nullable|url|max:255',
            'scopes' => 'nullable|array',
            'scopes.*' => 'string|max:255',
            'is_active' => 'boolean',
            'settings' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $configuration = ApiConfiguration::create([
                'tenant_id' => app('currentTenant')->id,
                'platform' => $request->platform,
                'client_id' => $request->client_id,
                'client_secret' => $request->client_secret,
                'redirect_uri' => $request->redirect_uri,
                'scopes' => $request->scopes ?: ApiConfiguration::getDefaultScopes($request->platform),
                'is_active' => $request->boolean('is_active', true),
                'settings' => $request->settings ?: []
            ]);

            Log::info('API configuration created', [
                'tenant_id' => app('currentTenant')->id,
                'platform' => $request->platform,
                'configuration_id' => $configuration->id
            ]);

            return redirect()->route('admin.api-configurations.index')
                ->with('success', ucfirst($request->platform) . ' API configuration created successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to create API configuration', [
                'error' => $e->getMessage(),
                'platform' => $request->platform
            ]);

            return back()->withInput()
                ->with('error', 'Failed to create API configuration. Please try again.');
        }
    }

    public function edit(ApiConfiguration $apiConfiguration)
    {
        // Ensure user can only edit their tenant's configurations
        if ($apiConfiguration->tenant_id !== app('currentTenant')->id) {
            abort(403);
        }

        $platformOptions = ApiConfiguration::getPlatformOptions();

        return view('admin.api-configurations.edit', compact('apiConfiguration', 'platformOptions'));
    }

    public function update(Request $request, ApiConfiguration $apiConfiguration)
    {
        // Ensure user can only update their tenant's configurations
        if ($apiConfiguration->tenant_id !== app('currentTenant')->id) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|string|max:255',
            'client_secret' => 'required|string|max:1000',
            'redirect_uri' => 'nullable|url|max:255',
            'scopes' => 'nullable|array',
            'scopes.*' => 'string|max:255',
            'is_active' => 'boolean',
            'settings' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $apiConfiguration->update([
                'client_id' => $request->client_id,
                'client_secret' => $request->client_secret,
                'redirect_uri' => $request->redirect_uri,
                'scopes' => $request->scopes ?: ApiConfiguration::getDefaultScopes($apiConfiguration->platform),
                'is_active' => $request->boolean('is_active', true),
                'settings' => $request->settings ?: []
            ]);

            Log::info('API configuration updated', [
                'tenant_id' => app('currentTenant')->id,
                'platform' => $apiConfiguration->platform,
                'configuration_id' => $apiConfiguration->id
            ]);

            return redirect()->route('admin.api-configurations.index')
                ->with('success', ucfirst($apiConfiguration->platform) . ' API configuration updated successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to update API configuration', [
                'error' => $e->getMessage(),
                'configuration_id' => $apiConfiguration->id
            ]);

            return back()->withInput()
                ->with('error', 'Failed to update API configuration. Please try again.');
        }
    }

    public function destroy(ApiConfiguration $apiConfiguration)
    {
        // Ensure user can only delete their tenant's configurations
        if ($apiConfiguration->tenant_id !== app('currentTenant')->id) {
            abort(403);
        }

        try {
            $platform = $apiConfiguration->platform;
            $apiConfiguration->delete();

            Log::info('API configuration deleted', [
                'tenant_id' => app('currentTenant')->id,
                'platform' => $platform,
                'configuration_id' => $apiConfiguration->id
            ]);

            return redirect()->route('admin.api-configurations.index')
                ->with('success', ucfirst($platform) . ' API configuration deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to delete API configuration', [
                'error' => $e->getMessage(),
                'configuration_id' => $apiConfiguration->id
            ]);

            return back()->with('error', 'Failed to delete API configuration. Please try again.');
        }
    }

    public function test(ApiConfiguration $apiConfiguration)
    {
        // Ensure user can only test their tenant's configurations
        if ($apiConfiguration->tenant_id !== app('currentTenant')->id) {
            abort(403);
        }

        try {
            // Test the configuration by attempting to create a Socialite driver
            $driver = \Laravel\Socialite\Facades\Socialite::driver($apiConfiguration->platform);
            
            // Set the configuration using the correct method
            config([
                'services.' . $apiConfiguration->platform . '.client_id' => $apiConfiguration->client_id,
                'services.' . $apiConfiguration->platform . '.client_secret' => $apiConfiguration->client_secret,
                'services.' . $apiConfiguration->platform . '.redirect' => $apiConfiguration->redirect_uri ?: config('services.' . $apiConfiguration->platform . '.redirect')
            ]);

            // For Facebook, we can test if the client ID format is valid
            if ($apiConfiguration->platform === 'facebook') {
                if (!preg_match('/^\d+$/', $apiConfiguration->client_id)) {
                    throw new \Exception('Facebook Client ID should be numeric');
                }
            }

            return response()->json([
                'success' => true,
                'message' => ucfirst($apiConfiguration->platform) . ' API configuration is valid.'
            ]);

        } catch (\Exception $e) {
            Log::error('API configuration test failed', [
                'error' => $e->getMessage(),
                'configuration_id' => $apiConfiguration->id,
                'platform' => $apiConfiguration->platform
            ]);

            return response()->json([
                'success' => false,
                'message' => 'API configuration test failed: ' . $e->getMessage()
            ], 422);
        }
    }

    public function getScopes(Request $request)
    {
        $platform = $request->get('platform');
        
        if (!$platform) {
            return response()->json(['scopes' => []]);
        }

        $scopes = ApiConfiguration::getDefaultScopes($platform);

        return response()->json(['scopes' => $scopes]);
    }
} 