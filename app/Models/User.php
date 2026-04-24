<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the user's currently active subscription.
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
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

    /**
     * Get or create wallet (token_quota record with balance).
     * Auto-claims free credit on first creation.
     */
    public function getOrCreateQuota(): TokenQuota
    {
        if ($this->tokenQuota) {
            return $this->tokenQuota;
        }

        $quota = $this->tokenQuota()->create([
            'balance' => 0,
            'free_credit_claimed' => false,
        ]);

        // Auto-claim free credit for new users
        $quota->claimFreeCredit();

        return $quota->fresh();
    }
}
