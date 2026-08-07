<?php

use App\Service\Billing\DodoPaymentsService;
use Illuminate\Support\Str;

uses(\Tests\TestCase::class);

beforeEach(function () {
    config()->set('dodo.webhook_key', 'whsec_'.Str::random(32));
});

function validDodoWebhookHeaders(string $payload, string $secret, string $id, ?int $timestamp = null): array
{
    if (str_starts_with($secret, 'whsec_')) {
        $secret = (string) base64_decode(substr($secret, 6), true);
    }

    $timestamp ??= time();
    $signedPayload = $id.'.'.$timestamp.'.'.$payload;
    $signature = base64_encode(hash_hmac('sha256', $signedPayload, $secret, true));

    return [
        'webhook-id' => [$id],
        'webhook-timestamp' => [(string) $timestamp],
        'webhook-signature' => ['v1,'.$signature],
    ];
}

function dodoVerifyWebhook(string $payload, array $headers): bool
{
    return (new DodoPaymentsService())->verifyWebhook($payload, $headers);
}

it('accepts a valid signature with a fresh timestamp', function () {
    $payload = json_encode(['event' => 'subscription.created']);

    $headers = validDodoWebhookHeaders(
        $payload,
        (string) config('dodo.webhook_key'),
        (string) Str::uuid(),
        time()
    );

    expect(dodoVerifyWebhook($payload, $headers))->toBeTrue();
});

it('rejects a valid signature when the timestamp is older than the tolerance', function () {
    $payload = json_encode(['event' => 'subscription.created']);

    $headers = validDodoWebhookHeaders(
        $payload,
        (string) config('dodo.webhook_key'),
        (string) Str::uuid(),
        time() - 301
    );

    expect(dodoVerifyWebhook($payload, $headers))->toBeFalse();
});

it('accepts a signature at the old tolerance boundary', function () {
    $payload = json_encode(['event' => 'subscription.created']);

    $headers = validDodoWebhookHeaders(
        $payload,
        (string) config('dodo.webhook_key'),
        (string) Str::uuid(),
        time() - 300
    );

    expect(dodoVerifyWebhook($payload, $headers))->toBeTrue();
});

it('rejects a valid signature when the timestamp is too far in the future', function () {
    $payload = json_encode(['event' => 'subscription.created']);

    $headers = validDodoWebhookHeaders(
        $payload,
        (string) config('dodo.webhook_key'),
        (string) Str::uuid(),
        time() + 301
    );

    expect(dodoVerifyWebhook($payload, $headers))->toBeFalse();
});

it('rejects a non-numeric timestamp', function () {
    $payload = json_encode(['event' => 'subscription.created']);

    $headers = validDodoWebhookHeaders(
        $payload,
        (string) config('dodo.webhook_key'),
        (string) Str::uuid(),
        time()
    );
    $headers['webhook-timestamp'] = ['not-a-timestamp'];

    expect(dodoVerifyWebhook($payload, $headers))->toBeFalse();
});

it('rejects a missing timestamp header', function () {
    $payload = json_encode(['event' => 'subscription.created']);

    $headers = validDodoWebhookHeaders(
        $payload,
        (string) config('dodo.webhook_key'),
        (string) Str::uuid(),
        time()
    );
    unset($headers['webhook-timestamp']);

    expect(dodoVerifyWebhook($payload, $headers))->toBeFalse();
});

it('rejects a valid signature when the header timestamp does not match the signed message', function () {
    $payload = json_encode(['event' => 'subscription.created']);

    $headers = validDodoWebhookHeaders(
        $payload,
        (string) config('dodo.webhook_key'),
        (string) Str::uuid(),
        time() - 200
    );
    $headers['webhook-timestamp'] = [(string) (time() - 100)];

    expect(dodoVerifyWebhook($payload, $headers))->toBeFalse();
});

it('rejects a tampered signature even with a fresh timestamp', function () {
    $payload = json_encode(['event' => 'subscription.created']);

    $headers = validDodoWebhookHeaders(
        $payload,
        (string) config('dodo.webhook_key'),
        (string) Str::uuid(),
        time()
    );
    $headers['webhook-signature'] = ['v1,Zm9vYmFy'];

    expect(dodoVerifyWebhook($payload, $headers))->toBeFalse();
});