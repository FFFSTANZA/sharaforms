<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserBlockRequest;
use App\Jobs\Template\GenerateTemplateJob;
use App\Models\Billing\Subscription;
use App\Models\Forms\Form;
use App\Models\User;
use App\Service\Billing\DodoPaymentsService;
use App\Service\UserActionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class AdminController extends Controller
{
    public const ADMIN_LOG_PREFIX = '[admin_action] ';

    public function __construct()
    {
        $this->middleware('moderator');
    }

    public function createTemplate(Request $request)
    {
        $request->validate([
            'template_prompt' => 'required|string|max:4000'
        ]);

        $job = new GenerateTemplateJob($request->template_prompt);
        $job->handle();

        return $this->success([
            'template_slug' => $job->generatedTemplate?->slug ?? null
        ]);
    }

    public function fetchUser($identifier)
    {
        $user = null;
        if (is_numeric($identifier)) {
            $user = User::find($identifier);
        } elseif (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = User::whereEmail($identifier)->first();
        } else {
            // Find by form slug
            $form = Form::whereSlug($identifier)->first();
            if ($form) {
                $user = $form->creator;
            }
        }

        if (!$user) {
            return $this->error([
                'message' => 'User not found.'
            ]);
        } elseif ($user->admin) {
            return $this->error([
                'message' => 'You cannot fetch an admin.'
            ]);
        }

        $user->makeVisible('meta');

        // Get two-factor authentication status
        $user->two_factor_enabled = $user->hasTwoFactorEnabled();

        $workspaces = $user->workspaces()
            ->withCount('forms')
            ->get()
            ->map(function ($workspace) {
                return [
                    'id' => $workspace->id,
                    'name' => $workspace->name,
                    'plan_tier' => $workspace->plan_tier,
                    'is_trialing' => $workspace->is_trialing,
                    'forms_count' => $workspace->forms_count
                ];
            });
        return $this->success([
            'user' => $user,
            'workspaces' => $workspaces
        ]);
    }

    public function blockUser(UserBlockRequest $request, UserActionService $userActionService)
    {
        $user = User::findOrFail($request->get('user_id'));

        if ($user->admin) {
            return $this->error([
                'message' => 'You cannot block an admin.'
            ]);
        }

        $user = $userActionService->block(
            $user,
            $request->get('reason'),
            request()->user()->id
        );

        return $this->success([
            "message" => "User has been blocked.",
            "user" => $user->makeVisible('meta'),
        ]);
    }

    public function unblockUser(UserBlockRequest $request, UserActionService $userActionService)
    {
        $user = User::findOrFail($request->get('user_id'));

        $user = $userActionService->unblock(
            $user,
            $request->get('reason'),
            request()->user()->id
        );

        return $this->success([
            "message" => "User has been unblocked.",
            "user" => $user->makeVisible('meta'),
        ]);
    }

    public function cancelSubscription(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'subscription_id' => 'required',
            'cancellation_reason' => 'required'
        ]);
        $user = User::find($request->get("user_id"));
        $subscription = $user->subscriptions()->find($request->get("subscription_id"));

        if (! $subscription) {
            return $this->error([
                "message" => "Subscription not found."
            ], 404);
        }

        if ($subscription && !in_array($subscription->stripe_status, ['active', 'trialing', 'on_hold', 'paused'])) {
            return $this->error([
                "message" => "The subscription is not active, trialing, on hold or paused. "
            ]);
        }

        try {
            app(DodoPaymentsService::class)->cancelSubscription($subscription);
        } catch (\Throwable $e) {
            Log::warning('Failed to cancel subscription.', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->stripe_id,
                'error' => $e->getMessage(),
            ]);

            return $this->error([
                'message' => 'Failed to cancel subscription. Please try again or contact support.',
            ], 502);
        }

        self::log('Cancelled subscription', [
            'user_id' => $user->id,
            'subscription_id' => $subscription->stripe_id,
        ]);

        return $this->success([
            'message' => 'Subscription has been cancelled. It will end at the next billing date.',
        ]);
    }

    public function sendPasswordResetEmail(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            return $this->error([
                'message' => "Password reset email failed to send"
            ]);
        }

        self::log('Sent password reset email', [
            'user_id' => $user->id,
        ]);

        return $this->success([
            'message' => "Password reset email has been sent to the user's email address"
        ]);
    }

    public function refundPayment(Request $request)
    {
        return $this->error([
            'message' => 'Refunds are not supported from this admin panel.',
        ], 422);
    }

    public function disableTwoFactorAuthentication(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'reason' => 'required'
        ]);

        $user = User::findOrFail($request->get('user_id'));

        if ($user->admin) {
            return $this->error([
                'message' => 'You cannot disable 2FA for an admin.'
            ]);
        }

        if (!$user->hasTwoFactorEnabled()) {
            return $this->error([
                'message' => "Two-factor authentication is not enabled."
            ]);
        }

        // Disable 2FA
        $user->disableTwoFactorAuth();

        self::log('Disable Two-Factor Authentication ', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'reason' => $request->get('reason')
        ]);

        $user->two_factor_enabled = false;

        return $this->success([
            'message' => "Two-factor authentication has been disabled successfully.",
            'user' => $user->makeVisible('meta')
        ]);
    }

    public function clearUserCache(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id'
        ]);

        $user = User::findOrFail($request->get('user_id'));
        $user->flushCache();

        self::log('Clear user cache', [
            'user_id' => $user->id,
        ]);

        return $this->success([
            'message' => 'User cache cleared.',
        ]);
    }

    public static function log($message, $data = [])
    {
        $moderator = request()->user();

        // Always include moderator information
        $baseData = [
            'moderator' => $moderator->email . ' (' . $moderator->id . ')',
        ];

        // Add action button for admin panel if user_id or target_id is present
        if (isset($data['user_id'])) {
            $baseData['actions'] = [
                'Open Admin' => front_url('/settings/admin?user_id=' . $data['user_id'])
            ];
        } elseif (isset($data['target_id'])) {
            $baseData['actions'] = [
                'Open Admin' => front_url('/settings/admin?user_id=' . $data['target_id'])
            ];
        }

        // Merge with provided data (provided data takes precedence)
        $logData = array_merge($baseData, $data);

        Log::warning(self::ADMIN_LOG_PREFIX . $message, $data);
        Log::channel('slack_admin')->warning($message, $logData);
    }
}
