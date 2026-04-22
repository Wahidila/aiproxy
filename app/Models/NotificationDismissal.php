<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDismissal extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'broadcast_notification_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(BroadcastNotification::class, 'broadcast_notification_id');
    }
}
