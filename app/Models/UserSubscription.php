<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id', 'plan_slug', 'status', 'starts_at', 'expires_at',
        'token_usage_total', 'daily_requests_used', 'daily_requests_reset_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'daily_requests_reset_at' => 'datetime',
        'token_usage_total' => 'integer',
        'daily_requests_used' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_slug', 'slug');
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        return true;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check and reset daily counter if needed.
     */
    public function checkDailyReset(): void
    {
        if (!$this->daily_requests_reset_at || $this->daily_requests_reset_at->isPast()) {
            $this->update([
                'daily_requests_used' => 0,
                'daily_requests_reset_at' => now()->endOfDay(),
            ]);
        }
    }

    /**
     * Increment daily request counter.
     */
    public function incrementDailyUsage(): void
    {
        $this->increment('daily_requests_used');
    }

    /**
     * Increment total token usage (for daily plan cap).
     */
    public function incrementTokenUsage(int $tokens): void
    {
        $this->increment('token_usage_total', $tokens);
    }

    /**
     * Check if daily request limit is reached.
     */
    public function isDailyLimitReached(): bool
    {
        $plan = $this->plan;
        if ($plan->isUnlimitedRequests()) return false;

        $this->checkDailyReset();
        return $this->daily_requests_used >= $plan->daily_request_limit;
    }

    /**
     * Check if token usage cap is reached (for daily plan).
     */
    public function isTokenCapReached(): bool
    {
        $plan = $this->plan;
        if (!$plan->max_token_usage) return false;

        return $this->token_usage_total >= $plan->max_token_usage;
    }
}
