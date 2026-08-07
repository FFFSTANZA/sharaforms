<?php

use App\Models\Billing\Subscription;
use Illuminate\Support\Carbon;

uses(\Tests\TestCase::class);

function subscriptionForStatusTest(string $status, ?string $trialEndsAt = null, ?string $endsAt = null): Subscription
{
    return new Subscription([
        'stripe_status' => $status,
        'trial_ends_at' => $trialEndsAt ? Carbon::parse($trialEndsAt) : null,
        'ends_at' => $endsAt ? Carbon::parse($endsAt) : null,
    ]);
}

it('is on trial only while the status allows a trial', function () {
    $trialing = subscriptionForStatusTest('trialing', now()->addDays(7)->toIso8601String());
    expect($trialing->onTrial())->toBeTrue();

    $active = subscriptionForStatusTest('active', now()->addDays(7)->toIso8601String());
    expect($active->onTrial())->toBeTrue();

    $cancelled = subscriptionForStatusTest('cancelled', now()->addDays(7)->toIso8601String());
    expect($cancelled->onTrial())->toBeFalse();

    $pastDue = subscriptionForStatusTest('past_due', now()->addDays(7)->toIso8601String());
    expect($pastDue->onTrial())->toBeFalse();
});

it('does not consider a cancelled subscription with a stale future trial date valid', function () {
    $cancelled = subscriptionForStatusTest('cancelled', now()->addDays(7)->toIso8601String());

    expect($cancelled->valid())->toBeFalse();
});

it('keeps past due and paused subscriptions in the grace window', function () {
    expect(subscriptionForStatusTest('past_due')->valid())->toBeTrue();
    expect(subscriptionForStatusTest('paused')->valid())->toBeTrue();
    expect(subscriptionForStatusTest('on_hold')->valid())->toBeTrue();

    expect(subscriptionForStatusTest('past_due')->active())->toBeFalse();
});

it('is invalid once cancelled and the grace period has ended', function () {
    $cancelled = subscriptionForStatusTest('cancelled', null, now()->subDay()->toIso8601String());

    expect($cancelled->valid())->toBeFalse();
});