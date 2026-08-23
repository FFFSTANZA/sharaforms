<?php

use App\Events\Forms\FormSubmitted;
use App\Integrations\Handlers\SupabaseIntegration;
use App\Models\Integration\FormIntegration;
use Illuminate\Support\Facades\Http;

it('allows free workspaces to create integrations without a required tier', function () {
    config(['sharaforms.webhooks.allow_private_urls' => true]);

    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $this->postJson(route('open.forms.integrations.create', $form), [
        'status' => 'active',
        'integration_id' => 'webhook',
        'logic' => null,
        'data' => [
            'webhook_url' => 'https://integrations.example.com/hook',
        ],
    ])
        ->assertSuccessful()
        ->assertJson([
            'type' => 'success',
            'message' => 'Form Integration was created.',
        ]);
});

it('blocks free workspaces from creating pro-gated integrations', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $response = $this->postJson(route('open.forms.integrations.create', $form), [
        'status' => 'active',
        'integration_id' => 'supabase',
        'logic' => null,
        'data' => [
            'api_key' => 'sb_test_key',
            'project_url' => 'https://test.supabase.co',
            'table_name' => 'submissions',
        ],
    ]);

    $response->assertStatus(402);
    expect($response->json('required_tier'))->toBe('pro');
    $this->assertDatabaseMissing('form_integrations', [
        'form_id' => $form->id,
        'integration_id' => 'supabase',
    ]);
});

it('allows pro workspaces to create pro integrations but blocks business ones', function () {
    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $this->postJson(route('open.forms.integrations.create', $form), [
        'status' => 'active',
        'integration_id' => 'supabase',
        'logic' => null,
        'data' => [
            'api_key' => 'sb_test_key',
            'project_url' => 'https://test.supabase.co',
            'table_name' => 'submissions',
        ],
    ])->assertSuccessful();

    $response = $this->postJson(route('open.forms.integrations.create', $form), [
        'status' => 'active',
        'integration_id' => 'trello',
        'logic' => null,
        'data' => [
            'api_key' => 'trello_key',
            'api_token' => 'trello_token',
            'list_id' => 'list-123',
        ],
    ]);

    $response->assertStatus(402);
    expect($response->json('required_tier'))->toBe('business');
});

it('allows business workspaces to create business integrations', function () {
    $user = $this->actingAsBusinessUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $this->postJson(route('open.forms.integrations.create', $form), [
        'status' => 'active',
        'integration_id' => 'trello',
        'logic' => null,
        'data' => [
            'api_key' => 'trello_key',
            'api_token' => 'trello_token',
            'list_id' => 'list-123',
        ],
    ])->assertSuccessful();
});

it('keeps legacy gated integrations updatable after a tier downgrade', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    // Configured while the workspace was on a paid tier.
    $integration = FormIntegration::factory()->create([
        'form_id' => $form->id,
        'integration_id' => 'supabase',
        'data' => [
            'api_key' => 'sb_old_key',
            'project_url' => 'https://legacy.supabase.co',
            'table_name' => 'submissions',
        ],
    ]);

    $this->putJson(route('open.forms.integrations.update', [$form, $integration->id]), [
        'status' => 'active',
        'integration_id' => 'supabase',
        'logic' => null,
        'data' => [
            'api_key' => 'sb_rotated_key',
            'project_url' => 'https://legacy.supabase.co',
            'table_name' => 'submissions',
        ],
    ])->assertSuccessful();

    $this->assertDatabaseHas('form_integrations', [
        'id' => $integration->id,
        'integration_id' => 'supabase',
    ]);
});

it('does not run gated integration handlers when the workspace lacks the tier', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $integration = FormIntegration::factory()->make([
        'form_id' => $form->id,
        'integration_id' => 'supabase',
        'data' => [
            'api_key' => 'sb_test_key',
            'project_url' => 'https://test.supabase.co',
            'table_name' => 'submissions',
        ],
    ]);

    Http::fake();

    $handler = new class (
        new FormSubmitted($form, []),
        $integration,
        ['name' => 'Supabase']
    ) extends SupabaseIntegration {
        public function runGuard(): bool
        {
            return $this->shouldRun();
        }
    };

    expect($handler->runGuard())->toBeFalse();
    Http::assertNothingSent();
});
