<?php

use App\Events\Forms\FormSubmitted;
use App\Integrations\Handlers\AirtableIntegration;
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

it('sends record to airtable', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'airtable',
        'data' => [
            'api_key' => 'pat_test123',
            'base_id' => 'appTestBase123',
            'table_id' => 'tblTestTable123',
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Alice',
    ]);

    $handler = new AirtableIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.airtable.com/v0/appTestBase123/tblTestTable123'
            && $request->method() === 'POST'
            && isset($request->data()['fields'])
            && $request->hasHeader('Authorization')
            && str_contains($request->header('Authorization')[0], 'Bearer pat_test123');
    });
});

it('skips when no api key is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'airtable',
        'data' => [],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new AirtableIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('returns correct validation rules', function () {
    $rules = AirtableIntegration::getValidationRules($this->form);
    expect($rules)->toHaveKey('api_key');
    expect($rules)->toHaveKey('base_id');
    expect($rules)->toHaveKey('table_id');
    expect($rules['api_key'])->toContain('required');
});

it('includes all submission fields in payload', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'airtable',
        'data' => [
            'api_key' => 'pat_test123',
            'base_id' => 'appTestBase123',
            'table_id' => 'tblTestTable123',
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Bob',
    ]);

    $handler = new AirtableIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $fields = $request->data()['fields'];
        return is_array($fields) && count($fields) > 0;
    });
});
