<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\ModelPricing;
use App\Models\TokenUsage;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TokenTrackingService
{
    /**
     * Record usage and deduct IDR cost from wallet.
     */
    public function recordUsage(
        User $user,
        ApiKey $apiKey,
        string $model,
        int $inputTokens,
        int $outputTokens,
        string $requestPath,
        int $statusCode = 200,
        int $responseTimeMs = 0
    ): TokenUsage {
        $totalTokens = $inputTokens + $outputTokens;

        // Calculate IDR cost at current pricing (snapshot at time of request)
        $cost = $this->calculateRequestCost($model, $inputTokens, $outputTokens);

        $usage = TokenUsage::create([
            'user_id' => $user->id,
            'api_key_id' => $apiKey->id,
            'model' => $model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $totalTokens,
            'request_path' => $requestPath,
            'status_code' => $statusCode,
            'response_time_ms' => $responseTimeMs,
            'cost_idr' => $cost,
            'created_at' => now(),
        ]);

        // Deduct from wallet based on API key tier
        if ($cost > 0) {
            $quota = $user->getOrCreateQuota();
            $tier = $apiKey->tier ?? 'free';
            $description = "{$model}: {$inputTokens} in + {$outputTokens} out = Rp " . number_format($cost, 0, ',', '.');
            $quota->deductBalance($cost, $description, $usage, $tier);
        }

        // Update API key last used
        $apiKey->update(['last_used_at' => now()]);

        return $usage;
    }

    /**
     * Calculate IDR cost for a request based on model pricing.
     */
    public function calculateRequestCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = ModelPricing::findForModel($model);

        if (!$pricing) {
            // Fallback: use a default pricing if model not found
            Log::warning("No pricing found for model: {$model}, using zero cost");
            return 0;
        }

        return $pricing->calculateCost($inputTokens, $outputTokens);
    }

    /**
     * Check if user has sufficient wallet balance.
     */
    public function checkQuota(User $user): array
    {
        $quota = $user->getOrCreateQuota();

        return [
            'has_quota' => $quota->hasBalance(),
            'free_balance' => (float) $quota->free_balance,
            'paid_balance' => (float) $quota->paid_balance,
            'total_balance' => (float) $quota->total_balance,
            'free_credit_claimed' => $quota->free_credit_claimed,
        ];
    }

    /**
     * Get user stats for dashboard.
     */
    public function getUserStats(User $user, int $days = 30): array
    {
        $usages = $user->tokenUsages()
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $totalRequests = $usages->count();
        $totalInputTokens = $usages->sum('input_tokens');
        $totalOutputTokens = $usages->sum('output_tokens');
        $totalTokens = $usages->sum('total_tokens');
        $avgResponseTime = $totalRequests > 0 ? round($usages->avg('response_time_ms')) : 0;

        // Use stored cost_idr from each usage record (snapshot at time of request)
        $totalCostSpent = $usages->sum('cost_idr');

        $modelUsage = $usages->groupBy('model')->map(function ($group) {
            return [
                'requests' => $group->count(),
                'input_tokens' => $group->sum('input_tokens'),
                'output_tokens' => $group->sum('output_tokens'),
                'total_tokens' => $group->sum('total_tokens'),
                'cost_idr' => round($group->sum('cost_idr'), 2),
            ];
        })->sortByDesc('cost_idr');

        $favoriteModel = $modelUsage->keys()->first() ?? 'N/A';

        // Daily usage for chart
        $dailyUsage = $usages->groupBy(function ($usage) {
            return $usage->created_at->format('Y-m-d');
        })->map(function ($group) {
            return [
                'requests' => $group->count(),
                'input_tokens' => $group->sum('input_tokens'),
                'output_tokens' => $group->sum('output_tokens'),
                'total_tokens' => $group->sum('total_tokens'),
                'cost_idr' => round($group->sum('cost_idr'), 2),
            ];
        });

        return [
            'total_requests' => $totalRequests,
            'total_input_tokens' => $totalInputTokens,
            'total_output_tokens' => $totalOutputTokens,
            'total_tokens' => $totalTokens,
            'total_cost_spent' => round($totalCostSpent, 2),
            'avg_response_time' => $avgResponseTime,
            'favorite_model' => $favoriteModel,
            'model_usage' => $modelUsage->toArray(),
            'daily_usage' => $dailyUsage->toArray(),
        ];
    }
}
