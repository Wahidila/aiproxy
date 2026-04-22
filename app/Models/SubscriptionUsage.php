<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionUsage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'subscription_id',
        'api_key_id',
        'model',
        'input_tokens',
        'output_tokens',
        'cost_usd',
        'cost_idr',
        'request_path',
        'status_code',
        'response_time_ms',
        'cycle_start',
        'created_at',
    ];

    protected $casts = [
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'cost_usd' => 'decimal:6',
        'cost_idr' => 'decimal:2',
        'status_code' => 'integer',
        'response_time_ms' => 'integer',
        'cycle_start' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(SubscriptionApiKey::class, 'api_key_id');
    }
}
