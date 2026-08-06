<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ImpersonationAudit;

class ImpersonationController extends Controller
{
    public function __construct()
    {
        $this->middleware('moderator');
    }

    public function impersonate(User $user)
    {
        if ($user->admin) {
            return $this->error([
                'message' => 'You cannot impersonate an admin.',
            ]);
        }

        AdminController::log('Impersonation started', [
            'impersonated_user' => $user->email . ' (' . $user->id . ')',
            'target_is_blocked' => $user->is_blocked,
        ]);

        ImpersonationAudit::record(
            impersonatorId: (int) auth()->id(),
            impersonatedUserId: (int) $user->id,
            action: 'started',
            request: request(),
            metadata: [
                'target_is_blocked' => $user->is_blocked,
                'target_email' => $user->email,
            ]
        );

        // Enhanced JWT claims: admins get admin_impersonating, moderators get impersonating
        $claims = auth()->user()->admin ? [
            'admin_impersonating' => true,
            'impersonator_id' => auth()->id(),
        ] : [
            'impersonating' => true,
            'impersonator_id' => auth()->id(),
        ];

        $token = auth()->claims($claims)->login($user);

        return $this->success([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->getPayload()->get('exp') - time(),
        ]);
    }
}
