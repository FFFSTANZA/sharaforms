<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Integrations\Notion\NotionApiClient;
use App\Models\OAuthProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotionController extends Controller
{
    /**
     * List all Notion databases the user's connected workspace has access to.
     */
    public function databases(Request $request): JsonResponse
    {
        $provider = $this->getNotionProvider($request);

        $client = new NotionApiClient($provider);
        $databases = $client->listDatabases();

        return response()->json($databases);
    }

    /**
     * Get the properties (columns) of a specific Notion database.
     */
    public function databaseProperties(Request $request, string $databaseId): JsonResponse
    {
        $provider = $this->getNotionProvider($request);

        $client = new NotionApiClient($provider);
        $properties = $client->getDatabaseProperties($databaseId);

        return response()->json($properties);
    }

    /**
     * Get the Notion provider for the authenticated user.
     * Accepts an optional oauth_id parameter to select a specific provider.
     */
    private function getNotionProvider(Request $request): OAuthProvider
    {
        $query = OAuthProvider::where('provider', 'notion');

        if ($request->oauth_id) {
            $query->where('id', $request->oauth_id);
        }

        $query->where('user_id', $request->user()->id);

        $provider = $query->first();

        if (!$provider) {
            abort(404, 'No connected Notion workspace found. Please connect your Notion account first.');
        }

        return $provider;
    }
}
