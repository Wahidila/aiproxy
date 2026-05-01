<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionLimits
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip subscription limits if feature is disabled
        if (\App\Models\Setting::get('subscription_enabled', '0') != '1') {
            return $next($request);
        }

        $user = $request->get('_user');
        $apiKey = $request->get('_api_key');
        if (!$user) {
            return $next($request);
        }

        // Only apply subscription rate limits to subscription-tier API keys.
        // Free/paid keys use wallet balance and are not subject to subscription limits.
        if (!$apiKey || ($apiKey->tier ?? 'free') !== 'subscription') {
            return $next($request);
        }

        $subscription = $user->activeSubscription();
        $plan = $subscription ? $subscription->plan : \App\Models\SubscriptionPlan::getBySlug('free');

        if (!$plan) {
            return $next($request);
        }

        // 1. Check daily request limit
        if ($subscription && $subscription->isDailyLimitReached()) {
            return response()->json([
                'error' => [
                    'message' => 'Batas request harian tercapai. Upgrade plan untuk limit lebih tinggi.',
                    'type' => 'rate_limit_exceeded',
                    'code' => 'daily_limit_reached',
                    'plan' => $plan->slug,
                    'limit' => $plan->daily_request_limit,
                ]
            ], 429);
        }

        // 2. Check per-minute rate limit (Redis sliding window)
        $minuteKey = "rate:{$user->id}:minute";
        $currentMinute = (int) Redis::get($minuteKey) ?? 0;

        if ($currentMinute >= $plan->per_minute_limit) {
            return response()->json([
                'error' => [
                    'message' => 'Terlalu banyak request. Tunggu sebentar atau upgrade plan.',
                    'type' => 'rate_limit_exceeded',
                    'code' => 'per_minute_limit',
                    'plan' => $plan->slug,
                    'limit' => $plan->per_minute_limit,
                    'retry_after' => 60,
                ]
            ], 429)->header('Retry-After', 60);
        }

        // 3. Check concurrent request limit
        $concurrentKey = "concurrent:{$user->id}";
        $currentConcurrent = (int) Redis::get($concurrentKey) ?? 0;

        if ($currentConcurrent >= $plan->concurrent_limit) {
            return response()->json([
                'error' => [
                    'message' => 'Terlalu banyak request bersamaan. Tunggu request sebelumnya selesai.',
                    'type' => 'rate_limit_exceeded',
                    'code' => 'concurrent_limit',
                    'plan' => $plan->slug,
                    'limit' => $plan->concurrent_limit,
                ]
            ], 429);
        }

        // 4. Check token cap (daily plan)
        if ($subscription && $subscription->isTokenCapReached()) {
            return response()->json([
                'error' => [
                    'message' => 'Batas token harian tercapai (100M token). Beli paket harian baru.',
                    'type' => 'rate_limit_exceeded',
                    'code' => 'token_cap_reached',
                    'plan' => $plan->slug,
                ]
            ], 429);
        }

        // Increment counters
        Redis::incr($minuteKey);
        Redis::expire($minuteKey, 60); // TTL 60 seconds

        Redis::incr($concurrentKey);

        // Increment daily usage
        if ($subscription) {
            $subscription->incrementDailyUsage();
        }

        // Store plan info for later use
        $request->merge(['_subscription' => $subscription, '_plan' => $plan]);

        // Process request
        $response = $next($request);

        // Decrement concurrent counter after response
        Redis::decr($concurrentKey);

        return $response;
    }
}
