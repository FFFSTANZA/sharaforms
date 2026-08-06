<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Billing\Subscription;
use App\Models\Forms\Form;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Get authenticated user.
     */
    public function current(Request $request)
    {
        // Eager load workspaces to prevent N+1 queries in UserResource
        /** @var User $user */
        $user = $request->user()->load([
            'workspaces' => function ($query) {
                $query->withPivot('role');
            }
        ]);

        return new UserResource($user);
    }

    public function exportPersonalData(Request $request)
    {
        /** @var User $user */
        $user = $request->user()->load([
            'workspaces' => function ($query) {
                $query->withPivot('role');
            },
            'forms:id,workspace_id,creator_id,title,slug,created_at,updated_at',
            'subscriptions',
        ]);

        $formatDate = static fn ($value): ?string => $value?->toIso8601String();

        $payload = [
            'generated_at' => Carbon::now()->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'hear_about_us' => $user->hear_about_us,
                'utm_data' => $user->utm_data,
                'meta' => $user->meta,
                'email_verified_at' => $formatDate($user->email_verified_at),
                'created_at' => $formatDate($user->created_at),
                'updated_at' => $formatDate($user->updated_at),
                'plan_tier' => $user->plan_tier,
                'is_blocked' => $user->is_blocked,
            ],
            'workspaces' => $user->workspaces->map(function (Workspace $workspace) use ($formatDate) {
                $pivot = $workspace->getRelation('pivot');

                return [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'role' => $pivot instanceof Pivot ? $pivot->getAttribute('role') : null,
                'created_at' => $formatDate($workspace->created_at),
                'updated_at' => $formatDate($workspace->updated_at),
                ];
            })->values(),
            'forms' => $user->forms->map(fn (Form $form) => [
                'id' => $form->id,
                'workspace_id' => $form->workspace_id,
                'title' => $form->title,
                'slug' => $form->slug,
                'created_at' => $formatDate($form->created_at),
                'updated_at' => $formatDate($form->updated_at),
            ])->values(),
            'subscriptions' => $user->subscriptions->map(fn (Subscription $subscription) => [
                'id' => $subscription->id,
                'type' => $subscription->type,
                'stripe_status' => $subscription->stripe_status,
                'stripe_price' => $subscription->stripe_price,
                'quantity' => $subscription->quantity,
                'trial_ends_at' => $formatDate($subscription->trial_ends_at),
                'ends_at' => $formatDate($subscription->ends_at),
                'created_at' => $formatDate($subscription->created_at),
                'updated_at' => $formatDate($subscription->updated_at),
            ])->values(),
        ];

        return response()->json($payload);
    }

    public function deleteAccount()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->admin) {
            return $this->error([
                'message' => 'Cannot delete an admin. Stay with us 🙏',
            ]);
        }

        $user->delete();

        return $this->success([
            'message' => 'Sorry to see you go 👋',
        ]);
    }
}
