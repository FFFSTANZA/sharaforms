<?php

use App\Events\Forms\FormSubmitted;
use App\Integrations\Handlers\SupabaseIntegration;
use App\Models\Integration\FormIntegration;
use Illuminate\Support\Facades\Http;

uses(\Tests\TestCase::class);

// These tests exercise payload formatting with fake HTTP targets.
// Relax the SSRF policy so no live DNS lookup is required; guard
// enforcement itself is covered by dedicated tests.
beforeEach(function () {
    config(['sharaforms.webhooks.allow_private_urls' => true]);

    Http::fake();
    $user = $this->actingAsProUser();
    $this->workspace = $this->createUserWorkspace($user);
    $this->form = $this->createForm($user, $this->workspace);
});

it('inserts row into supabase table', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'supabase',
        'data' => [
            'api_key' => 'sb_test_key_123',
            'project_url' => 'https://xyzcompany.supabase.co',
            'table_name' => 'submissions',
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Test Data',
    ]);

    $handler = new SupabaseIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://xyzcompany.supabase.co/rest/v1/submissions'
            && $request->method() === 'POST'
            && $request->hasHeader('apikey')
            && $request->header('apikey')[0] === 'sb_test_key_123';
    });
});

it('skips when no api key is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'supabase',
        'data' => [],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new SupabaseIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('skips when no project_url is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'supabase',
        'data' => [
            'api_key' => 'sb_test_key_123',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new SupabaseIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('returns correct validation rules', function () {
    $rules = SupabaseIntegration::getValidationRules($this->form);
    expect($rules)->toHaveKey('api_key');
    expect($rules)->toHaveKey('project_url');
    expect($rules)->toHaveKey('table_name');
    expect($rules)->toHaveKey('column_mapping');
    expect($rules)->toHaveKey('message');
    expect($rules)->toHaveKey('include_submission_data');
    expect($rules)->toHaveKey('views_submissions_count');
    expect($rules)->toHaveKey('on_conflict');
});

it('sends correct headers', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'supabase',
        'data' => [
            'api_key' => 'sb_test_key_123',
            'project_url' => 'https://xyzcompany.supabase.co',
            'table_name' => 'submissions',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new SupabaseIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization')
            && $request->header('Authorization')[0] === 'Bearer sb_test_key_123'
            && $request->hasHeader('apikey')
            && $request->hasHeader('Prefer')
            && $request->header('Prefer')[0] === 'return=minimal';
    });
});

it('uses column mapping when provided', function () {
    $field = $this->form->properties[0];

    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'supabase',
        'data' => [
            'api_key' => 'sb_test_key_123',
            'project_url' => 'https://xyzcompany.supabase.co',
            'table_name' => 'submissions',
            'column_mapping' => [
                $field['id'] => [
                    'column_name' => 'user_name',
                    'column_type' => 'text',
                ],
            ],
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $field['id'] => 'Alice',
    ]);

    $handler = new SupabaseIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $data = $request->data();
        return isset($data['user_name']) && $data['user_name'] === 'Alice';
    });
});

it('accepts plain string column mappings', function () {
    $field = $this->form->properties[0];

    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'supabase',
        'data' => [
            'api_key' => 'sb_test_key_123',
            'project_url' => 'https://xyzcompany.supabase.co',
            'table_name' => 'submissions',
            // Older/simple UIs may store just the column name as a string
            'column_mapping' => [
                $field['id'] => 'user_name',
            ],
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $field['id'] => 'Alice',
    ]);

    $handler = new SupabaseIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $data = $request->data();
        return isset($data['user_name']) && $data['user_name'] === 'Alice';
    });
});

it('adds metadata fields', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'supabase',
        'data' => [
            'api_key' => 'sb_test_key_123',
            'project_url' => 'https://xyzcompany.supabase.co',
            'table_name' => 'submissions',
            'include_submission_data' => false,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Test',
    ]);

    $handler = new SupabaseIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $data = $request->data();
        return isset($data['_form_id'])
            && isset($data['_submitted_at'])
            && $data['_form_id'] === $this->form->id;
    });
});

it('casts boolean fields correctly', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'supabase',
        'data' => [
            'api_key' => 'sb_test_key_123',
            'project_url' => 'https://xyzcompany.supabase.co',
            'table_name' => 'submissions',
            'column_mapping' => [
                $this->form->properties[0]['id'] => [
                    'column_name' => 'is_active',
                    'column_type' => 'bool',
                ],
            ],
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'true',
    ]);

    $handler = new SupabaseIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->data()['is_active'] === true;
    });
});

it('casts integer fields correctly', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'supabase',
        'data' => [
            'api_key' => 'sb_test_key_123',
            'project_url' => 'https://xyzcompany.supabase.co',
            'table_name' => 'submissions',
            'column_mapping' => [
                $this->form->properties[0]['id'] => [
                    'column_name' => 'age',
                    'column_type' => 'int',
                ],
            ],
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => '25',
    ]);

    $handler = new SupabaseIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->data()['age'] === 25;
    });
});

it('casts json fields correctly', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'supabase',
        'data' => [
            'api_key' => 'sb_test_key_123',
            'project_url' => 'https://xyzcompany.supabase.co',
            'table_name' => 'submissions',
            'column_mapping' => [
                $this->form->properties[0]['id'] => [
                    'column_name' => 'metadata',
                    'column_type' => 'json',
                ],
            ],
        ],
    ]);

    // Pass a JSON-encoded string — outputStringsOnly() converts arrays to CSV,
    // so castValue() receives the string and must json_decode it back.
    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => '{"key":"value"}',
    ]);

    $handler = new SupabaseIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $metadata = $request->data()['metadata'];

        return is_array($metadata) && $metadata['key'] === 'value';
    });
});

it('enables upsert when on_conflict is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'supabase',
        'data' => [
            'api_key' => 'sb_test_key_123',
            'project_url' => 'https://xyzcompany.supabase.co',
            'table_name' => 'submissions',
            'on_conflict' => 'email',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new SupabaseIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return str_contains($request->header('Prefer')[0], 'resolution=merge-duplicates');
    });
});

it('slugifies unmapped field names', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'supabase',
        'data' => [
            'api_key' => 'sb_test_key_123',
            'project_url' => 'https://xyzcompany.supabase.co',
            'table_name' => 'submissions',
        ],
    ]);

    // The first property name gets slugified
    $fieldName = $this->form->properties[0]['name'];

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Test',
    ]);

    $handler = new SupabaseIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) use ($fieldName) {
        // Match handler's slugify: strtolower first, then preg_replace
        $expectedSlug = strtolower($fieldName);
        $expectedSlug = preg_replace('/[^a-z0-9]+/', '_', $expectedSlug);
        $expectedSlug = trim($expectedSlug, '_');
        $data = $request->data();
        return isset($data[$expectedSlug]);
    });
});
