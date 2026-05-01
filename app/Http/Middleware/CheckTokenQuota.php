<?php

namespace App\Http\Middleware;

use App\Models\ModelPricing;
use App\Models\Setting;
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
        $requestedModel = $request->input('model');

        // ─── SUBSCRIPTION KEY: validate via subscription plan, NOT wallet ───
        if ($tier === 'subscription') {
            return $this->handleSubscriptionKey($request, $next, $user, $apiKey, $requestedModel);
        }

        // ─── FREE / PAID KEY: validate via wallet balance ───
        return $this->handleWalletKey($request, $next, $user, $apiKey, $tier, $requestedModel);
    }

    /**
     * Handle subscription-tier API keys.
     * These keys ONLY work with models allowed by the user's active subscription plan.
     * They do NOT use wallet balance — billing is via subscription plan.
     */
    private function handleSubscriptionKey(Request $request, Closure $next, $user, $apiKey, ?string $requestedModel): Response
    {
        // Subscription feature must be enabled
        if (Setting::get('subscription_enabled', '0') != '1') {
            return response()->json([
                'error' => [
                    'message' => 'Fitur subscription belum diaktifkan.',
                    'type' => 'feature_disabled',
                    'code' => 'subscription_disabled',
                ]
            ], 403);
        }

        // User must have an active subscription
        $subscription = $user->activeSubscription();
        if (!$subscription || !$subscription->isActive()) {
            return response()->json([
                'error' => [
                    'message' => 'Subscription Anda tidak aktif atau sudah expired. Silakan beli plan baru.',
                    'type' => 'subscription_expired',
                    'code' => 'no_active_subscription',
                ]
            ], 403);
        }

        $plan = $subscription->plan;

        // Check model access against subscription plan
        if ($requestedModel && $plan) {
            if (!$plan->hasModelAccess($requestedModel)) {
                $availableModels = $plan->getAccessibleModelIds();
                return response()->json([
                    'error' => [
                        'message' => "Model '{$requestedModel}' tidak tersedia untuk plan {$plan->name}. Upgrade plan untuk akses model ini.",
                        'type' => 'model_restricted',
                        'code' => 'plan_model_restricted',
                        'plan' => $plan->slug,
                        'available_models' => $availableModels,
                    ]
                ], 403);
            }
        }

        // Subscription key passes — rate limits handled by CheckSubscriptionLimits middleware
        return $next($request);
    }

    /**
     * Handle free/paid (wallet-based) API keys.
     * These keys use wallet balance and CANNOT access subscription-only models.
     */
    private function handleWalletKey(Request $request, Closure $next, $user, $apiKey, string $tier, ?string $requestedModel): Response
    {
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

            // ─── BLOCK free/paid keys from subscription-only models ───
            // If subscription is enabled and the model is ONLY in subscription plans
            // (not in free tier and not in paid tier pricing), block access.
            if (Setting::get('subscription_enabled', '0') == '1') {
                $plan = $user->getActivePlan();

                // Check if this model is a subscription-plan-only model
                // A model is subscription-only if:
                // 1. It exists in plan_model_access table, AND
                // 2. It does NOT exist in model_pricings as an active model
                $isInPlanAccess = \App\Models\PlanModelAccess::where('model_id', $requestedModel)->exists();
                $isInPricing = ModelPricing::where('model_id', $requestedModel)->where('is_active', true)->exists();

                if ($isInPlanAccess && !$isInPricing) {
                    return response()->json([
                        'error' => [
                            'message' => "Model '{$requestedModel}' hanya tersedia untuk subscription. Buat API key subscription untuk mengakses model ini.",
                            'type' => 'model_restricted',
                            'code' => 'subscription_only_model',
                            'tier' => $tier,
                            'hint' => 'Buat API key baru dengan tipe Subscription untuk mengakses model ini.',
                        ]
                    ], 403);
                }

                // Also block if model is in pricing but user's free/paid key tier doesn't match
                if ($isInPricing && $pricing) {
                    // Free tier key can only use free tier models
                    if ($tier === 'free' && !$pricing->is_free_tier) {
                        return response()->json([
                            'error' => [
                                'message' => "Model '{$requestedModel}' tidak tersedia untuk Free Tier. Gunakan API key Paid untuk model premium.",
                                'type' => 'model_restricted',
                                'code' => 'paid_model_only',
                                'tier' => $tier,
                            ]
                        ], 403);
                    }
                }
            }
        }

        return $next($request);
    }
}
