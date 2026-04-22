<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BroadcastNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'is_active',
        'created_by',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public const TYPE_INFO = 'info';
    public const TYPE_WARNING = 'warning';
    public const TYPE_SUCCESS = 'success';
    public const TYPE_DANGER = 'danger';

    public const TYPES = [
        self::TYPE_INFO,
        self::TYPE_WARNING,
        self::TYPE_SUCCESS,
        self::TYPE_DANGER,
    ];

    /**
     * The admin who created this notification.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * All dismissals for this notification.
     */
    public function dismissals(): HasMany
    {
        return $this->hasMany(NotificationDismissal::class);
    }

    /**
     * Scope: only active notifications that haven't expired.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: active notifications not dismissed by a specific user.
     */
    public function scopeVisibleTo($query, User $user)
    {
        return $query->active()
            ->whereDoesntHave('dismissals', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'desc');
    }

    /**
     * Check if this notification has been dismissed by a user.
     */
    public function isDismissedBy(User $user): bool
    {
        return $this->dismissals()->where('user_id', $user->id)->exists();
    }
}
