<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelPricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_id',
        'model_name',
        'input_price_usd',
        'output_price_usd',
        'discount_percent',
        'is_free_tier',
        'is_active',
    ];

    protected $casts = [
        'input_price_usd' => 'decimal:4',
        'output_price_usd' => 'decimal:4',
        'discount_percent' => 'integer',
        'is_free_tier' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get USD to IDR exchange rate from settings.
     */
    public static function getExchangeRate(): float
    {
        return (float) Setting::get('usd_to_idr_rate', 16500);
    }

    /**
     * Get input price in IDR per 1M tokens.
     */
    public function getInputPriceIdrAttribute(): float
    {
        return round($this->input_price_usd * static::getExchangeRate(), 2);
    }

    /**
     * Get output price in IDR per 1M tokens.
     */
    public function getOutputPriceIdrAttribute(): float
    {
        return round($this->output_price_usd * static::getExchangeRate(), 2);
    }

    /**
     * Calculate cost in IDR for given token counts.
     * Returns cost after discount.
     */
    public function calculateCost(int $inputTokens, int $outputTokens): float
    {
        $rate = static::getExchangeRate();

        // Cost per token = (USD per 1M tokens) * exchange_rate / 1,000,000
        $inputCost = ($inputTokens / 1_000_000) * $this->input_price_usd * $rate;
        $outputCost = ($outputTokens / 1_000_000) * $this->output_price_usd * $rate;

        $totalCost = $inputCost + $outputCost;

        // Apply discount
        if ($this->discount_percent > 0) {
            $totalCost = $totalCost * (1 - $this->discount_percent / 100);
        }

        return round($totalCost, 2);
    }

    /**
     * Find pricing for a model, with fallback to default pricing.
     */
    public static function findForModel(string $modelId): ?self
    {
        return static::where('model_id', $modelId)->where('is_active', true)->first();
    }

    /**
     * Get all free tier model IDs.
     */
    public static function getFreeTierModelIds(): array
    {
        return static::where('is_free_tier', true)
            ->where('is_active', true)
            ->pluck('model_id')
            ->toArray();
    }

    /**
     * Check if a model is available in free tier.
     */
    public static function isFreeTierModel(string $modelId): bool
    {
        return static::where('model_id', $modelId)
            ->where('is_free_tier', true)
            ->where('is_active', true)
            ->exists();
    }
}
