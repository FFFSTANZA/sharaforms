<?php

namespace App\Models;

use App\Models\Billing\Subscription;
use App\Models\Forms\Form;
use App\Models\Traits\CachableAttributes;
use App\Models\Traits\CachesAttributes;
use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use App\Service\Billing\BillingStateResolver;
use App\Service\Billing\DodoPaymentsService;
use App\Service\Billing\PlanAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laragear\TwoFactor\Contracts\TwoFactorAuthenticatable as TwoFactorAuthenticatableContract;
use Laragear\TwoFactor\TwoFactorAuthentication;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property-read bool $admin
 * @property-read bool $moderator
 * @property-read bool $template_editor
 * @property-read bool $has_forms
 * @property-read bool $is_subscribed
 * @property-read bool $has_customer_id
 * @property-read string $plan_tier
 * @property-read bool $is_blocked
 * @property-read bool $is_risky
 * @property array<string, mixed>|null $utm_data
 * @property array<string, mixed>|null $meta
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $blocked_at
 * @property-read \Illuminate\Support\Collection<int, Workspace> $workspaces
 * @property-read \Illuminate\Support\Collection<int, Form> $forms
 * @property-read \Illuminate\Support\Collection<int, Subscription> $subscriptions
 * @method bool hasTwoFactorEnabled()
 */
