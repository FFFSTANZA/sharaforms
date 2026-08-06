<?php

namespace App\Service;

use App\Models\Billing\Subscription as BillingSubscription;
use App\Service\Billing\DodoPaymentsService;
use App\Service\Billing\PlanTier;

class BillingHelper
{
    public static function getPricing($productName = 'default')
    {
        return config('dodo.products.' . ($productName === 'default' ? 'pro' : $productName));
    }

    public static function getTierForSubscription(BillingSubscription $subscription): ?string
    {
        $mapping = config('billing_state.product_tier_mapping', []);

        return $mapping[$subscription->type] ?? config('plans.subscription_tier_mapping.' . $subscription->type, PlanTier::PRO);
    }

    public static function isGrandfatheredPriceId(?string $priceId): bool
    {
        if (!$priceId) {
            return false;
        }

        return in_array($priceId, config('billing_state.grandfathered_prices', []), true);
    }

    public static function getSubscriptionInterval(BillingSubscription $subscription): ?string
    {
        return app(DodoPaymentsService::class)->getIntervalByProductId($subscription->stripe_price);
    }
}
