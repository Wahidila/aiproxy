<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiKey extends Model
{
    use HasFactory;

    const TIER_PAID = 'paid';
    const TIER_SUBSCRIPTION = 'subscription';

    protected $fillable = [
        'user_id',
        'key',
        'name',
        'tier',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function isPaid(): bool
    {
        return $this->tier === self::TIER_PAID;
    }

    public function isSubscription(): bool
    {
        return $this->tier === self::TIER_SUBSCRIPTION;
    }

    /**
     * Check if this key uses wallet balance (paid tier).
     */
    public function isWalletBased(): bool
    {
        return $this->tier === self::TIER_PAID;
    }

    public function getTierLabelAttribute(): string
    {
        return match ($this->tier) {
            self::TIER_PAID => 'Pay-as-you-go',
            self::TIER_SUBSCRIPTION => 'Subscription',
            default => ucfirst($this->tier),
        };
    }

    protected $hidden = [
        'key',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tokenUsages(): HasMany
    {
        return $this->hasMany(TokenUsage::class);
    }

    public static function generateKey(): string
    {
        return 'sk-' . bin2hex(random_bytes(32));
    }

    public function getMaskedKeyAttribute(): string
    {
        return substr($this->key, 0, 7) . '...' . substr($this->key, -4);
    }
}
