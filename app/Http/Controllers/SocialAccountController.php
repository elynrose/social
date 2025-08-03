<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SocialAccount;
use Laravel\Socialite\Facades\Socialite;

class SocialAccountController extends Controller
{
    public function index(Request $request)
    {
        $socialAccounts = SocialAccount::where('tenant_id', app('currentTenant')->id)
            ->orderBy('platform')
            ->orderBy('username')
            ->get();

        if ($request->wantsJson()) {
            return response()->json($socialAccounts);
        }

        return view('social-accounts.index', compact('socialAccounts'));
    }

    public function create()
    {
        $platforms = [
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'linkedin' => 'LinkedIn',
            'instagram' => 'Instagram',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok'
        ];

        return view('social-accounts.create', compact('platforms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'platform' => 'required|string',
            'account_id' => 'required|string',
            'username' => 'required|string',
            'access_token' => 'required|string',
            'refresh_token' => 'nullable|string',
            'token_expires_at' => 'nullable|date',
        ]);
        
        $account = SocialAccount::create($validated);
        
        if ($request->wantsJson()) {
            return response()->json($account, 201);
        }
        
        return redirect()->route('social-accounts.index')
            ->with('success', 'Social account connected successfully.');
    }

    public function edit(SocialAccount $socialAccount)
    {
        // Ensure user can only edit accounts for their current tenant
        if ($socialAccount->tenant_id !== app('currentTenant')->id) {
            abort(403, 'Unauthorized action.');
        }

        $platforms = [
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'linkedin' => 'LinkedIn',
            'instagram' => 'Instagram',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok'
        ];

        return view('social-accounts.edit', compact('socialAccount', 'platforms'));
    }

    public function update(Request $request, SocialAccount $socialAccount)
    {
        // Ensure user can only update accounts for their current tenant
        if ($socialAccount->tenant_id !== app('currentTenant')->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'username' => 'required|string',
            'access_token' => 'required|string',
            'refresh_token' => 'nullable|string',
            'token_expires_at' => 'nullable|date',
        ]);

        $socialAccount->update($validated);

        if ($request->wantsJson()) {
            return response()->json($socialAccount);
        }

        return redirect()->route('social-accounts.index')
            ->with('success', 'Social account updated successfully.');
    }

    public function destroy($id)
    {
        $account = SocialAccount::findOrFail($id);
        
        // Ensure user can only delete accounts for their current tenant
        if ($account->tenant_id !== app('currentTenant')->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $account->delete();
        
        if (request()->wantsJson()) {
            return response()->json(['message' => 'Account disconnected.']);
        }
        
        return redirect()->route('social-accounts.index')
            ->with('success', 'Social account disconnected successfully.');
    }

    public function redirect($provider)
    {
        // Redirect the user to the provider's authorization page.  Some providers
        // require specific scopes which are configured in config/services.php.
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        // Handle callback from provider.  Retrieve the user information and
        // access tokens, then persist them on the SocialAccount model.  We
        // call stateless() here to disable state verification for local
        // testing; in production you should remove this if your callback
        // domain matches the redirect URI exactly.
        $socialUser = Socialite::driver($provider)
            ->stateless()
            ->user();
        $tenant = app('currentTenant');
        // Create or update the social account record.  Encrypt tokens for
        // security.  The `platform` field stores the provider name.
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
    }
}