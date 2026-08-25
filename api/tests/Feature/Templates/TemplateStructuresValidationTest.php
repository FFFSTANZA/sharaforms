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
        // 2026-08 batch: field-trip through meal-train
        'field-trip-permission-slip-form-template',
        'therapy-intake-form-template',
        'race-registration-form-template',
        'golf-tournament-registration-form-template',
        'vendor-application-form-template',
        'work-order-form-template',
        'warranty-claim-form-template',
        'return-exchange-form-template',
        'facility-rental-request-form-template',
        'parking-permit-application-form-template',
        'internship-application-form-template',
        'student-registration-form-template',
        'preschool-waitlist-form-template',
        'parent-teacher-conference-form-template',
        'tutoring-request-form-template',
        'newsletter-signup-form-template',
        'demo-request-form-template',
        'affiliate-program-application-form-template',
        'speaker-proposal-form-template',
        'complaint-form-template',
        'suggestion-box-form-template',
        'medication-refill-request-form-template',
        // 2026-08 batch 4: conference through foster-animal
        'conference-registration-form-template',
        'reunion-registration-form-template',
        'vacation-bible-school-registration-form-template',
        'cake-order-form-template',
        'yearbook-order-form-template',
        'overtime-request-form-template',
        'travel-authorization-form-template',
        'art-commission-request-form-template',
        'training-evaluation-form-template',
        'transcript-request-form-template',
        'direct-deposit-form-template',
        'purchase-requisition-form-template',
        'it-support-ticket-form-template',
        'credit-application-form-template',
        'wholesale-account-application-form-template',
        'insurance-quote-request-form-template',
        'tax-preparation-client-intake-form-template',
        'tattoo-consent-form-template',
        'prayer-request-form-template',
        'open-house-sign-in-form-template',
        'hoa-architectural-request-form-template',
        'guest-post-pitch-form-template',
        'employee-of-the-month-nomination-form-template',
        'potluck-signup-sheet-form-template',
        'foster-animal-application-form-template',
        // 2026-08 batch 5: move-in-move-out through storage-unit
        'move-in-move-out-inspection-form-template',
        'minor-travel-consent-form-template',
        'vaccination-consent-form-template',
        'resignation-notice-form-template',
        'offer-acceptance-form-template',
        'reference-check-form-template',
        'shift-swap-form-template',
        'job-requisition-form-template',
        'address-change-form-template',
        'callback-request-form-template',
        'public-records-request-form-template',
        'order-status-inquiry-form-template',
        'warranty-registration-form-template',
        'supplier-registration-form-template',
        'school-absence-report-form-template',
        'dental-new-patient-form-template',
        'charity-auction-donation-form-template',
        'donation-pickup-request-form-template',
        'event-feedback-survey-form-template',
        'employee-engagement-survey-template',
        'course-evaluation-form-template',
        'photo-contest-entry-form-template',
        'customer-referral-form-template',
        'birthday-party-booking-form-template',
        'storage-unit-reservation-form-template',
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
        // 2026-08 batch: race through equipment checkout
        'race-registration-form-template' => ['cv_race_total', '{race_category}'],
        'golf-tournament-registration-form-template' => ['cv_golf_total', '{players}'],
        'vendor-application-form-template' => ['cv_booth_total', '{booth_size}'],
        'work-order-form-template' => ['cv_work_estimate', '{est_hours}'],
        'facility-rental-request-form-template' => ['cv_rental_estimate', '{hours}'],
        'parking-permit-application-form-template' => ['cv_permit_fee', '{permit_type}'],
        'tutoring-request-form-template' => ['cv_monthly_estimate', '{sessions_per_month}'],
        'equipment-checkout-form-template' => ['cv_checkout_total', '{duration_days}'],
        // 2026-08 batch 4
        'conference-registration-form-template' => ['cv_conference_total', '{registration_type}'],
        'reunion-registration-form-template' => ['cv_reunion_total', '{attendee_count}'],
        'cake-order-form-template' => ['cv_cake_total', '{cake_size}'],
        'yearbook-order-form-template' => ['cv_yearbook_total', '{copies}'],
        'overtime-request-form-template' => ['cv_overtime_pay', '{hourly_rate}'],
        'travel-authorization-form-template' => ['cv_trip_total', '{hotel_nights}'],
        'art-commission-request-form-template' => ['cv_commission_total', '{commission_type}'],
        'training-evaluation-form-template' => ['cv_training_score', 'SUM('],
        'purchase-requisition-form-template' => ['cv_line_total', '{unit_cost}'],
        // 2026-08 batch 5
        'move-in-move-out-inspection-form-template' => ['cv_deposit_deduction', 'IFBLANK('],
        'event-feedback-survey-form-template' => ['cv_experience_score', 'SUM({r_overall}'],
        'employee-engagement-survey-template' => ['cv_engagement_index', 'SUM({q_purpose}'],
        'birthday-party-booking-form-template' => ['cv_party_total', '{package_choice}'],
        'storage-unit-reservation-form-template' => ['cv_monthly_rate', '{unit_size_su}'],
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
