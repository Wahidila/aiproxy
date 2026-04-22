<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price_idr',
        'rpm_limit',
        'parallel_limit',
        'budget_usd_per_cycle',
        'cycle_hours',
        'allowed_models',
        'description',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_idr' => 'integer',
        'rpm_limit' => 'integer',
        'parallel_limit' => 'integer',
        'budget_usd_per_cycle' => 'decimal:2',
        'cycle_hours' => 'integer',
        'allowed_models' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id')->where('status', 'active');
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price_idr, 0, ',', '.');
    }
}
