<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'slug', 'name', 'type', 'price_idr',
        'daily_request_limit', 'per_minute_limit', 'concurrent_limit',
        'max_token_usage', 'allowed_models', 'features', 'is_popular', 'sort_order', 'tier_level',
    ];

    protected $casts = [
        'features' => 'array',
        'allowed_models' => 'array',
        'is_popular' => 'boolean',
        'daily_request_limit' => 'integer',
        'per_minute_limit' => 'integer',
        'concurrent_limit' => 'integer',
        'max_token_usage' => 'integer',
        'tier_level' => 'integer',
    ];

    /**
     * Get daily price for prorated upgrade calculation.
     * Monthly plans: price_idr / 30
     * Daily plans: price_idr
     */
    public function getDailyPriceAttribute(): float
    {
        if ($this->type === 'daily') {
            return (float) $this->price_idr;
        }
        return round((float) $this->price_idr / 30, 2);
    }

    /**
     * Check if this plan is higher tier than another.
     */
    public function isHigherTierThan(SubscriptionPlan $other): bool
    {
        return $this->tier_level > $other->tier_level;
    }

    /**
     * Check if this plan is same tier as another.
     */
    public function isSameTierAs(SubscriptionPlan $other): bool
    {
        return $this->tier_level === $other->tier_level;
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'plan_slug', 'slug');
    }

    public function modelAccess(): HasMany
    {
        return $this->hasMany(PlanModelAccess::class, 'plan_slug', 'slug');
    }

    public function getAccessibleModelIds(): array
    {
        return $this->modelAccess()->pluck('model_id')->toArray();
    }

    public function hasModelAccess(string $modelId): bool
    {
        return $this->modelAccess()->where('model_id', $modelId)->exists();
    }

    public static function getBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public function isUnlimitedRequests(): bool
    {
        return $this->daily_request_limit === null;
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price_idr === 0) return 'Rp 0';
        return 'Rp ' . number_format($this->price_idr, 0, ',', '.');
    }
}
