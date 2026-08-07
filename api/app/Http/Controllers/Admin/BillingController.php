<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Service\Billing\DodoPaymentsService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(protected DodoPaymentsService $dodoPaymentsService)
    {
        $this->middleware('moderator');
    }

    public function getCustomer(User $user)
    {
        if (!$user->has_customer_id) {
            return $this->error([
                "message" => "Billing customer not created",
            ]);
        }

        return $this->success($this->dodoPaymentsService->getCustomer($user));
    }

    public function updateCustomer(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'billing_email' => 'required|email',
            'billing_name' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($request->get("user_id"));

        if (!$user->has_customer_id) {
            return $this->error([
                "message" => "Billing customer not created",
            ]);
        }

        $customerId = $this->dodoPaymentsService->updateCustomer($user, $request->billing_email, $request->billing_name);

        $user->forceFill([
            'stripe_id' => $customerId,
        ])->save();

        return $this->success(['message' => 'Billing info updated successfully']);
    }

    public function getSubscriptions(User $user)
    {
        if (!$user->has_customer_id) {
            return $this->error([
                "message" => "Billing customer not created",
            ]);
        }
        $subscriptions = $user->subscriptions()->latest()->take(100)->get()->map(function ($subscription) use ($user) {
            return  [
                "id" => $subscription->id,
                "stripe_id" => $subscription->stripe_id,
                "name" => ucfirst($user->name),
                "plan" => $subscription->type,
                "status" => $subscription->stripe_status,
                "creation_date" => $subscription->created_at->format('Y-m-d'),
                "canceled_at" => $subscription->ends_at ? $subscription->ends_at->format('Y-m-d') : null,
            ];
        });
        return $this->success([
            'subscriptions'  =>  $subscriptions,
        ]);
    }

    public function getPayments(User $user)
    {
        if (!$user->has_customer_id) {
            return $this->error([
                "message" => "Billing customer not created",
            ]);
        }

        return $this->success([
            'payments' => $this->dodoPaymentsService->getPayments($user),
        ]);
    }
}
