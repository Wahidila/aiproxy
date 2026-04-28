<?php

namespace App\Http\Middleware;

use App\Models\ModelPricing;
use App\Services\TokenTrackingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenQuota
{
    public function __construct(private TokenTrackingService $trackingService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->get('_user');
        $apiKey = $request->get('_api_key');

        if (!$user || !$apiKey) {
            return response()->json([
                'error' => [
                    'message' => 'User not found.',
                    'type' => 'authentication_error',
                ]
            ], 401);
        }

        $tier = $apiKey->tier ?? 'free';
        $quota = $user->getOrCreateQuota();
        $paidBalance = (float) $quota->paid_balance;
        $freeBalance = (float) $quota->free_balance;

        // Determine effective balance based on tier
        // For paid tier: paid + free (fallback), for free tier: free only
        if ($tier === 'free') {
            $effectiveBalance = $freeBalance;
        } else {
            // Paid tier can fall back to free balance when paid is exhausted
            $effectiveBalance = max($paidBalance, 0) + max($freeBalance, 0);
        }

        // Block if no effective balance available
        if ($effectiveBalance <= 0) {
            return response()->json([
                'error' => [
                    'message' => $tier === 'free'
                        ? 'Saldo free trial habis. Silakan top up untuk melanjutkan.'
                        : 'Saldo tidak mencukupi. Silakan top up saldo Anda.',
                    'type' => 'insufficient_balance',
                    'code' => 'insufficient_balance',
                    'tier' => $tier,
                    'paid_balance' => $paidBalance,
                    'free_balance' => $freeBalance,
                ]
            ], 429);
        }

        // Estimate minimum cost for the requested model and check if balance is sufficient
        $requestedModel = $request->input('model');
        if ($requestedModel) {
            $pricing = ModelPricing::findForModel($requestedModel);
            if ($pricing) {
                // Estimate minimum cost: at least 100 input tokens + 1 output token
                $minCost = $pricing->calculateCost(100, 1);
                if ($effectiveBalance < $minCost) {
                    return response()->json([
                        'error' => [
                            'message' => 'Saldo tidak mencukupi untuk model ini. Silakan top up saldo Anda.',
                            'type' => 'insufficient_balance',
                            'code' => 'insufficient_balance',
                            'tier' => $tier,
                            'paid_balance' => $paidBalance,
                            'free_balance' => $freeBalance,
                            'min_cost_estimate' => $minCost,
                        ]
                    ], 429);
                }
            }
        }

        // Check model restriction for free tier API keys
        if ($requestedModel && $tier === 'free') {
            if (!ModelPricing::isFreeTierModel($requestedModel)) {
                $freeModels = ModelPricing::getFreeTierModelIds();
                return response()->json([
                    'error' => [
                        'message' => "Model '{$requestedModel}' tidak tersedia untuk API key free tier. Gunakan API key paid atau top up saldo.",
                        'type' => 'model_restricted',
                        'code' => 'free_tier_model_restricted',
                        'available_models' => $freeModels,
                    ]
                ], 403);
            }
        }

        return $next($request);
    }
}
