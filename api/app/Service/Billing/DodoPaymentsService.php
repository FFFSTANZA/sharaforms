<?php

namespace App\Service\Billing;

use App\Models\Billing\Subscription;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DodoPaymentsService
{
    public function isEnabled(): bool
    {
        return filled(config('dodo.api_key'));
    }

    public function isFullyConfigured(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        foreach (config('dodo.products', []) as $intervals) {
            foreach ((array) $intervals as $productId) {
                if (!filled($productId)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getCheckoutUrl(User $user, string $plan, string $interval, array $options = []): string
    {
        $payload = [
            'product_cart' => [[
                'product_id' => $this->getProductId($plan, $interval),
                'quantity' => 1,
            ]],
            'metadata' => [
                'user_id' => (string) $user->id,
                'plan' => $plan,
                'interval' => $interval,
            ],
        ];

        if (!empty($options['return_url'])) {
            $payload['return_url'] = $options['return_url'];
        }

        if (!empty($options['cancel_url'])) {
            $payload['cancel_url'] = $options['cancel_url'];
        }

        $payload['customer'] = $this->userHasDodoCustomer($user)
            ? array_filter([
                'customer_id' => $user->stripe_id,
                'email' => $user->email,
                'name' => $user->name,
            ])
            : array_filter([
                'email' => $user->email,
                'name' => $user->name,
            ]);

        $response = $this->request()->post('/checkouts', $payload);
        $this->throwIfFailed($response, 'Unable to start checkout process.');

        $checkoutUrl = $response->json('checkout_url');
        if (!is_string($checkoutUrl) || $checkoutUrl === '') {
            throw new \RuntimeException('Dodo checkout did not return a checkout_url.');
        }

        return $checkoutUrl;
    }

    public function createCustomerPortalUrl(User $user, ?string $returnUrl = null): string
    {
        if (!$this->userHasDodoCustomer($user)) {
            throw new \RuntimeException('Please subscribe before accessing your billing portal.');
        }

        $response = $this->request()->post(
            '/customers/' . $user->stripe_id . '/customer-portal/session' . $this->buildQuery([
                'return_url' => $returnUrl,
            ])
        );

        $this->throwIfFailed($response, 'Unable to access billing portal.');

        $portalUrl = $response->json('link');
        if (!is_string($portalUrl) || $portalUrl === '') {
            throw new \RuntimeException('Dodo portal did not return a link.');
        }

        return $portalUrl;
    }

    public function updateCustomer(User $user, string $email, string $name): string
    {
        if (!$this->userHasDodoCustomer($user)) {
            $response = $this->request()->post('/customers', [
                'email' => $email,
                'name' => $name,
                'metadata' => [
                    'user_id' => (string) $user->id,
                ],
            ]);

            $this->throwIfFailed($response, 'Unable to save billing details.');

            $customerId = $response->json('customer_id');
            if (!is_string($customerId) || $customerId === '') {
                throw new \RuntimeException('Dodo customer creation did not return a customer_id.');
            }

            return $customerId;
        }

        $response = $this->request()->patch('/customers/' . $user->stripe_id, [
            'email' => $email,
            'name' => $name,
        ]);

        $this->throwIfFailed($response, 'Unable to save billing details.');

        return (string) $user->stripe_id;
    }

    public function getCustomer(User $user): array
    {
        if (!$this->userHasDodoCustomer($user)) {
            return [
                'billing_email' => $user->email,
                'billing_name' => $user->name,
            ];
        }

        $response = $this->request()->get('/customers/' . $user->stripe_id);
        $this->throwIfFailed($response, 'Unable to load billing details.');

        return [
            'billing_email' => (string) ($response->json('email') ?? $user->email),
            'billing_name' => (string) ($response->json('name') ?? $user->name),
        ];
    }

    public function changePlan(Subscription $subscription, string $plan, string $interval): array
    {
        $productId = $this->getProductId($plan, $interval);

        $response = $this->request()->post('/subscriptions/' . $subscription->stripe_id . '/change-plan', [
            'product_id' => $productId,
            'quantity' => 1,
            'proration_billing_mode' => 'prorated_immediately',
            'effective_at' => 'immediately',
            'on_payment_failure' => 'prevent_change',
            'metadata' => [
                'plan' => $plan,
                'interval' => $interval,
            ],
        ]);

        $this->throwIfFailed($response, 'Unable to change plan.');

        $subscriptionData = $this->getSubscriptionDetails($subscription->stripe_id);

        if (!is_string($subscriptionData['product_id'] ?? null) || $subscriptionData['product_id'] === '') {
            Log::warning('Dodo change-plan response missing product_id.', [
                'subscription_id' => $subscription->stripe_id,
                'requested_plan' => $plan,
                'requested_interval' => $interval,
                'payload' => $subscriptionData,
            ]);

            throw new \RuntimeException('Your plan change could not be confirmed. Please try again or contact support.');
        }

        return $subscriptionData;
    }

    public function getSubscriptionDetails(string $subscriptionId): array
    {
        $response = $this->request()->get('/subscriptions/' . $subscriptionId);
        $this->throwIfFailed($response, 'Unable to load subscription details.');

        $payload = $response->json();

        return is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
    }

    public function syncSubscription(Subscription $subscription, array $data, ?User $user = null): void
    {
        $resolvedUser = $user ?? $subscription->user;
        $productId = $data['product_id'] ?? $subscription->stripe_price;
        $status = $data['status'] ?? $subscription->stripe_status ?? 'active';
        $trialEndsAt = $this->parseDate($data['trial_ends_at'] ?? null) ?? $this->resolveTrialEndsAt($data);
        $endsAt = $this->parseDate($data['ends_at'] ?? null) ?? $this->resolveEndsAt($data);

        if ($productId && $this->getIntervalByProductId($productId) === null) {
            Log::warning('Dodo subscription referenced an unknown product ID.', [
                'subscription_id' => $subscription->stripe_id,
                'product_id' => $productId,
            ]);
        }

        $subscription->forceFill([
            'type' => $this->getPlanNameByProductId($productId) ?? $subscription->type ?? 'pro',
            'stripe_status' => $status,
            'stripe_price' => $productId,
            'quantity' => max((int) ($data['quantity'] ?? $subscription->quantity ?? 1), 1),
            'trial_ends_at' => $trialEndsAt,
            'ends_at' => $endsAt,
        ]);

        if ($resolvedUser) {
            $subscription->user()->associate($resolvedUser);
        }

        $subscription->save();
    }

    public function getPlanNameByProductId(?string $productId): ?string
    {
        foreach (config('dodo.products', []) as $plan => $intervals) {
            foreach ($intervals as $configuredProductId) {
                if ($configuredProductId && $configuredProductId === $productId) {
                    return $plan;
                }
            }
        }

        return null;
    }

    public function getIntervalByProductId(?string $productId): ?string
    {
        foreach (config('dodo.products', []) as $intervals) {
            foreach ($intervals as $interval => $configuredProductId) {
                if ($configuredProductId && $configuredProductId === $productId) {
                    return $interval;
                }
            }
        }

        if ($productId) {
            Log::warning('Unable to map Dodo product ID to interval.', [
                'product_id' => $productId,
            ]);
        }

        return null;
    }

    public function userHasDodoCustomer(User $user): bool
    {
        return filled($user->stripe_id);
    }

    public function verifyWebhook(string $payload, array $headers): bool
    {
        $webhookId = Arr::first($headers['webhook-id'] ?? []);
        $webhookTimestamp = Arr::first($headers['webhook-timestamp'] ?? []);
        $webhookSignature = Arr::first($headers['webhook-signature'] ?? []);
        $secret = (string) config('dodo.webhook_key');

        if (!$webhookId || !$webhookTimestamp || !$webhookSignature || $secret === '') {
            return false;
        }

        $signedPayload = $webhookId . '.' . $webhookTimestamp . '.' . $payload;
        $digest = hash_hmac('sha256', $signedPayload, $secret, true);
        $base64Signature = base64_encode($digest);
        $hexSignature = bin2hex($digest);

        foreach ($this->parseSignatures($webhookSignature) as $candidate) {
            if (hash_equals($base64Signature, $candidate) || hash_equals($hexSignature, $candidate)) {
                return true;
            }
        }

        return false;
    }

    private function getProductId(string $plan, string $interval): string
    {
        $normalizedPlan = $plan === 'default' ? 'pro' : $plan;
        $productId = config('dodo.products.' . $normalizedPlan . '.' . $interval);

        if (!is_string($productId) || $productId === '') {
            throw new \InvalidArgumentException('Invalid pricing plan selected.');
        }

        return $productId;
    }

    private function request(): PendingRequest
    {
        if (!$this->isEnabled()) {
            throw new \RuntimeException('Billing is not configured.');
        }

        return Http::acceptJson()
            ->connectTimeout(5)
            ->timeout(15)
            ->baseUrl($this->getBaseUrl())
            ->withToken(config('dodo.api_key'));
    }

    private function getBaseUrl(): string
    {
        return rtrim((string) config('dodo.base_urls.' . config('dodo.environment', 'test_mode')), '/');
    }

    private function buildQuery(array $params): string
    {
        $query = http_build_query(array_filter($params, fn ($value) => $value !== null && $value !== ''));

        return $query === '' ? '' : '?' . $query;
    }

    private function throwIfFailed(Response $response, string $fallbackMessage): void
    {
        if ($response->successful()) {
            return;
        }

        $message = Arr::get($response->json(), 'message')
            ?? Arr::get($response->json(), 'error.message')
            ?? Arr::get($response->json(), 'error')
            ?? $fallbackMessage;

        throw new \RuntimeException((string) $message);
    }

    private function parseSignatures(string $header): array
    {
        return collect(explode(' ', str_replace(',', ' ', $header)))
            ->flatMap(function (string $part) {
                $trimmed = trim($part);
                if ($trimmed === '') {
                    return [];
                }

                return [Str::contains($trimmed, '=') ? trim(Str::after($trimmed, '=')) : $trimmed];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    private function resolveTrialEndsAt(array $data): ?Carbon
    {
        $trialDays = (int) ($data['trial_period_days'] ?? 0);
        $startDate = $data['previous_billing_date'] ?? $data['created_at'] ?? null;

        if ($trialDays <= 0 || !is_string($startDate) || $startDate === '') {
            return null;
        }

        return Carbon::parse($startDate)->addDays($trialDays);
    }

    private function resolveEndsAt(array $data): ?Carbon
    {
        if (($data['cancel_at_next_billing_date'] ?? false) && !empty($data['next_billing_date'])) {
            return Carbon::parse($data['next_billing_date']);
        }

        foreach (['ends_at', 'cancelled_at', 'canceled_at', 'expires_at'] as $field) {
            if (!empty($data[$field])) {
                return Carbon::parse($data[$field]);
            }
        }

        return null;
    }
}
