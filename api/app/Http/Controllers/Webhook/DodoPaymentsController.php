<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\Billing\RemoveWorkspaceGuests;
use App\Models\User;
use App\Service\Billing\DodoPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DodoPaymentsController extends Controller
{
    public function __construct(protected DodoPaymentsService $dodoPaymentsService)
    {
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $webhookId = $request->header('webhook-id');

        if (!$this->dodoPaymentsService->verifyWebhook($payload, $request->headers->all())) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        if ($webhookId && !Cache::add('dodo_webhook:' . $webhookId, true, now()->addDay())) {
            return response()->json(['received' => true]);
        }

        $event = json_decode($payload, true);
        if (!is_array($event)) {
            return response()->json(['message' => 'Invalid payload.'], 400);
        }

        $eventType = $event['type'] ?? null;
        if (is_string($eventType) && str_starts_with($eventType, 'subscription.')) {
            $this->syncSubscription($eventType, $event['data'] ?? []);
        }

        return response()->json(['received' => true]);
    }

    private function syncSubscription(string $eventType, array $data): void
    {
        $subscriptionId = $data['subscription_id'] ?? null;
        $productId = $data['product_id'] ?? null;
        $customer = is_array($data['customer'] ?? null) ? $data['customer'] : [];
        $customerId = $customer['customer_id'] ?? null;
        $customerEmail = $customer['email'] ?? null;

        if (!$subscriptionId || !$productId) {
            Log::warning('Ignoring Dodo webhook without subscription identifiers.', [
                'event_type' => $eventType,
            ]);

            return;
        }

        $user = $this->resolveUser($customerId, $customerEmail);
        if (!$user) {
            Log::warning('Unable to resolve user for Dodo subscription webhook.', [
                'event_type' => $eventType,
                'subscription_id' => $subscriptionId,
                'customer_id' => $customerId,
                'customer_email' => $customerEmail,
            ]);

            return;
        }

        if ($customerId && $user->stripe_id !== $customerId) {
            $user->forceFill([
                'stripe_id' => $customerId,
            ])->save();
        }

        $subscription = $user->subscriptions()->firstOrNew([
            'stripe_id' => $subscriptionId,
        ]);

        $newStatus = $this->resolveStatus($eventType, $data, $subscription->stripe_status);

        $this->dodoPaymentsService->syncSubscription(
            $subscription,
            array_merge($data, [
                'status' => $newStatus,
                'trial_ends_at' => $this->resolveTrialEndsAt($data)?->toIso8601String(),
                'ends_at' => $this->resolveEndsAt($data)?->toIso8601String(),
            ]),
            $user,
        );

        if (!$subscription->valid()) {
            RemoveWorkspaceGuests::dispatch($user);
        }
    }

    private function resolveUser(?string $customerId, ?string $email): ?User
    {
        if ($customerId) {
            $user = User::query()->where('stripe_id', $customerId)->first();
            if ($user) {
                return $user;
            }
        }

        if ($email) {
            return User::query()->where('email', $email)->first();
        }

        return null;
    }

    private function resolveStatus(string $eventType, array $data, ?string $currentStatus = null): string
    {
        $resolvedStatus = $data['status']
            ?? match ($eventType) {
                'subscription.active', 'subscription.renewed', 'subscription.plan_changed', 'subscription.updated' => 'active',
                'subscription.cancelled' => 'cancelled',
                'subscription.failed' => 'failed',
                'subscription.expired' => 'expired',
                'subscription.on_hold' => 'on_hold',
                'subscription.past_due' => 'past_due',
                'subscription.paused' => 'paused',
                'subscription.incomplete' => 'incomplete',
                default => null,
            };

        if ($resolvedStatus === null) {
            Log::warning('Unhandled Dodo subscription webhook status.', [
                'event_type' => $eventType,
            ]);
        }

        return $resolvedStatus ?? $currentStatus ?? 'incomplete';
    }

    private function resolveTrialEndsAt(array $data): ?Carbon
    {
        $trialDays = (int) ($data['trial_period_days'] ?? 0);
        $previousBillingDate = $data['previous_billing_date'] ?? $data['created_at'] ?? null;

        if ($trialDays <= 0 || !$previousBillingDate) {
            return null;
        }

        return Carbon::parse($previousBillingDate)->addDays($trialDays);
    }

    private function resolveEndsAt(array $data): ?Carbon
    {
        if (($data['cancel_at_next_billing_date'] ?? false) && !empty($data['next_billing_date'])) {
            return Carbon::parse($data['next_billing_date']);
        }

        if (!empty($data['cancelled_at'])) {
            return Carbon::parse($data['cancelled_at']);
        }

        if (!empty($data['canceled_at'])) {
            return Carbon::parse($data['canceled_at']);
        }

        if (!empty($data['expires_at'])) {
            return Carbon::parse($data['expires_at']);
        }

        return null;
    }
}
