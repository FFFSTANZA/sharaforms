<?php

namespace App\Service\Billing;

use App\Models\Billing\Subscription;
use App\Models\User;
use App\Models\Workspace;
use App\Service\BillingHelper;
use App\Service\Billing\Data\BillingState;

class BillingStateResolver
{
    private const ACTIVE_STATUSES = ['trialing', 'active', 'on_hold'];

    public function __construct(protected PlanOverrideResolver $planOverrideResolver)
    {
    }

    public function resolveWorkspace(Workspace $workspace): BillingState
    {
        $state = $workspace->remember('billing_state', 15 * 60, function () use ($workspace) {
            $overrides = $this->planOverrideResolver->getEffectiveOverrides($workspace);
            $overrideTier = $overrides['tier'] ?? null;
            if (is_string($overrideTier) && in_array($overrideTier, PlanTier::all(), true)) {
                return new BillingState(
                    workspaceId: $workspace->id,
                    tier: $overrideTier,
                    isPaid: $overrideTier !== PlanTier::FREE,
                    hasOverrides: true,
                );
            }

            $owners = $workspace->relationLoaded('users')
                ? $workspace->users->where('pivot.role', User::ROLE_ADMIN)
                : $workspace->owners()->get();

            $highest = new BillingState(
                workspaceId: $workspace->id,
                tier: PlanTier::FREE,
                isPaid: false,
            );

            foreach ($owners as $owner) {
                $ownerState = $this->resolveUser($owner, $workspace->id);
                if ($this->compareStates($ownerState, $highest) > 0) {
                    $highest = $ownerState;
                }
            }

            return new BillingState(
                workspaceId: $workspace->id,
                tier: $highest->tier,
                isPaid: $highest->isPaid,
                interval: $highest->interval,
                stripeSubscriptionId: $highest->stripeSubscriptionId,
                stripePriceId: $highest->stripePriceId,
                subscriptionType: $highest->subscriptionType,
                isGrandfathered: $highest->isGrandfathered,
                hasOverrides: false,
            );
        });

        return $state;
    }

    public function resolveUser(User $user, ?int $workspaceId = null): BillingState
    {
        if (in_array($user->email, config('sharaforms.extra_pro_users_emails'), true)) {
            return new BillingState(
                workspaceId: $workspaceId,
                tier: PlanTier::PRO,
                isPaid: true,
            );
        }

        $subscription = $this->resolveActiveSubscription($user);
        if (!$subscription) {
            return new BillingState(
                workspaceId: $workspaceId,
                tier: PlanTier::FREE,
                isPaid: false,
            );
        }

        return $this->stateFromSubscription($subscription, $workspaceId);
    }

    public function resolveActiveSubscription(User $user): ?Subscription
    {
        $paidTypes = config('billing_state.subscription_types', ['default', 'pro', 'business', 'enterprise']);

        return $user->subscriptions()
            ->whereIn('type', $paidTypes)
            ->whereIn('stripe_status', self::ACTIVE_STATUSES)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }

    public function hasActivePaidSubscription(User $user): bool
    {
        return $this->resolveActiveSubscription($user) !== null;
    }

    public function isSubscribed(User|Workspace $subject): bool
    {
        if ($subject instanceof Workspace) {
            return $this->resolveWorkspace($subject)->isPaid;
        }

        return $this->resolveUser($subject)->isPaid;
    }

    public function isYearly(Workspace $workspace): bool
    {
        return $this->resolveWorkspace($workspace)->interval === 'yearly';
    }

    private function stateFromSubscription(Subscription $subscription, ?int $workspaceId): BillingState
    {
        $tier = BillingHelper::getTierForSubscription($subscription) ?? PlanTier::PRO;
        $interval = BillingHelper::getSubscriptionInterval($subscription);
        $priceId = $subscription->stripe_price;

        return new BillingState(
            workspaceId: $workspaceId,
            tier: $tier,
            isPaid: true,
            interval: $interval,
            stripeSubscriptionId: $subscription->stripe_id,
            stripePriceId: $priceId,
            subscriptionType: $subscription->type,
            isGrandfathered: BillingHelper::isGrandfatheredPriceId($priceId),
        );
    }

    private function compareStates(BillingState $left, BillingState $right): int
    {
        $leftOrder = PlanTier::ORDER[$left->tier] ?? -1;
        $rightOrder = PlanTier::ORDER[$right->tier] ?? -1;

        if ($leftOrder !== $rightOrder) {
            return $leftOrder <=> $rightOrder;
        }

        if ($left->isPaid !== $right->isPaid) {
            return $left->isPaid <=> $right->isPaid;
        }

        return strcmp($left->stripeSubscriptionId ?? '', $right->stripeSubscriptionId ?? '');
    }
}
