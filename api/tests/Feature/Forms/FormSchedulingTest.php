<?php

use App\Models\Forms\Form;
use Carbon\Carbon;

// ── opens_at tests ──

it('can not submit form that is not yet open (opens_at future)', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace, [
        'opens_at' => Carbon::now()->addDays(1)->toDateTimeString(),
    ]);
    $formData = $this->generateFormSubmissionData($form);

    $this->postJson(route('forms.answer', $form->slug), $formData)
        ->assertStatus(403);
});

it('can submit form that is already open (opens_at past)', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace, [
        'opens_at' => Carbon::now()->subDays(1)->toDateTimeString(),
    ]);
    $formData = $this->generateFormSubmissionData($form);

    $this->postJson(route('forms.answer', $form->slug), $formData)
        ->assertSuccessful()
        ->assertJson([
            'type' => 'success',
            'message' => 'Form submission saved.',
        ]);
});

it('returns scheduled status for form not yet open', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace, [
        'opens_at' => Carbon::now()->addDays(1)->toDateTimeString(),
    ]);

    $this->getJson(route('forms.show', $form->slug))
        ->assertSuccessful()
        ->assertJson([
            'status' => 'scheduled',
        ]);
});

it('returns schedule status from dedicated endpoint', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace, [
        'opens_at' => Carbon::now()->addDays(1)->toDateTimeString(),
    ]);

    $this->getJson(route('forms.schedule-status', $form->slug))
        ->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'opens_at',
        ])
        ->assertJson([
            'status' => 'scheduled',
        ]);
});

// ── recurring schedule-only tests (no opens_at) ──

it('can not submit form outside recurring schedule window (no opens_at)', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $todayName = strtolower(Carbon::now()->format('l'));
    $otherDay = 'monday';
    if ($todayName === 'monday') {
        $otherDay = 'tuesday';
    }

    $form = $this->createForm($user, $workspace, [
        'schedule' => [
            'timezone' => 'UTC',
            'windows' => [
                [
                    'days' => [$otherDay],
                    'start_time' => '00:00',
                    'end_time' => '23:59',
                ],
            ],
        ],
    ]);

    $formData = $this->generateFormSubmissionData($form);

    $this->postJson(route('forms.answer', $form->slug), $formData)
        ->assertStatus(403);
});

it('can submit form inside recurring schedule window (no opens_at)', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $todayName = strtolower(Carbon::now()->format('l'));

    $form = $this->createForm($user, $workspace, [
        'schedule' => [
            'timezone' => 'UTC',
            'windows' => [
                [
                    'days' => [$todayName],
                    'start_time' => Carbon::now()->subHour()->format('H:i'),
                    'end_time' => Carbon::now()->addHour()->format('H:i'),
                ],
            ],
        ],
    ]);

    $formData = $this->generateFormSubmissionData($form);

    $this->postJson(route('forms.answer', $form->slug), $formData)
        ->assertSuccessful()
        ->assertJson([
            'type' => 'success',
            'message' => 'Form submission saved.',
        ]);
});

// ── opens_at + schedule interaction ──

it('can not submit when opens_at is future even if schedule window is active', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $todayName = strtolower(Carbon::now()->format('l'));

    $form = $this->createForm($user, $workspace, [
        'opens_at' => Carbon::now()->addDays(1)->toDateTimeString(),
        'schedule' => [
            'timezone' => 'UTC',
            'windows' => [
                [
                    'days' => [$todayName],
                    'start_time' => Carbon::now()->subHour()->format('H:i'),
                    'end_time' => Carbon::now()->addHour()->format('H:i'),
                ],
            ],
        ],
    ]);

    $formData = $this->generateFormSubmissionData($form);

    // opens_at in future takes priority
    $this->postJson(route('forms.answer', $form->slug), $formData)
        ->assertStatus(403);
});