class User extends Authenticatable implements JWTSubject, CachableAttributes, TwoFactorAuthenticatableContract
{
    use HasFactory;
    use Notifiable;
    use HasApiTokens;
    use CachesAttributes;
    use TwoFactorAuthentication;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';
    public const ROLE_READONLY = 'readonly';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_USER,
        self::ROLE_READONLY,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'hear_about_us',
        'utm_data',
        'meta',
        'blocked_at'
    ];

    protected $dispatchesEvents = [
        'created' => \App\Events\Models\UserCreated::class,
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'hear_about_us',
        'meta'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected function casts()
    {
        return [
            'email_verified_at' => 'datetime',
            'utm_data' => 'array',
            'meta' => 'array',
            'blocked_at' => 'datetime',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'photo_url',
        'is_blocked'
    ];

    protected $cachableAttributes = [
        'has_forms',
        'is_subscribed',
        'plan_tier',
    ];

    public static function fingerprintUserAgent(?string $userAgent): string
    {
        return hash_hmac('sha256', (string) $userAgent, (string) config('app.key', ''));
    }

    public function ownsForm(Form $form)
    {
        // Use loaded relationship if available to avoid queries
        if ($this->relationLoaded('workspaces')) {
            return $this->workspaces->contains('id', $form->workspace_id);
        }

        return $this->workspaces()->where('workspaces.id', $form->workspace_id)->exists();
    }

    public function ownsWorkspace(Workspace $workspace)
    {
        // Use loaded relationship if available to avoid queries
        if ($this->relationLoaded('workspaces')) {
            return $this->workspaces->contains('id', $workspace->id);
        }

        return $this->workspaces()->where('workspaces.id', $workspace->id)->exists();
    }

    /**
     * Get the profile photo URL attribute.
     *
     * @return string
     */
    public function getPhotoUrlAttribute()
    {
        return vsprintf('https://www.gravatar.com/avatar/%s.jpg?s=200&d=%s', [
            md5(strtolower($this->email)),
            $this->name ? urlencode("https://ui-avatars.com/api/$this->name.jpg") : 'mp',
        ]);
    }

    public function getHasFormsAttribute()
    {
        return $this->remember('has_forms', 10 * 60, function (): bool {
            // Use loaded relationship if available to avoid queries
            if ($this->relationLoaded('workspaces')) {
                return $this->workspaces->some(function ($workspace) {
                    // If workspace has forms relationship loaded, use it
                    if ($workspace->relationLoaded('forms')) {
                        return $workspace->forms->isNotEmpty();
                    }
                    // Otherwise fall back to database query for this workspace
                    return $workspace->forms()->exists();
                });
            }

            return $this->workspaces()->whereHas('forms')->exists();
        });
    }

    public function getIsSubscribedAttribute()
    {
        if (!pricing_enabled()) {
            return true;
        }

        return $this->remember('is_subscribed', 5 * 60, function (): bool {
            return app(BillingStateResolver::class)->hasActivePaidSubscription($this)
                || in_array($this->email, config('sharaforms.extra_pro_users_emails'));
        });
    }

    public function getHasCustomerIdAttribute()
    {
        return !is_null($this->stripe_id);
    }

    public function getAdminAttribute()
    {
        return in_array($this->email, config('sharaforms.admin_emails'));
    }

    public function getModeratorAttribute()
    {
        return in_array($this->email, config('sharaforms.moderator_emails')) || $this->admin;
    }

    public function getTemplateEditorAttribute()
    {
        return $this->admin || in_array($this->email, config('sharaforms.template_editor_emails'));
    }

    /**
     * Get the user's current plan tier.
     * This is the SINGLE source of truth for plan status.
     *
     * @return string One of: 'free', 'pro', 'business', 'enterprise'
     */
    public function getPlanTierAttribute(): string
    {
        return app(PlanAccessService::class)->getUserTier($this);
    }

    public function getIsBlockedAttribute()
    {
        return !is_null($this->blocked_at);
    }

    public function blockUser(string $reason, ?int $moderatorId): void
    {
        $this->blocked_at = now();
        $history = is_array($this->meta['blocking_history'] ?? null) ? $this->meta['blocking_history'] : [];
        $history[] = [
            'reason' => $reason,
            'blocked_at' => $this->blocked_at,
            'blocked_by' => $moderatorId ?? null,
            'unblock_reason' => null,
            'unblocked_at' => null,
            'unblocked_by' => null,
        ];
        $this->meta = array_merge($this->meta ?? [], ['blocking_history' => $history]);
        $this->save();
    }

    public function unblockUser(string $reason, int $moderatorId): void
    {
        $this->blocked_at = null;
        $history = is_array($this->meta['blocking_history'] ?? null) ? $this->meta['blocking_history'] : [];
        if (empty($history)) {
            $this->save();
            return;
        }

        $lastBlockKey = array_key_last($history);
        $history[$lastBlockKey]['unblock_reason'] = $reason;
        $history[$lastBlockKey]['unblocked_at'] = now();
        $history[$lastBlockKey]['unblocked_by'] = $moderatorId;

        $this->meta = array_merge($this->meta ?? [], ['blocking_history' => $history]);
        $this->save();
    }

    public function getLastBlock(): ?array
    {
        $history = is_array($this->meta['blocking_history'] ?? null) ? $this->meta['blocking_history'] : [];
        if (empty($history)) {
            return null;
        }

        return end($history);
    }

    /**
     * =================================
     *  Helper Related
     * =================================
     */

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }

    /**
     * =================================
     *  Relationship
     * =================================
     */
    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class)->withPivot('role');
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class, 'creator_id');
    }

    public function formTemplates()
    {
        return $this->hasMany(Template::class, 'creator_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Whether the user has any active paid subscription (default, pro, business, enterprise).
     */
    public function hasActivePaidSubscription(): bool
    {
        return app(BillingStateResolver::class)->hasActivePaidSubscription($this);
    }

    public function activePaidSubscription(): ?Subscription
    {
        return app(BillingStateResolver::class)->resolveActiveSubscription($this);
    }

    public function onTrial(): bool
    {
        return $this->subscriptions()
            ->whereIn('stripe_status', ['trialing', 'active'])
            ->where('trial_ends_at', '>', now())
            ->exists();
    }

    /**
     * =================================
     *  Oauth Related
     * =================================
     */

    /**
     * Get the oauth providers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function oauthProviders()
    {
        return $this->hasMany(OAuthProvider::class);
    }

    /**
     * Get the OIDC user identities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userIdentities()
    {
        return $this->hasMany(\App\Enterprise\Oidc\Models\UserIdentity::class, 'user_id');
    }

    /**
     * @return int
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'ua' => self::fingerprintUserAgent(request()->userAgent()),
        ];
    }

    public function getIsRiskyAttribute()
    {
        return $this->created_at?->isAfter(now()->subDays(3)) ?? false;
    }

    public function flushCache()
    {
        // Clear user's own cached attributes
        $this->flush();

        // Clear related workspace caches
        $this->workspaces()->with('forms')->get()->each(function (Workspace $workspace) {
            $workspace->flush();
            $workspace->forms->each(function (Form $form) {
                $form->flush();
            });
        });
    }

    public static function boot()
    {
        parent::boot();
        static::deleting(function (User $user) {
            // Delete all OAuth providers for this user
            $user->oauthProviders()->delete();

            // Cancel all Dodo subscriptions before deleting the rows
            $dodoPaymentsService = app(DodoPaymentsService::class);
            foreach ($user->subscriptions()->get() as $subscription) {
                try {
                    $dodoPaymentsService->cancelSubscription($subscription);
                } catch (\Throwable $e) {
                    Log::warning('Failed to cancel Dodo subscription during account deletion.', [
                        'user_id' => $user->id,
                        'subscription_id' => $subscription->stripe_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            $user->subscriptions()->delete();

            // Remove user's workspace if he's the only one with this workspace
            foreach ($user->workspaces as $workspace) {
                if ($workspace->users()->count() == 1) {
                    $workspace->delete();
                } else {
                    $workspace->users()->detach($user->id);
                }
            }
        });
    }

    public function scopeWithActiveSubscription(Builder $query): Builder
    {
        return $query->whereHas('subscriptions', function (Builder $query): void {
            $query->where(function (Builder $q): void {
                $q->where('stripe_status', 'trialing')
                    ->orWhere('stripe_status', 'active')
                    ->orWhere('stripe_status', 'on_hold');
            });
        });
    }
}
