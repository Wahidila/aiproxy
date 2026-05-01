<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TokenQuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'free_balance',
        'paid_balance',
        'free_credit_claimed',
        'free_tokens_used',
        'free_tokens_limit',
        'free_tokens_reset_at',
        'paid_tokens_used',
        'paid_tokens_limit',
        'paid_expires_at',
        'balance_alert_threshold',
        'balance_alert_enabled',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'free_balance' => 'decimal:2',
        'paid_balance' => 'decimal:2',
        'free_credit_claimed' => 'boolean',
        'free_tokens_used' => 'integer',
        'free_tokens_limit' => 'integer',
        'paid_tokens_used' => 'integer',
        'paid_tokens_limit' => 'integer',
        'free_tokens_reset_at' => 'datetime',
        'paid_expires_at' => 'datetime',
        'balance_alert_threshold' => 'integer',
        'balance_alert_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->user->walletTransactions();
    }

    // ===== WALLET METHODS =====

    /**
     * Check if user has any balance (either free or paid).
     */
    public function hasBalance(): bool
    {
        return $this->free_balance > 0 || $this->paid_balance > 0;
    }

    /**
     * Get total balance (free + paid).
     */
    public function getTotalBalanceAttribute(): float
    {
        return (float) $this->free_balance + (float) $this->paid_balance;
    }

    /**
     * Deduct from free balance. Always deducts (can go negative) to keep wallet in sync.
     * Returns true if balance was sufficient, false if it went negative.
     *
     * @deprecated Kept only for migration command to zero out free balances. Do not use for new code.
     */
    public function deductFreeBalance(float $amount, string $description, $reference = null): bool
    {
        return DB::transaction(function () use ($amount, $description, $reference) {
            $quota = static::where('id', $this->id)->lockForUpdate()->first();

            $wasSufficient = $quota->free_balance >= $amount;

            // Always deduct — even if it goes negative — to keep wallet transactions in sync with usage
            $quota->free_balance -= $amount;
            $quota->save();

            $this->free_balance = $quota->free_balance;

            WalletTransaction::create([
                'user_id' => $this->user_id,
                'type' => WalletTransaction::TYPE_USAGE,
                'amount' => -$amount,
                'balance_after' => $quota->free_balance + $quota->paid_balance,
                'description' => '[Free] ' . $description,
                'reference_id' => $reference?->id,
                'reference_type' => $reference ? get_class($reference) : null,
                'created_at' => now(),
            ]);

            return $wasSufficient;
        });
    }

    /**
     * Deduct from paid balance. Always deducts (can go negative) to keep wallet in sync.
     * Returns true if balance was sufficient, false if it went negative.
     */
    public function deductPaidBalance(float $amount, string $description, $reference = null): bool
    {
        return DB::transaction(function () use ($amount, $description, $reference) {
            $quota = static::where('id', $this->id)->lockForUpdate()->first();

            $wasSufficient = $quota->paid_balance >= $amount;

            // Always deduct — even if it goes negative — to keep wallet transactions in sync with usage
            $quota->paid_balance -= $amount;
            $quota->save();

            $this->paid_balance = $quota->paid_balance;

            WalletTransaction::create([
                'user_id' => $this->user_id,
                'type' => WalletTransaction::TYPE_USAGE,
                'amount' => -$amount,
                'balance_after' => $quota->free_balance + $quota->paid_balance,
                'description' => '[Paid] ' . $description,
                'reference_id' => $reference?->id,
                'reference_type' => $reference ? get_class($reference) : null,
                'created_at' => now(),
            ]);

            return $wasSufficient;
        });
    }

    /**
     * Deduct balance from paid wallet.
     * Returns 'paid' always (free tier removed).
     */
    public function deductBalance(float $amount, string $description, $reference = null, string $tier = 'paid'): string
    {
        $this->deductPaidBalance($amount, $description, $reference);
        return 'paid';
    }

    /**
     * Add to paid balance (topup).
     */
    public function addPaidBalance(float $amount, string $description, $reference = null): void
    {
        DB::transaction(function () use ($amount, $description, $reference) {
            $quota = static::where('id', $this->id)->lockForUpdate()->first();

            $quota->paid_balance += $amount;
            $quota->save();

            $this->paid_balance = $quota->paid_balance;

            WalletTransaction::create([
                'user_id' => $this->user_id,
                'type' => WalletTransaction::TYPE_TOPUP,
                'amount' => $amount,
                'balance_after' => $quota->free_balance + $quota->paid_balance,
                'description' => $description,
                'reference_id' => $reference?->id,
                'reference_type' => $reference ? get_class($reference) : null,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Add to free balance (admin adjustment).
     *
     * @deprecated Kept only for migration command to zero out free balances. Do not use for new code.
     */
    public function addFreeBalance(float $amount, string $type, string $description, $reference = null): void
    {
        DB::transaction(function () use ($amount, $type, $description, $reference) {
            $quota = static::where('id', $this->id)->lockForUpdate()->first();

            $quota->free_balance += $amount;
            $quota->save();

            $this->free_balance = $quota->free_balance;

            WalletTransaction::create([
                'user_id' => $this->user_id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $quota->free_balance + $quota->paid_balance,
                'description' => '[Free] ' . $description,
                'reference_id' => $reference?->id,
                'reference_type' => $reference ? get_class($reference) : null,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Add balance (generic - for admin adjustments).
     */
    public function addBalance(float $amount, string $type, string $description, $reference = null): void
    {
        // Topup goes to paid_balance, free_credit goes to free_balance, adjustments go to paid_balance
        $field = ($type === WalletTransaction::TYPE_FREE_CREDIT) ? 'free_balance' : 'paid_balance';

        DB::transaction(function () use ($amount, $type, $description, $reference, $field) {
            $quota = static::where('id', $this->id)->lockForUpdate()->first();

            $quota->$field += $amount;
            $quota->save();

            $this->$field = $quota->$field;

            WalletTransaction::create([
                'user_id' => $this->user_id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $quota->free_balance + $quota->paid_balance,
                'description' => $description,
                'reference_id' => $reference?->id,
                'reference_type' => $reference ? get_class($reference) : null,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Check if any balance (free or paid) is below the alert threshold.
     */
    public function isBelowThreshold(): bool
    {
        if (!$this->balance_alert_enabled) {
            return false;
        }

        $threshold = $this->balance_alert_threshold ?? 10000;

        return (float) $this->free_balance < $threshold || (float) $this->paid_balance < $threshold;
    }

    public function getFormattedFreeBalanceAttribute(): string
    {
        return 'Rp ' . number_format($this->free_balance, 0, ',', '.');
    }

    public function getFormattedPaidBalanceAttribute(): string
    {
        return 'Rp ' . number_format($this->paid_balance, 0, ',', '.');
    }

    public function getFormattedBalanceAttribute(): string
    {
        return 'Rp ' . number_format($this->total_balance, 0, ',', '.');
    }
}
