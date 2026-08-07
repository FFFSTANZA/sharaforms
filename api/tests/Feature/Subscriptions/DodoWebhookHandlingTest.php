<?php

use App\Models\Billing\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    $this->enablePricing();
    config()->set('dodo.webhook_key', 'whsec_'.Str::random(32));
});

function postDodoWebhook(string $payload, ?string $webhookId = null): TestResponse
{
    $webhookId ??= (string) Str::uuid();
    $secret = (string) config('dodo.webhook_key');

    if (str_starts_with($secret, 'whsec_')) {
        $secret = (string) base64_decode(substr($secret, 6), true);
    }

    $timestamp = time();
    $signedPayload = $webhookId.'.'.$timestamp.'.'.$payload;
    $signature = base64_encode(hash_hmac('sha256', $signedPayload, $secret, true));

    return test()->call('POST', route('dodo.webhook'), [], [], [], [
        'HTTP_WEBHOOK_ID' => $webhookId,
        'HTTP_WEBHOOK_TIMESTAMP' => (string) $timestamp,
        'HTTP_WEBHOOK_SIGNATURE' => 'v1,'.$signature,
    ], $payload);
}

function dodoWebhookPayload(string $type, array $data): string
{
    return json_encode([
        'type' => $type,
        'data' => $data,
    ]);
}

it('rejects webhooks with an invalid signature', function () {
    $response = test()->call('POST', route('dodo.webhook'), [], [], [], [
        'HTTP_WEBHOOK_ID' => (string) Str::uuid(),
        'HTTP_WEBHOOK_TIMESTAMP' => (string) time(),
        'HTTP_WEBHOOK_SIGNATURE' => 'v1,invalid',
    ], json_encode(['type' => 'subscription.created', 'data' => []]));

    $response->assertStatus(401);
});

it('creates a subscription from subscription.created and attributes it to the user', function () {
    $user = $this->createUser();
    $trialEndsAt = now()->addDays(7)->toIso8601String();

    $response = postDodoWebhook(dodoWebhookPayload('subscription.created', [
        'subscription_id' => 'sub_created_1',
        'product_id' => 'prod_pro_monthly',
        'status' => 'trialing',
        'trial_period_days' => 14,
        'trial_ends_at' => $trialEndsAt,
        'customer' => [
            'customer_id' => 'cus_created_1',
            'email' => $user->email,
        ],
    ]));

    $response->assertStatus(200);

    $subscription = $user->fresh()->subscriptions()->where('stripe_id', 'sub_created_1')->first();

    expect($subscription)->not->toBeNull();
    expect($subscription->stripe_status)->toBe('trialing');
    expect($subscription->trial_ends_at->equalTo(Carbon::parse($trialEndsAt)))->toBeTrue();
    expect($user->fresh()->stripe_id)->toBe('cus_created_1');
});

it('does not reprocess a webhook that already succeeded', function () {
    $user = $this->createUser();
    $webhookId = (string) Str::uuid();
    $payload = dodoWebhookPayload('subscription.created', [
        'subscription_id' => 'sub_dedup_1',
        'product_id' => 'prod_pro_monthly',
        'status' => 'active',
        'customer' => ['customer_id' => 'cus_dedup_1', 'email' => $user->email],
    ]);

    $first = postDodoWebhook($payload, $webhookId);
    $first->assertStatus(200);

    $second = postDodoWebhook($payload, $webhookId);
    $second->assertStatus(200);

    expect(Subscription::query()->where('stripe_id', 'sub_dedup_1')->count())->toBe(1);
});

it('does not create a second active subscription when the user already has one', function () {
    $user = $this->createUser();
    $user->forceFill(['stripe_id' => 'cus_dup_active_1'])->save();
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_existing_active_1',
        'stripe_status' => 'active',
        'stripe_price' => 'prod_pro_monthly',
        'quantity' => 1,
    ]);

    $response = postDodoWebhook(dodoWebhookPayload('subscription.created', [
        'subscription_id' => 'sub_dup_active_2',
        'product_id' => 'prod_pro_monthly',
        'status' => 'active',
        'customer' => ['customer_id' => 'cus_dup_active_1', 'email' => $user->email],
    ]));

    $response->assertStatus(200);

    expect(Subscription::query()->where('stripe_id', 'sub_dup_active_2')->count())->toBe(0);
    expect($user->fresh()->subscriptions()->count())->toBe(1);
});

