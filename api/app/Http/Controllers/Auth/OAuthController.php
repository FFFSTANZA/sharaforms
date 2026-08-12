<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OAuthRedirectRequest;
use App\Service\OAuth\OAuthContextService;
use App\Service\OAuth\OAuthFlowOrchestrator;
use Illuminate\Http\Request;

class OAuthController extends Controller
{
    public function __construct(
        private OAuthFlowOrchestrator $flowOrchestrator,
        private OAuthContextService $contextService
    ) {
    }

    /**
     * Redirect the user to the provider authentication page.
     */
    public function redirect(OAuthRedirectRequest $request, string $provider)
    {
        $result = $this->flowOrchestrator->processRedirect(
            $provider,
            $request->validated()
        );

        // Bind the flow to this browser via a SameSite=Lax double-submit cookie.
        return response()
            ->json($result)
            ->withCookie($this->contextService->issueStateCookie($result['state']));
    }

    /**
     * Handle the OAuth callback from the provider.
     */
    public function callback(Request $request, string $provider)
    {
        $params = $request->all();

        // Orchestrator now returns JsonResponse directly with proper status codes
        return $this->flowOrchestrator->processCallback($provider, $params);
    }

    /**
     * Handle widget-based OAuth callback.
     */
    public function handleWidgetCallback(Request $request, string $service)
    {
        $request->validate([
            'intent' => 'required|in:' . implode(',', OAuthFlowOrchestrator::INTENTS),
            'invite_token' => 'sometimes|string',
            'utm_data' => 'sometimes|array',
        ]);

        // Orchestrator now returns JsonResponse directly with proper status codes
        return $this->flowOrchestrator->processWidgetCallback($service, $request);
    }
}
