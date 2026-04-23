<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'category',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_IN_PROGRESS,
        self::STATUS_CLOSED,
    ];

    public const CATEGORY_UMUM = 'umum';
    public const CATEGORY_TEKNIS = 'teknis';
    public const CATEGORY_PEMBAYARAN = 'pembayaran';
    public const CATEGORY_SARAN = 'saran';
    public const CATEGORY_LAINNYA = 'lainnya';

    public const CATEGORIES = [
        self::CATEGORY_UMUM,
        self::CATEGORY_TEKNIS,
        self::CATEGORY_PEMBAYARAN,
        self::CATEGORY_SARAN,
        self::CATEGORY_LAINNYA,
    ];

    public const CATEGORY_LABELS = [
        self::CATEGORY_UMUM => 'Umum',
        self::CATEGORY_TEKNIS => 'Masalah Teknis',
        self::CATEGORY_PEMBAYARAN => 'Pembayaran / Top Up',
        self::CATEGORY_SARAN => 'Saran / Feedback',
        self::CATEGORY_LAINNYA => 'Lainnya',
    ];

    public const STATUS_LABELS = [
        self::STATUS_OPEN => 'Open',
        self::STATUS_IN_PROGRESS => 'Diproses',
        self::STATUS_CLOSED => 'Selesai',
    ];

    /**
     * The user who created this ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All replies for this ticket.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class, 'ticket_id');
    }

    /**
     * Scope: only open tickets.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope: filter by status.
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: filter by category.
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function close(): void
    {
        $this->update(['status' => self::STATUS_CLOSED]);
    }

    public function markInProgress(): void
    {
        $this->update(['status' => self::STATUS_IN_PROGRESS]);
    }

    /**
     * Get the human-readable category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORY_LABELS[$this->category] ?? $this->category;
    }

    /**
     * Get the human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
