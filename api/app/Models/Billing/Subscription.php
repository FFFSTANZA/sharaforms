<?php

namespace App\Models\Billing;

use App\Events\Billing\SubscriptionCreated;
use App\Events\Billing\SubscriptionUpdated;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'quantity',
        'trial_ends_at',
        'ends_at',
    ];

    protected $dispatchesEvents = [
        'created' => SubscriptionCreated::class,
        'updated' => SubscriptionUpdated::class,
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function valid(): bool
    {
        return $this->active() || $this->onTrial() || $this->onGracePeriod() || $this->onHold() || $this->pastDue() || $this->paused();
    }

    public function active(): bool
    {
        return $this->stripe_status === 'active';
    }

    public function onHold(): bool
    {
        return $this->stripe_status === 'on_hold';
    }

    public function pastDue(): bool
    {
        return $this->stripe_status === 'past_due';
    }

    public function paused(): bool
    {
        return $this->stripe_status === 'paused';
    }

    public function onTrial(): bool
    {
        return in_array($this->stripe_status, ['trialing', 'active'], true)
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function onGracePeriod(): bool
    {
        return $this->ends_at && $this->ends_at->isFuture();
    }

    public static function booted(): void
    {
        static::saved(function (Subscription $sub) {
            $sub->user?->flushCache();
        });
        static::deleted(function (Subscription $sub) {
            $sub->user?->flushCache();
        });
    }
}
