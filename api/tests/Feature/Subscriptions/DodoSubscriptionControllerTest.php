<?php

use App\Service\Billing\DodoPaymentsService;
use Illuminate\Support\Str;

it('creates a dodo checkout session for new subscriptions', function () {
    config()->set('dodo.products.pro.monthly', 'prod_pro_monthly');

    $user = $this->createUser();
    $this->actingAsUser($user);

    $service = \Mockery::mock(DodoPaymentsService::class);
    $service->shouldReceive('getCheckoutUrl')
        ->once()
        ->withArgs(function ($authedUser, $plan, $interval, $options) use ($user) {
            return $authedUser->is($user)
                && $plan === 'pro'
                && $interval === 'monthly'
                && isset($options['return_url'], $options['cancel_url']);
        })
        ->andReturn('https://checkout.dodo.test/session');
    app()->instance(DodoPaymentsService::class, $service);

    $response = $this->getJson(route('subscription.checkout', [
        'subscription' => 'pro',
        'plan' => 'monthly',
    ]));

    $response->assertOk()->assertJson([
        'type' => 'success',
        'checkout_url' => 'https://checkout.dodo.test/session',
    ]);
});

it('uses dodo to change plans for dodo-backed subscriptions', function () {
    config()->set('dodo.products.business.yearly', 'prod_business_yearly');

    $user = $this->createUser();
    $subscription = $user->subscriptions()->create([
        'type' => 'pro',
        'stripe_id' => (string) Str::uuid(),
        'stripe_status' => 'active',
        'stripe_price' => 'prod_pro_monthly',
        'quantity' => 1,
    ]);
    $this->actingAsUser($user);

    $service = \Mockery::mock(DodoPaymentsService::class);
    $service->shouldReceive('changePlan')
        ->once()
        ->withArgs(function ($resolvedSubscription, $plan, $interval) use ($subscription) {
            return $resolvedSubscription->is($subscription)
                && $plan === 'business'
                && $interval === 'yearly';
        })
        ->andReturn([
            'product_id' => 'prod_business_yearly',
            'status' => 'active',
            'quantity' => 1,
        ]);
    $service->shouldReceive('syncSubscription')
        ->once()
        ->withArgs(function ($resolvedSubscription, $data) use ($subscription) {
            return $resolvedSubscription->is($subscription)
                && ($data['product_id'] ?? null) === 'prod_business_yearly';
        });
    app()->instance(DodoPaymentsService::class, $service);

    $response = $this->postJson(route('subscription.change-plan'), [
        'plan' => 'business',
        'interval' => 'yearly',
    ]);

    $response->assertOk()->assertJson([
        'type' => 'success',
        'message' => 'Your plan has been updated successfully.',
    ]);
});

it('uses dodo customer portal for dodo-backed customers', function () {
    $user = $this->createUser();
    $user->forceFill([
        'stripe_id' => 'cus_dodo_123',
    ])->save();
    $this->actingAsUser($user);

    $service = \Mockery::mock(DodoPaymentsService::class);
    $service->shouldReceive('createCustomerPortalUrl')
        ->once()
        ->withArgs(function ($authedUser, $returnUrl) use ($user) {
            return $authedUser->is($user) && is_string($returnUrl) && str_contains($returnUrl, '/home');
        })
        ->andReturn('https://portal.dodo.test/session');
    app()->instance(DodoPaymentsService::class, $service);

    $response = $this->getJson(route('subscription.billing-portal'));

    $response->assertOk()->assertJson([
        'type' => 'success',
        'portal_url' => 'https://portal.dodo.test/session',
    ]);
});