it('can not submit when opens_at is past but schedule window is inactive', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $todayName = strtolower(Carbon::now()->format('l'));
    $otherDay = 'monday';
    if ($todayName === 'monday') {
        $otherDay = 'tuesday';
    }

    $form = $this->createForm($user, $workspace, [
        'opens_at' => Carbon::now()->subDays(1)->toDateTimeString(),
        'schedule' => [
            'timezone' => 'UTC',
            'windows' => [
                [
                    'days' => [$otherDay],
                    'start_time' => '00:00',
                    'end_time' => '23:59',
                ],
            ],
        ],
    ]);

    $formData = $this->generateFormSubmissionData($form);

    $this->postJson(route('forms.answer', $form->slug), $formData)
        ->assertStatus(403);
});

it('submits when opens_at past and schedule window is active', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $todayName = strtolower(Carbon::now()->format('l'));

    $form = $this->createForm($user, $workspace, [
        'opens_at' => Carbon::now()->subDays(1)->toDateTimeString(),
        'schedule' => [
            'timezone' => 'UTC',
            'windows' => [
                [
                    'days' => [$todayName],
                    'start_time' => Carbon::now()->subHour()->format('H:i'),
                    'end_time' => Carbon::now()->addHour()->format('H:i'),
                ],
            ],
        ],
    ]);

    $formData = $this->generateFormSubmissionData($form);

    $this->postJson(route('forms.answer', $form->slug), $formData)
        ->assertSuccessful()
        ->assertJson([
            'type' => 'success',
            'message' => 'Form submission saved.',
        ]);
});

// ── schedule with empty windows ──

it('rejects submission when schedule has no windows', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $form = $this->createForm($user, $workspace, [
        'opens_at' => Carbon::now()->subDays(1)->toDateTimeString(),
        'schedule' => [
            'timezone' => 'UTC',
            'windows' => [],
        ],
    ]);

    $formData = $this->generateFormSubmissionData($form);

    $this->postJson(route('forms.answer', $form->slug), $formData)
        ->assertStatus(403);
});

it('submits when schedule is null (no recurring gate)', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $form = $this->createForm($user, $workspace, [
        'opens_at' => Carbon::now()->subDays(1)->toDateTimeString(),
        'schedule' => null,
    ]);

    $formData = $this->generateFormSubmissionData($form);

    $this->postJson(route('forms.answer', $form->slug), $formData)
        ->assertSuccessful()
        ->assertJson([
            'type' => 'success',
            'message' => 'Form submission saved.',
        ]);
});

// ── schedule_status attribute ──

it('schedule_status is scheduled when opens_at is future', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $form = $this->createForm($user, $workspace, [
        'opens_at' => Carbon::now()->addDays(1)->toDateTimeString(),
    ]);

    expect($form->schedule_status)->toBe('scheduled');
});

it('schedule_status is closed_by_schedule when outside recurring window', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $todayName = strtolower(Carbon::now()->format('l'));
    $otherDay = 'monday';
    if ($todayName === 'monday') {
        $otherDay = 'tuesday';
    }

    $form = $this->createForm($user, $workspace, [
        'opens_at' => Carbon::now()->subDays(1)->toDateTimeString(),
        'schedule' => [
            'timezone' => 'UTC',
            'windows' => [
                [
                    'days' => [$otherDay],
                    'start_time' => '00:00',
                    'end_time' => '23:59',
                ],
            ],
        ],
    ]);

    expect($form->schedule_status)->toBe('closed_by_schedule');
});

it('schedule_status is open when inside recurring window and opens_at is past', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $todayName = strtolower(Carbon::now()->format('l'));

    $form = $this->createForm($user, $workspace, [
        'opens_at' => Carbon::now()->subDays(1)->toDateTimeString(),
        'schedule' => [
            'timezone' => 'UTC',
            'windows' => [
                [
                    'days' => [$todayName],
                    'start_time' => Carbon::now()->subHour()->format('H:i'),
                    'end_time' => Carbon::now()->addHour()->format('H:i'),
                ],
            ],
        ],
    ]);

    expect($form->schedule_status)->toBe('open');
});