it('creates a new subscription once a previous one has been cancelled', function () {
    $user = $this->createUser();
    $user->forceFill(['stripe_id' => 'cus_after_cancel_1'])->save();
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_cancelled_before_1',
        'stripe_status' => 'cancelled',
        'stripe_price' => 'prod_pro_monthly',
        'quantity' => 1,
    ]);

    $response = postDodoWebhook(dodoWebhookPayload('subscription.created', [
        'subscription_id' => 'sub_after_cancel_1',
        'product_id' => 'prod_pro_monthly',
        'status' => 'active',
        'customer' => ['customer_id' => 'cus_after_cancel_1', 'email' => $user->email],
    ]));

    $response->assertStatus(200);

    expect(Subscription::query()->where('stripe_id', 'sub_after_cancel_1')->count())->toBe(1);
    expect($user->fresh()->subscriptions()->count())->toBe(2);
});

it('returns 400 when the customer cannot be resolved and processes the retry once the user exists', function () {
    $payload = dodoWebhookPayload('subscription.created', [
        'subscription_id' => 'sub_retry_1',
        'product_id' => 'prod_pro_monthly',
        'status' => 'active',
        'customer' => ['customer_id' => 'cus_retry_1', 'email' => 'late.user@example.com'],
    ]);
    $webhookId = (string) Str::uuid();

    $response = postDodoWebhook($payload, $webhookId);
    $response->assertStatus(400);
    expect(Subscription::query()->where('stripe_id', 'sub_retry_1')->count())->toBe(0);

    $this->createUser(['email' => 'late.user@example.com']);

    $retry = postDodoWebhook($payload, $webhookId);
    $retry->assertStatus(200);
    expect(Subscription::query()->where('stripe_id', 'sub_retry_1')->count())->toBe(1);
});

it('defaults subscription.created without an explicit status to active', function () {
    $user = $this->createUser();

    $response = postDodoWebhook(dodoWebhookPayload('subscription.created', [
        'subscription_id' => 'sub_no_status_1',
        'product_id' => 'prod_pro_monthly',
        'customer' => ['customer_id' => 'cus_no_status_1', 'email' => $user->email],
    ]));

    $response->assertStatus(200);

    $subscription = $user->fresh()->subscriptions()->where('stripe_id', 'sub_no_status_1')->first();
    expect($subscription->stripe_status)->toBe('active');
});

it('clears a stale trial date when the subscription is cancelled', function () {
    $user = $this->createUser();
    $user->forceFill(['stripe_id' => 'cus_cancel_1'])->save();
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_cancel_1',
        'stripe_status' => 'trialing',
        'stripe_price' => 'prod_pro_monthly',
        'quantity' => 1,
        'trial_ends_at' => now()->addDays(7),
    ]);

    $response = postDodoWebhook(dodoWebhookPayload('subscription.cancelled', [
        'subscription_id' => 'sub_cancel_1',
        'product_id' => 'prod_pro_monthly',
        'status' => 'cancelled',
        'customer' => ['customer_id' => 'cus_cancel_1', 'email' => $user->email],
    ]));

    $response->assertStatus(200);

    $subscription = $user->fresh()->subscriptions()->where('stripe_id', 'sub_cancel_1')->first();
    expect($subscription->stripe_status)->toBe('cancelled');
    expect($subscription->trial_ends_at)->toBeNull();
});

it('stores past due status and keeps the subscription valid during the grace window', function () {
    $user = $this->createUser();
    $user->forceFill(['stripe_id' => 'cus_past_due_1'])->save();
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_past_due_1',
        'stripe_status' => 'active',
        'stripe_price' => 'prod_pro_monthly',
        'quantity' => 1,
    ]);

    $response = postDodoWebhook(dodoWebhookPayload('subscription.past_due', [
        'subscription_id' => 'sub_past_due_1',
        'product_id' => 'prod_pro_monthly',
        'status' => 'past_due',
        'customer' => ['customer_id' => 'cus_past_due_1', 'email' => $user->email],
    ]));

    $response->assertStatus(200);

    $subscription = $user->fresh()->subscriptions()->where('stripe_id', 'sub_past_due_1')->first();
    expect($subscription->stripe_status)->toBe('past_due');
    expect($subscription->valid())->toBeTrue();
    expect($user->fresh()->hasActivePaidSubscription())->toBeFalse();
});