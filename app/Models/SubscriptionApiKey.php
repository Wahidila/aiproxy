<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SubscriptionApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_id',
        'key',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(SubscriptionUsage::class, 'api_key_id');
    }

    /**
     * Generate a new subscription API key.
     */
    public static function generateKey(): string
    {
        return 'sk-sub-' . Str::random(32);
    }

    /**
     * Get masked key for display (show first 7 and last 4 chars).
     */
    public function getMaskedKeyAttribute(): string
    {
        $key = $this->key;
        if (strlen($key) <= 15) {
            return $key;
        }
        return substr($key, 0, 10) . '...' . substr($key, -4);
    }
}
