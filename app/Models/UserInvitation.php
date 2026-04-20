<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserInvitation extends Model
{
    protected $fillable = [
        'email',
        'name',
        'token',
        'invited_by',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    /**
     * Boot: auto-generate token on creation.
     */
    protected static function booted(): void
    {
        static::creating(function (self $invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(64);
            }
            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addHours(72);
            }
        });
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isPending(): bool
    {
        return ! $this->isAccepted() && ! $this->isExpired();
    }

    /**
     * Mark invitation as accepted.
     */
    public function markAccepted(): void
    {
        $this->update(['accepted_at' => now()]);
    }

    /**
     * Refresh token and extend expiry (for resend).
     */
    public function refreshToken(): void
    {
        $this->update([
            'token' => Str::random(64),
            'expires_at' => now()->addHours(72),
        ]);
    }

    /**
     * Get the accept invitation URL.
     */
    public function getAcceptUrl(): string
    {
        return route('invitation.accept', $this->token);
    }

    /**
     * Scope: pending invitations only.
     */
    public function scopePending($query)
    {
        return $query->whereNull('accepted_at')
                     ->where('expires_at', '>', now());
    }

    /**
     * Scope: by email.
     */
    public function scopeForEmail($query, string $email)
    {
        return $query->where('email', $email);
    }
}
