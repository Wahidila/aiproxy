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

        $tier = $apiKey->tier;
        $requestedModel = $request->input('model');

        // ─── SUBSCRIPTION KEY: validate via subscription plan, NOT wallet ───
        if ($tier === 'subscription') {
            return $this->handleSubscriptionKey($request, $next, $user, $apiKey, $requestedModel);
        }

        // ─── PAID KEY: validate via wallet balance ───
        return $this->handleWalletKey($request, $next, $user, $apiKey, $requestedModel);
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
     * Handle paid (wallet-based) API keys.
     * These keys use wallet balance and CANNOT access subscription-only models.
     */
    private function handleWalletKey(Request $request, Closure $next, $user, $apiKey, ?string $requestedModel): Response
    {
        $quota = $user->getOrCreateQuota();
        $paidBalance = (float) $quota->paid_balance;

        if ($paidBalance <= 0) {
            return response()->json([
                'error' => [
                    'message' => 'Saldo tidak mencukupi. Silakan top up saldo Anda.',
                    'type' => 'insufficient_balance',
                    'code' => 'insufficient_balance',
                    'paid_balance' => $paidBalance,
                ]
            ], 429);
        }

        if ($requestedModel) {
            $pricing = ModelPricing::findForModel($requestedModel);
            if ($pricing) {
                $minCost = $pricing->calculateCost(100, 1);
                if ($paidBalance < $minCost) {
                    return response()->json([
                        'error' => [
                            'message' => 'Saldo tidak mencukupi untuk model ini. Silakan top up saldo Anda.',
                            'type' => 'insufficient_balance',
                            'code' => 'insufficient_balance',
                            'paid_balance' => $paidBalance,
                            'min_cost_estimate' => $minCost,
                        ]
                    ], 429);
                }
            }

            if (Setting::get('subscription_enabled', '0') == '1') {
                $isInPlanAccess = \App\Models\PlanModelAccess::where('model_id', $requestedModel)->exists();
                $isInPricing = ModelPricing::where('model_id', $requestedModel)->where('is_active', true)->exists();

                if ($isInPlanAccess && !$isInPricing) {
                    return response()->json([
                        'error' => [
                            'message' => "Model '{$requestedModel}' hanya tersedia untuk subscription. Buat API key subscription untuk mengakses model ini.",
                            'type' => 'model_restricted',
                            'code' => 'subscription_only_model',
                            'hint' => 'Buat API key baru dengan tipe Subscription untuk mengakses model ini.',
                        ]
                    ], 403);
                }
            }
        }

        return $next($request);
    }
}
