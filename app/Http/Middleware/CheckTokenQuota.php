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

        // Check balance for the API key's tier
        if (!$quota->hasBalanceForTier($tier)) {
            $balanceField = $tier === 'free' ? 'free_balance' : 'paid_balance';
            return response()->json([
                'error' => [
                    'message' => $tier === 'free'
                        ? 'Saldo free trial habis. Silakan top up untuk melanjutkan.'
                        : 'Saldo tidak mencukupi. Silakan top up saldo Anda.',
                    'type' => 'insufficient_balance',
                    'code' => 'insufficient_balance',
                    'tier' => $tier,
                    'balance' => (float) $quota->$balanceField,
                ]
            ], 429);
        }

        // Check model restriction for free tier API keys
        $requestedModel = $request->input('model');
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
