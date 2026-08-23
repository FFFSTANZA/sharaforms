<?php

use App\Events\Forms\FormSubmitted;
use App\Integrations\Handlers\PipedriveIntegration;
use App\Models\Integration\FormIntegration;
use Illuminate\Support\Facades\Http;

uses(\Tests\TestCase::class);

beforeEach(function () {
    Http::fake([
        'https://api.pipedrive.com/v1/persons*' => Http::response(['success' => true, 'data' => ['id' => 55]]),
        'https://api.pipedrive.com/v1/deals*' => Http::response(['success' => true, 'data' => ['id' => 900]]),
    ]);
    $user = $this->actingAsProUser();
    $this->workspace = $this->createUserWorkspace($user);
    $this->form = $this->createForm($user, $this->workspace);
});

it('creates person then deal when contact fields are mapped', function () {
    $nameField = $this->form->properties[0]['id'];
    $emailField = $this->form->properties[1]['id'] ?? $this->form->properties[0]['id'];
    $phoneField = collect($this->form->properties)->firstWhere('type', 'phone_number')['id'] ?? null;

    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'pipedrive',
        'data' => [
            'api_token' => 'pd_token_123',
            'person_name_field_id' => $nameField,
            'person_email_field_id' => $emailField,
            'person_phone_field_id' => $phoneField,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $nameField => 'Alice',
        $emailField => 'alice@example.com',
        $phoneField => '+1 555 0100',
    ]);

    $handler = new PipedriveIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSentInOrder([
        function ($request) {
            return str_starts_with($request->url(), 'https://api.pipedrive.com/v1/persons?')
                && str_contains($request->url(), 'api_token=pd_token_123')
                && $request->data()['name'] === 'Alice'
                && $request->data()['email'] === ['alice@example.com']
                && $request->data()['phone'] === ['+1 555 0100'];
        },
        function ($request) use ($nameField) {
            $data = $request->data();

            return str_starts_with($request->url(), 'https://api.pipedrive.com/v1/deals?')
                && $data['title'] === $this->form->title.' - Submission'
                && $data['person_id'] === 55
                && ! isset($data['person_name']);
        },
    ]);
});

it('creates only a deal when no contact fields are mapped', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'pipedrive',
        'data' => [
            'api_token' => 'pd_token_123',
            'pipeline_id' => '3',
            'stage_id' => '9',
            'deal_value' => '500.50',
            'currency' => 'usd',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new PipedriveIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    $requests = Http::recorded();
    expect($requests)->toHaveCount(1);

    $data = $requests[0][0]->data();
    expect(str_starts_with($requests[0][0]->url(), 'https://api.pipedrive.com/v1/deals?'))->toBeTrue()
        ->and($data['pipeline_id'])->toBe(3)
        ->and($data['stage_id'])->toBe(9)
        ->and($data['value'])->toBe(500.5)
        ->and($data['currency'])->toBe('USD');
});

it('skips when no api token is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'pipedrive',
        'data' => [],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new PipedriveIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('returns correct validation rules', function () {
    $rules = PipedriveIntegration::getValidationRules($this->form);

    expect($rules)->toHaveKey('api_token')
        ->and($rules)->toHaveKey('deal_title_template')
        ->and($rules)->toHaveKey('pipeline_id')
        ->and($rules)->toHaveKey('stage_id')
        ->and($rules)->toHaveKey('deal_value')
        ->and($rules)->toHaveKey('currency')
        ->and($rules)->toHaveKey('person_name_field_id')
        ->and($rules)->toHaveKey('person_email_field_id');
});
