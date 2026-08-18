<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\OAuthProviderResource;
use App\Integrations\Google\GoogleOAuthClient;
use App\Integrations\Google\GoogleOAuthException;
use App\Integrations\OAuth\OAuthProviderService;
use App\Models\OAuthProvider;
use Illuminate\Support\Facades\Auth;

class OAuthProviderController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $providers = $user->oauthProviders()->get();

        return OAuthProviderResource::collection($providers);
    }

    public function token(OAuthProvider $provider)
    {
        $this->authorize('view', $provider);

        if ($provider->provider !== OAuthProviderService::Google) {
            return response()->json(['message' => 'This provider cannot issue an access token.'], 403);
        }

        try {
            $accessToken = (new GoogleOAuthClient($provider))->getAccessTokenString();
        } catch (GoogleOAuthException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\RuntimeException) {
            return response()->json(['message' => 'Google account not found or token expired. Please reconnect your Google account.'], 422);
        }

        return response()->json([
            'access_token' => $accessToken,
            'expires_in' => 3600,
        ]);
    }

    public function destroy(OAuthProvider $provider)
    {
        $this->authorize('delete', $provider);

        $provider->delete();

        return response()->json();
    }
}
