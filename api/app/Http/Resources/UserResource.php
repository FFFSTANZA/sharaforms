<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user = $this->resource;

        $personalData = Auth::id() === $user->id ? [
            'plan_tier' => $user->plan_tier,
            'is_subscribed' => $user->is_subscribed,
            'admin' => $user->admin,
            'moderator' => $user->moderator,
            'template_editor' => $user->template_editor,
            'has_customer_id' => $user->has_customer_id,
            'has_forms' => $user->has_forms,
            'two_factor_enabled' => $user->hasTwoFactorEnabled(),
            'must_enable_two_factor' => ($user->admin || $user->moderator) && ! $user->hasTwoFactorEnabled(),
        ] : [];

        return array_merge(parent::toArray($request), $personalData);
    }
}
