<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrialRequest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'status',
        'notes',
    ];

    /**
     * Scope: pending requests only.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: by status.
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInvited(): bool
    {
        return $this->status === 'invited';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function markInvited(): void
    {
        $this->update(['status' => 'invited']);
    }

    public function markRejected(?string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'notes' => $notes,
        ]);
    }
}
