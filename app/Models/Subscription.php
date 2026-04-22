<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'starts_at',
        'expires_at',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(SubscriptionApiKey::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(SubscriptionUsage::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get the start of the current budget cycle.
     * Cycles are at 00:00, 06:00, 12:00, 18:00 WIB.
     */
    public function getCurrentCycleStart(): \Carbon\Carbon
    {
        $now = now();
        $cycleHours = $this->plan->cycle_hours ?? 6;
        $hour = $now->hour;
        $cycleStartHour = intdiv($hour, $cycleHours) * $cycleHours;

        return $now->copy()->setTime($cycleStartHour, 0, 0);
    }

    /**
     * Get total cost spent in the current budget cycle.
     */
    public function getCurrentCycleCostUsd(): float
    {
        $cycleStart = $this->getCurrentCycleStart();

        return (float) $this->usages()
            ->where('cycle_start', $cycleStart)
            ->sum('cost_usd');
    }

    /**
     * Get remaining budget in the current cycle.
     */
    public function getRemainingBudgetUsd(): float
    {
        $budget = (float) $this->plan->budget_usd_per_cycle;
        $spent = $this->getCurrentCycleCostUsd();

        return max(0, $budget - $spent);
    }
}
