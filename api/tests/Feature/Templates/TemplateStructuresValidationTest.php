<?php

use App\Http\Resources\FormResource;
use App\Models\Template;
use App\Models\Forms\Form;
use Database\Seeders\TemplateSeeder;

/**
 * Guards every template structure against the real form-creation validation
 * pipeline (StoreFormRequest -> UserFormRequest -> property validators +
 * ComputedVariablesRule). Any template that ships broken logic conditions,
 * formulas referencing missing fields, or invalid option shapes fails here
 * before it can reach production.
 */
uses(\Tests\TestHelpers::class);

function templateStructurePayload(Template $template, array $basePayload): array
{
    $structure = $template->structure;

    // The template structure is a partial form object; overlay it onto the
    // canonical payload the same way the client does when instantiating a
    // template (resolveCreateFormState spreads templateStructure over baseForm).
    return array_merge($basePayload, $structure);
}

function baseCreatePayload($tester, $user, $workspace): array
{
    return (new FormResource($tester->makeForm($user, $workspace)))->toArray(request());
}

it('seeds every template with valid form structures', function () {
    $this->seed(TemplateSeeder::class);

    expect(Template::count())->toBeGreaterThan(70);

    foreach (Template::all() as $template) {
        expect($template->structure)->toBeArray();
        expect($template->structure['properties'])->toBeArray();

        foreach ($template->structure['properties'] as $field) {
            expect(isset($field['type']))->toBeTrue("Template {$template->slug} has a field without type");
            expect(isset($field['id']))->toBeTrue("Template {$template->slug} has a field without id");
        }
    }
});

it('instantiates every template through the real form creation endpoint', function () {
    $this->seed(TemplateSeeder::class);

    $user = $this->createUser();
    $workspace = $this->createUserWorkspace($user);

    $failed = [];
    $basePayload = baseCreatePayload($this, $user, $workspace);
    foreach (Template::all() as $template) {
        $payload = templateStructurePayload($template, $basePayload);

        $response = $this->actingAs($user)
            ->postJson(route('open.forms.store'), $payload);

        if ($response->status() >= 400) {
            $failed[] = [
                'slug' => $template->slug,
                'status' => $response->status(),
                'errors' => $response->json('errors') ?? $response->json(),
            ];
        } else {
            // Clean up so subsequent iterations stay fast and unique slugs free.
            Form::whereKey($response->json('form.id'))->first()?->forceDelete();
        }
    }

    expect($failed)->toBe([], 'Templates failing form-create validation: '
        .json_encode($failed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

it('validates the flagship conditional logic templates expose reveal logic', function () {
    $this->seed(TemplateSeeder::class);

    $logicSlugs = [
        'liability-waiver-form-template',
        'photo-release-form-template',
        'poll-form-template',
        'voting-form-template',
        'nps-survey-template',
        'wedding-rsvp-form-template',
        'rsvp-form-template',
        'job-application-form-template',
        'rental-application-form-template',
        'patient-intake-form-template',
        'leave-request-form-template',
        'insurance-claim-form-template',
        'maintenance-request-form-template',
        'sports-team-registration-form-template',
        'donation-form-template',
        'customer-feedback-survey-template',
        'tshirt-order-form-template',
        'catering-order-form-template',
        'reimbursement-claim-form-template',
    ];

    foreach ($logicSlugs as $slug) {
        $template = Template::whereSlug($slug)->firstOrFail();
        $withLogic = collect($template->structure['properties'])->filter(
            fn ($f) => ! empty($f['logic']['conditions']['children'])
        );

        expect($withLogic->isNotEmpty())
            ->toBeTrue("Template {$slug} was expected to carry conditional logic");
    }
});

it('validates calculation templates compute live totals', function () {
    $this->seed(TemplateSeeder::class);

    $calcSlugs = [
        'calculation-form-template' => ['cv_total', '{project_type}'],
        'quiz-form-template' => ['cv_score', '{q1}'],
        'purchase-order-form-template' => ['cv_line_total', '{quantity}*{unit_price}'],
        'timesheet-form-template' => ['cv_total_hours', 'SUM('],
        'tshirt-order-form-template' => ['cv_order_total', '{quantity}'],
        'catering-order-form-template' => ['cv_estimate', '{guest_count}'],
        'event-registration-template' => ['cv_ticket_total', '{ticket_type}'],
        'gym-membership-form-template' => ['cv_monthly_cost', '{plan}'],
        'summer-camp-registration-form-template' => ['cv_camp_fee', '{session}'],
        'gaming-tournament-registration-form-template' => ['cv_entry_fee', '{team_size}'],
        'photography-booking-form-template' => ['cv_starting_price', '{session_type}'],
        'reimbursement-claim-form-template' => ['cv_mileage_estimate', '{mileage_distance}'],
    ];

    foreach ($calcSlugs as $slug => [$variableId, $formulaFragment]) {
        $template = Template::whereSlug($slug)->firstOrFail();
        $variables = collect($template->structure['computed_variables'] ?? []);

        $variable = $variables->firstWhere('id', $variableId);
        expect($variable)->not->toBeNull("Template {$slug} is missing computed variable {$variableId}");
        expect($variable['formula'])->toContain($formulaFragment);

        // Every referenced field must exist in properties.
        preg_match_all('/\{([^}]+)\}/', $variable['formula'], $matches);
        $propertyIds = collect($template->structure['properties'])->pluck('id')->all();
        foreach (array_unique($matches[1]) as $ref) {
            expect(in_array($ref, $propertyIds))
                ->toBeTrue('Template ' . $slug . ': formula references unknown field {' . $ref . '}');
        }

        // The rendering block must mention the variable for live display.
        $mentionBlocks = collect($template->structure['properties'])->filter(
            fn ($f) => ($f['type'] ?? '') === 'nf-text'
                && str_contains($f['content'] ?? '', 'mention-field-id="'.$variableId.'"')
        );
        expect($mentionBlocks->isNotEmpty())
            ->toBeTrue("Template {$slug} has no nf-text block rendering {$variableId}");
    }
});
