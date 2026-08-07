<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subscriptions\UpdateCustomerDetailsRequest;
use App\Models\Workspace;
use App\Service\Billing\BillingStateResolver;
use App\Service\Billing\DodoPaymentsService;
use App\Service\BillingHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function __construct(
        protected BillingStateResolver $billingStateResolver,
        protected DodoPaymentsService $dodoPaymentsService,
    )
    {
    }

    public const SUBSCRIPTION_PLANS = ['monthly', 'yearly'];

    public const SUBSCRIPTION_NAMES = [
        'default',
        'pro',
        'business',
        'enterprise',
    ];

    public function checkout($pricing, $plan)
    {
        $user = Auth::user();
        $lockKey = "subscription_checkout:{$user->id}";
        $checkoutLock = Cache::lock($lockKey, 30);

        if (!$checkoutLock->get()) {
            return $this->error([
                'message' => 'A checkout session is already being created. Please retry in a few seconds.',
            ], 429);
        }

        try {
            if ($this->billingStateResolver->hasActivePaidSubscription($user)) {
                return $this->error([
                    'message' => 'You already have an active subscription.',
                ]);
            }

            if ($user->subscriptions()->where('stripe_status', 'past_due')->first()) {
                return $this->error([
                    'message' => 'You already have a past due subscription. Please verify your details in the billing page, '
                        . 'and contact us if the issue persists.',
                ]);
            }

            $pricingConfig = BillingHelper::getPricing($pricing);
            if (!$pricingConfig || !isset($pricingConfig[$plan])) {
                return $this->error([
                    'message' => 'Invalid pricing plan selected.',
                ]);
            }

            try {
                $checkoutUrl = $this->dodoPaymentsService->getCheckoutUrl($user, $pricing, $plan, [
                    'return_url' => front_url('/subscriptions/success'),
                    'cancel_url' => front_url('/subscriptions/error'),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Unable to create Dodo checkout session.', [
                    'user_id' => $user->id,
                    'pricing' => $pricing,
                    'plan' => $plan,
                    'error' => $e->getMessage(),
                ]);

                return $this->error([
                    'message' => 'Billing is not configured yet. Please try again later.',
                ], 503);
            }

            return $this->success([
                'checkout_url' => $checkoutUrl,
            ]);
        } finally {
            $checkoutLock->release();
        }
    }

    public function updateCustomerDetails(UpdateCustomerDetailsRequest $request)
    {
        $user = Auth::user();
        $customerLock = Cache::lock("subscription_update_customer:{$user->id}", 30);

        if (!$customerLock->get()) {
            return $this->error([
                'message' => 'Billing details update is already in progress. Please retry in a few seconds.',
            ], 429);
        }

        try {
            $customerId = $this->dodoPaymentsService->updateCustomer($user, $request->email, $request->name);
            $user->forceFill([
                'stripe_id' => $customerId,
            ])->save();
        } catch (\Throwable $e) {
            Log::warning('Unable to update Dodo billing details.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return $this->error([
                'message' => 'Failed to save billing details. Please try again or contact support.',
            ], 503);
        } finally {
            $customerLock->release();
        }

        return $this->success([
            'message' => 'Details saved.',
        ]);
    }

    public function billingPortal()
    {
        if (!Auth::user()->has_customer_id) {
            return $this->error([
                'message' => 'Please subscribe before accessing your billing portal.',
            ]);
        }

        try {
            $portalUrl = $this->dodoPaymentsService->createCustomerPortalUrl(Auth::user(), front_url('/home'));
        } catch (\Throwable $e) {
            Log::warning('Unable to access Dodo billing portal.', [
                'user_id' => Auth::user()->id,
                'error' => $e->getMessage(),
            ]);

            return $this->error([
                'message' => 'Unable to access billing portal. Please try again or contact support.',
            ], 503);
        }

        return $this->success([
            'portal_url' => $portalUrl,
        ]);
    }

    public function changePlan(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:pro,business,enterprise',
            'interval' => 'required|string|in:monthly,yearly',
        ]);

        $user = Auth::user();
        $subscription = $this->billingStateResolver->resolveActiveSubscription($user);
        if (!$subscription) {
            return $this->error([
                'message' => 'No active subscription found. Please subscribe first.',
            ]);
        }

        $targetPlan = $request->input('plan');
        $targetInterval = $request->input('interval');

        $pricingConfig = BillingHelper::getPricing($targetPlan);
        if (!$pricingConfig || !isset($pricingConfig[$targetInterval])) {
            return $this->error([
                'message' => 'Invalid pricing plan selected.',
            ]);
        }

        $changePlanLock = Cache::lock("subscription_change_plan:{$user->id}", 30);
        if (!$changePlanLock->get()) {
            return $this->error([
                'message' => 'A plan change is already in progress. Please retry in a few seconds.',
            ], 429);
        }

        try {
            $subscriptionData = $this->dodoPaymentsService->changePlan($subscription, $targetPlan, $targetInterval);
            $this->dodoPaymentsService->syncSubscription($subscription, $subscriptionData);

            $user->flushCache();
        } catch (\Exception $e) {
            Log::warning('Failed to change subscription plan.', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->stripe_id,
                'plan' => $targetPlan,
                'interval' => $targetInterval,
                'error' => $e->getMessage(),
            ]);

            return $this->error([
                'message' => 'Failed to change plan. Please try again or contact support.',
            ]);
        } finally {
            $changePlanLock->release();
        }

        return $this->success([
            'message' => 'Your plan has been updated successfully.',
        ]);
    }

    public function upgradeToYearly(Request $request)
    {
        $request->validate([
            'workspace_id' => 'required|exists:workspaces,id',
        ]);

        $user = Auth::user();
        if (!$user->is_subscribed) {
            return $this->error([
                "message" => "Please subscribe before upgrading to yearly plan.",
            ]);
        }

        $workspace = Workspace::findOrFail($request->get("workspace_id"));
        if (!$workspace->isAdminUser($user)) {
            return $this->error([
                "message" => "Please ask an admin to upgrade the workspace to yearly plan.",
            ]);
        }

        if (!$workspace->owners()->where('users.id', $user->id)->exists()) {
            return $this->error([
                "message" => "You must be an owner of this workspace to upgrade its subscription.",
            ]);
        }

        if ($workspace->is_yearly_plan) {
            return $this->error([
                "message" => "The workspace is already on yearly plan.",
            ]);
        }

        $changePlanLock = Cache::lock("subscription_change_plan:{$user->id}", 30);
        if (!$changePlanLock->get()) {
            return $this->error([
                "message" => "A plan change is already in progress. Please retry in a few seconds.",
            ], 429);
        }

        try {
            $subscription = $this->billingStateResolver->resolveActiveSubscription($user);
            if (!$subscription) {
                return $this->error([
                    'message' => 'No active subscription found for this user.',
                ]);
            }

            $subscriptionType = $subscription->type ?? 'default';
            $subscriptionData = $this->dodoPaymentsService->changePlan($subscription, $subscriptionType, 'yearly');
            $this->dodoPaymentsService->syncSubscription($subscription, $subscriptionData);

            $user->flushCache();
            $workspace->flushWithOwners();
        } catch (\Exception $e) {
            Log::warning('Unable to upgrade subscription to yearly plan.', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->stripe_id ?? null,
                'workspace_id' => $workspace->id,
                'error' => $e->getMessage(),
            ]);

            return $this->error([
                "message" => "Failed to upgrade the subscription to yearly plan. Please try again or contact support.",
            ]);
        } finally {
            $changePlanLock->release();
        }

        return $this->success(['message' => 'Congratulations! Your plan has been upgraded to yearly.']);
    }
}
