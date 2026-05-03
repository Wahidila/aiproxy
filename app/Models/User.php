<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'role',
        'is_banned',
        'banned_at',
        'ban_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_banned' => 'boolean',
        'banned_at' => 'datetime',
    ];

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function tokenUsages(): HasMany
    {
        return $this->hasMany(TokenUsage::class);
    }

    public function tokenQuota(): HasOne
    {
        return $this->hasOne(TokenQuota::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBanned(): bool
    {
        return $this->is_banned;
    }

    public function ban(string $reason = ''): void
    {
        $this->update([
            'is_banned' => true,
            'banned_at' => now(),
            'ban_reason' => $reason,
        ]);

        // Revoke/delete all API keys
        $this->apiKeys()->delete();
    }

    public function unban(): void
    {
        $this->update([
            'is_banned' => false,
            'banned_at' => null,
            'ban_reason' => null,
        ]);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscription(): ?UserSubscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->latest('starts_at')
            ->first();
    }

    public function getActivePlan(): SubscriptionPlan
    {
        $sub = $this->activeSubscription();
        if ($sub) {
            return $sub->plan;
        }
        // Default: free plan
        return SubscriptionPlan::getBySlug('free') ?? new SubscriptionPlan([
            'slug' => 'free',
            'name' => 'FREE',
            'daily_request_limit' => 50,
            'per_minute_limit' => 6,
            'concurrent_limit' => 1,
        ]);
    }

    /**
     * Assign a subscription plan to user.
     */
    public function subscribeTo(string $planSlug, ?Carbon $expiresAt = null): UserSubscription
    {
        // Cancel existing active subscriptions
        $this->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);

        return $this->subscriptions()->create([
            'plan_slug' => $planSlug,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => $expiresAt,
            'daily_requests_used' => 0,
            'daily_requests_reset_at' => now()->endOfDay(),
        ]);
    }

    /**
     * Get or create wallet (token_quota record with balance).
     */
    public function getOrCreateQuota()
    {
        $quota = $this->tokenQuota;
        if (!$quota) {
            $quota = TokenQuota::create([
                'user_id' => $this->id,
                'free_balance' => 0,
                'paid_balance' => 0,
                'free_credit_claimed' => true,
            ]);
            $this->setRelation('tokenQuota', $quota);
        }
        return $quota;
    }
}
