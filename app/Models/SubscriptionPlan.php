<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'slug', 'name', 'type', 'price_idr',
        'daily_request_limit', 'per_minute_limit', 'concurrent_limit',
        'max_token_usage', 'features', 'is_popular', 'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_popular' => 'boolean',
        'daily_request_limit' => 'integer',
        'per_minute_limit' => 'integer',
        'concurrent_limit' => 'integer',
        'max_token_usage' => 'integer',
    ];

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
