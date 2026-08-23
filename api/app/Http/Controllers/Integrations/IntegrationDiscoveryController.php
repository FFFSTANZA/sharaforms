<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Service\Security\PublicWebhookUrl;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class IntegrationDiscoveryController extends Controller
{
    /**
     * List Trello boards the user has access to.
     */
    public function trelloBoards(Request $request): JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string',
            'api_token' => 'required|string',
        ]);

        $response = Http::timeout(10)->get('https://api.trello.com/1/members/me/boards', [
            'key' => $request->api_key,
            'token' => $request->api_token,
            'fields' => 'name,url,closed',
            'filter' => 'open',
        ]);

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Trello boards. Check your API key and token.');
        }

        return response()->json($response->json());
    }

    /**
     * List Trello lists on a specific board.
     */
    public function trelloLists(Request $request, string $boardId): JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string',
            'api_token' => 'required|string',
        ]);

        $response = Http::timeout(10)->get("https://api.trello.com/1/boards/{$boardId}/lists", [
            'key' => $request->api_key,
            'token' => $request->api_token,
            'fields' => 'name,closed',
            'filter' => 'open',
        ]);

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Trello lists.');
        }

        return response()->json($response->json());
    }

    /**
     * List Trello labels on a specific board.
     */
    public function trelloLabels(Request $request, string $boardId): JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string',
            'api_token' => 'required|string',
        ]);

        $response = Http::timeout(10)->get("https://api.trello.com/1/boards/{$boardId}/labels", [
            'key' => $request->api_key,
            'token' => $request->api_token,
        ]);

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Trello labels.');
        }

        return response()->json($response->json());
    }

    /**
     * List tables in a Supabase project.
     */
    public function supabaseTables(Request $request): JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string',
            'project_url' => 'required|url',
        ]);

        $projectUrl = rtrim($request->project_url, '/');

        $response = Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer '.$request->api_key,
                'apikey' => $request->api_key,
            ])
            ->get("{$projectUrl}/rest/v1/", [
                'select' => '*',
            ]);

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Supabase tables. Check your project URL and API key.');
        }

        // The root endpoint returns a list of tables/views
        $data = $response->json();

        // Extract table names from the response
        $tables = [];
        if (is_array($data)) {
            foreach ($data as $item) {
                if (isset($item['table_name'])) {
                    $tables[] = [
                        'name' => $item['table_name'],
                        'schema' => $item['table_schema'] ?? 'public',
                    ];
                }
            }
        }

        // Deduplicate
        $tables = collect($tables)->unique('name')->values()->all();

        return response()->json($tables);
    }

    /**
     * List columns in a Supabase table.
     */
    public function supabaseColumns(Request $request, string $tableName): JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string',
            'project_url' => 'required|url',
        ]);

        $projectUrl = rtrim($request->project_url, '/');

        // Use information_schema to get column details
        $response = Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer '.$request->api_key,
                'apikey' => $request->api_key,
                'Content-Type' => 'application/json',
            ])
            ->post("{$projectUrl}/rest/v1/rpc/get_table_columns", [
                'p_table_name' => $tableName,
            ]);

        // Fallback: try querying the table directly to infer columns
        if ($response->failed()) {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$request->api_key,
                    'apikey' => $request->api_key,
                ])
                ->get("{$projectUrl}/rest/v1/{$tableName}", [
                    'select' => '*',
                    'limit' => 0,
                ]);
        }

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch columns for table "'.$tableName.'".');
        }

        // The PostgREST response includes column info in the response headers
        // or we can infer from the OpenAPI schema
        $columns = [];

        // Try fetching from the OpenAPI spec
        $openApiResponse = Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer '.$request->api_key,
                'apikey' => $request->api_key,
            ])
            ->get("{$projectUrl}/rest/v1/");

        if ($openApiResponse->successful()) {
            $schema = $openApiResponse->json();
            $tableDef = $schema['definitions'][$tableName] ?? null;
            if ($tableDef && isset($tableDef['properties'])) {
                foreach ($tableDef['properties'] as $colName => $colDef) {
                    $columns[] = [
                        'name' => $colName,
                        'type' => $colDef['type'] ?? 'text',
                        'nullable' => ! in_array($colName, $tableDef['required'] ?? []),
                    ];
                }
            }
        }

        return response()->json($columns);
    }

    /**
     * Resolve the Baserow base URL from the request (self-hosted support).
     */
    private function baserowBaseUrl(Request $request): string
    {
        $baseUrl = trim((string) $request->input('base_url', ''));

        return $baseUrl !== '' ? rtrim($baseUrl, '/') : 'https://api.baserow.io';
    }

    /**
     * Guard a user-supplied base URL against SSRF (HTTPS, public IPs only).
     * Applies the same policy as outgoing webhooks.
     */
    private function assertSafeBaseUrl(string $url): void
    {
        try {
            PublicWebhookUrl::assertSafe($url);
        } catch (InvalidArgumentException $e) {
            abort(422, $e->getMessage());
        }
    }

    /**
     * Baserow authenticates with "Authorization: Token <token>".
     */
    private function baserowClient(Request $request): PendingRequest
    {
        return Http::timeout(10)
            ->withOptions(PublicWebhookUrl::requestOptions($this->baserowBaseUrl($request).'/'))
            ->withHeaders([
                'Authorization' => 'Token '.$request->api_key,
            ]);
    }

    /**
     * List Baserow workspaces the token has access to.
     */
    public function baserowWorkspaces(Request $request): JsonResponse
    {
        $request->validate([
            'api_key' => ['required', 'string'],
            'base_url' => ['nullable', 'url'],
        ]);

        $this->assertSafeBaseUrl($this->baserowBaseUrl($request));

        $response = $this->baserowClient($request)
            ->get($this->baserowBaseUrl($request).'/api/workspaces/');

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Baserow workspaces. Check your API token.');
        }

        return response()->json($response->json());
    }

    /**
     * List database applications inside a Baserow workspace.
     */
    public function baserowDatabases(Request $request, string $workspaceId): JsonResponse
    {
        $request->validate([
            'api_key' => ['required', 'string'],
            'base_url' => ['nullable', 'url'],
        ]);

        $this->assertSafeBaseUrl($this->baserowBaseUrl($request));

        $response = $this->baserowClient($request)
            ->get($this->baserowBaseUrl($request)."/api/applications/workspace/{$workspaceId}/");

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Baserow databases.');
        }

        // Only "database" applications contain tables.
        $databases = collect((array) $response->json())
            ->filter(fn ($app) => is_array($app) && ($app['type'] ?? null) === 'database')
            ->map(fn ($app) => [
                'id' => $app['id'],
                'name' => $app['name'],
            ])
            ->values()
            ->all();

        return response()->json($databases);
    }

    /**
     * List tables of a Baserow database (application).
     */
    public function baserowTables(Request $request, string $databaseId): JsonResponse
    {
        $request->validate([
            'api_key' => ['required', 'string'],
            'base_url' => ['nullable', 'url'],
        ]);

        $this->assertSafeBaseUrl($this->baserowBaseUrl($request));

        $response = $this->baserowClient($request)
            ->get($this->baserowBaseUrl($request)."/api/database/tables/database/{$databaseId}/");

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Baserow tables.');
        }

        return response()->json(collect((array) $response->json())
            ->map(fn ($table) => [
                'id' => $table['id'] ?? null,
                'name' => $table['name'] ?? '',
            ])
            ->filter(fn ($table) => ! empty($table['id']))
            ->values()
            ->all());
    }

    /**
     * List fields (columns) of a Baserow table.
     */
    public function baserowFields(Request $request, string $tableId): JsonResponse
    {
        $request->validate([
            'api_key' => ['required', 'string'],
            'base_url' => ['nullable', 'url'],
        ]);

        $this->assertSafeBaseUrl($this->baserowBaseUrl($request));

        $response = $this->baserowClient($request)
            ->get($this->baserowBaseUrl($request)."/api/database/fields/table/{$tableId}/");

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Baserow fields.');
        }

        return response()->json(collect((array) $response->json())
            ->map(fn ($field) => [
                'name' => $field['name'] ?? '',
                'type' => $field['type'] ?? 'text',
            ])
            ->values()
            ->all());
    }

    /**
     * List Linear teams accessible with the API key.
     */
    public function linearTeams(Request $request): JsonResponse
    {
        $request->validate([
            'api_key' => ['required', 'string'],
        ]);

        $response = $this->linearClient($request)->post('https://api.linear.app/graphql', [
            'query' => '{ teams { nodes { id name key } } }',
        ]);

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Linear teams. Check your API key.');
        }

        return response()->json(data_get($response->json(), 'data.teams.nodes', []));
    }

    /**
     * List Linear projects that belong to a team.
     */
    public function linearProjects(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'api_key' => ['required', 'string'],
            'team_id' => ['required', 'string'],
        ]);

        $response = $this->linearClient($request)->post('https://api.linear.app/graphql', [
            'query' => 'query Projects($teamId: UUID!) { projects(first: 100, filter: { teams: { id: { eq: $teamId } } }) { nodes { id name } } }',
            'variables' => ['teamId' => $validated['team_id']],
        ]);

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Linear projects.');
        }

        return response()->json(data_get($response->json(), 'data.projects.nodes', []));
    }

    /**
     * List Linear workflow states (statuses) of a team.
     */
    public function linearStates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'api_key' => ['required', 'string'],
            'team_id' => ['required', 'string'],
        ]);

        $response = $this->linearClient($request)->post('https://api.linear.app/graphql', [
            'query' => 'query States($teamId: UUID!) { workflowStates(first: 50, filter: { team: { id: { eq: $teamId } } }) { nodes { id name type } } }',
            'variables' => ['teamId' => $validated['team_id']],
        ]);

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Linear statuses.');
        }

        return response()->json(data_get($response->json(), 'data.workflowStates.nodes', []));
    }

    /**
     * List Linear issue labels of a team.
     */
    public function linearLabels(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'api_key' => ['required', 'string'],
            'team_id' => ['required', 'string'],
        ]);

        $response = $this->linearClient($request)->post('https://api.linear.app/graphql', [
            'query' => 'query Labels($teamId: UUID!) { issueLabels(first: 100, filter: { team: { id: { eq: $teamId } } }) { nodes { id name color } } }',
            'variables' => ['teamId' => $validated['team_id']],
        ]);

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Linear labels.');
        }

        return response()->json(data_get($response->json(), 'data.issueLabels.nodes', []));
    }

    /**
     * Linear authenticates with the raw key in the Authorization header.
     */
    private function linearClient(Request $request): PendingRequest
    {
        return Http::timeout(10)->withHeaders([
            'Authorization' => (string) $request->api_key,
        ]);
    }

    /**
     * List Pipedrive pipelines for the API token.
     */
    public function pipedrivePipelines(Request $request): JsonResponse
    {
        $request->validate([
            'api_token' => ['required', 'string'],
        ]);

        $response = Http::timeout(10)->get('https://api.pipedrive.com/v1/pipelines', [
            'api_token' => $request->api_token,
        ]);

        if ($response->failed() || ! data_get($response->json(), 'success')) {
            abort($response->status() ?: 500, 'Failed to fetch Pipedrive pipelines. Check your API token.');
        }

        return response()->json(data_get($response->json(), 'data', []));
    }

    /**
     * List stages of a Pipedrive pipeline.
     */
    public function pipedriveStages(Request $request, string $pipelineId): JsonResponse
    {
        $request->validate([
            'api_token' => ['required', 'string'],
        ]);

        $response = Http::timeout(10)->get('https://api.pipedrive.com/v1/stages', [
            'api_token' => $request->api_token,
            'pipeline_id' => $pipelineId,
        ]);

        if ($response->failed() || ! data_get($response->json(), 'success')) {
            abort($response->status() ?: 500, 'Failed to fetch Pipedrive stages.');
        }

        return response()->json(data_get($response->json(), 'data', []));
    }

    /**
     * Resolve the Plane base URL from the request (self-hosted support).
     */
    private function planeBaseUrl(Request $request): string
    {
        $baseUrl = trim((string) $request->input('base_url', ''));

        return $baseUrl !== '' ? rtrim($baseUrl, '/') : 'https://api.plane.so';
    }

    /**
     * Plane authenticates with the X-API-Key header.
     */
    private function planeClient(Request $request): PendingRequest
    {
        return Http::timeout(10)
            ->withOptions(PublicWebhookUrl::requestOptions($this->planeBaseUrl($request).'/'))
            ->withHeaders([
                'X-API-Key' => $request->api_key,
            ]);
    }

    /**
     * Normalize a list response that may be a plain array or paginated {results: []}.
     */
    private function normalizeListResponse($payload): array
    {
        if (is_array($payload) && array_values($payload) === []) {
            return [];
        }
        if (is_array($payload) && isset($payload['results']) && is_array($payload['results'])) {
            return $payload['results'];
        }

        return is_array($payload) ? $payload : [];
    }

    /**
     * List Plane workspaces the API key has access to.
     */
    public function planeWorkspaces(Request $request): JsonResponse
    {
        $request->validate([
            'api_key' => ['required', 'string'],
            'base_url' => ['nullable', 'url'],
        ]);

        $this->assertSafeBaseUrl($this->planeBaseUrl($request));

        $response = $this->planeClient($request)
            ->get($this->planeBaseUrl($request).'/api/v1/workspaces/');

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Plane workspaces. Check your API key.');
        }

        return response()->json($this->normalizeListResponse($response->json()));
    }

    /**
     * List projects in a Plane workspace.
     */
    public function planeProjects(Request $request, string $workspaceSlug): JsonResponse
    {
        $request->validate([
            'api_key' => ['required', 'string'],
            'base_url' => ['nullable', 'url'],
        ]);

        $this->assertSafeBaseUrl($this->planeBaseUrl($request));

        $response = $this->planeClient($request)
            ->get($this->planeBaseUrl($request)."/api/v1/workspaces/{$workspaceSlug}/projects/");

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Plane projects.');
        }

        return response()->json($this->normalizeListResponse($response->json()));
    }

    /**
     * List states (issue statuses) of a Plane project.
     */
    public function planeStates(Request $request, string $workspaceSlug, string $projectId): JsonResponse
    {
        $request->validate([
            'api_key' => ['required', 'string'],
            'base_url' => ['nullable', 'url'],
        ]);

        $this->assertSafeBaseUrl($this->planeBaseUrl($request));

        $response = $this->planeClient($request)
            ->get($this->planeBaseUrl($request)."/api/v1/workspaces/{$workspaceSlug}/projects/{$projectId}/states/");

        if ($response->failed()) {
            abort($response->status(), 'Failed to fetch Plane states.');
        }

        return response()->json($this->normalizeListResponse($response->json()));
    }
}
