<?php

use App\Events\Forms\FormSubmitted;
use App\Integrations\Handlers\BaserowIntegration;
use App\Models\Integration\FormIntegration;
use Illuminate\Support\Facades\Http;

uses(\Tests\TestCase::class);

// Payload-formatting tests use fake HTTP targets; relax the SSRF policy
// so no live DNS lookup is needed. Guard enforcement has dedicated tests.
beforeEach(function () {
    config(['sharaforms.webhooks.allow_private_urls' => true]);

    Http::fake();
    $user = $this->actingAsProUser();
    $this->workspace = $this->createUserWorkspace($user);
    $this->form = $this->createForm($user, $this->workspace);
});

it('inserts row into baserow table with token auth', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'baserow',
        'data' => [
            'api_key' => 'brw_test_token',
            'table_id' => '42',
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Alice',
    ]);

    $handler = new BaserowIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return str_starts_with($request->url(), 'https://api.baserow.io/api/database/rows/table/42/')
            && str_contains($request->url(), 'user_field_names=true')
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization')
            && $request->header('Authorization')[0] === 'Token brw_test_token'
            && ($request->data()['name'] ?? null) === 'Alice';
    });
});

it('uses custom base url for self-hosted instances', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'baserow',
        'data' => [
            'api_key' => 'brw_test_token',
            'base_url' => 'https://api.baserow.example.com/',
            'table_id' => '7',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new BaserowIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return str_starts_with($request->url(), 'https://api.baserow.example.com/api/database/rows/table/7/');
    });
});

it('skips when no api key is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'baserow',
        'data' => [
            'table_id' => '42',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new BaserowIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('skips when no table id is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'baserow',
        'data' => [
            'api_key' => 'brw_test_token',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new BaserowIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('uses column mapping when provided', function () {
    $field = $this->form->properties[0];

    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'baserow',
        'data' => [
            'api_key' => 'brw_test_token',
            'table_id' => '42',
            'column_mapping' => [
                $field['id'] => ['column_name' => 'Full Name'],
            ],
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $field['id'] => 'Alice',
    ]);

    $handler = new BaserowIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return ($request->data()['Full Name'] ?? null) === 'Alice';
    });
});

it('accepts plain string column mappings', function () {
    $field = $this->form->properties[0];

    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'baserow',
        'data' => [
            'api_key' => 'brw_test_token',
            'table_id' => '42',
            // Older/simple UIs may store just the column name as a string
            'column_mapping' => [
                $field['id'] => 'Full Name',
            ],
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $field['id'] => 'Alice',
    ]);

    $handler = new BaserowIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return ($request->data()['Full Name'] ?? null) === 'Alice';
    });
});

it('casts values based on the mapped column type', function () {
    $nameField = $this->form->properties[0];
    $numberField = collect($this->form->properties)->firstWhere('type', 'number');
    $checkboxField = collect($this->form->properties)->firstWhere('type', 'checkbox');

    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'baserow',
        'data' => [
            'api_key' => 'brw_test_token',
            'table_id' => '42',
            'column_mapping' => [
                $nameField['id'] => ['column_name' => 'Name', 'column_type' => 'text'],
                $numberField['id'] => ['column_name' => 'Age', 'column_type' => 'int'],
                $checkboxField['id'] => ['column_name' => 'Subscribed', 'column_type' => 'bool'],
            ],
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $nameField['id'] => 'Alice',
        $numberField['id'] => '42',
        $checkboxField['id'] => 'true',
    ]);

    $handler = new BaserowIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $data = $request->data();

        return $data['Name'] === 'Alice'
            && $data['Age'] === 42
            && $data['Subscribed'] === true;
    });
});

it('refuses private base urls', function () {
    // The guard honors WEBHOOKS_ALLOW_PRIVATE_URLS; force the strict policy here.
    config(['sharaforms.webhooks.allow_private_urls' => false]);

    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'baserow',
        'data' => [
            'api_key' => 'brw_test_token',
            'base_url' => 'https://127.0.0.1',
            'table_id' => '42',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new BaserowIntegration($event, $integration, $integration->toArray());

    expect(fn () => $handler->handle())->toThrow(InvalidArgumentException::class);
});

it('returns correct validation rules', function () {
    $rules = BaserowIntegration::getValidationRules($this->form);

    expect($rules)->toHaveKey('api_key')
        ->and($rules)->toHaveKey('base_url')
        ->and($rules)->toHaveKey('workspace_id')
        ->and($rules)->toHaveKey('database_id')
        ->and($rules)->toHaveKey('table_id')
        ->and($rules)->toHaveKey('column_mapping');
});
