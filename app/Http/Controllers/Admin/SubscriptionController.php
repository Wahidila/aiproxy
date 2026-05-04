<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModelPricing;
use App\Models\SubscriptionPlan;
use App\Models\TokenUsage;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = UserSubscription::with(['user', 'plan']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by plan
        if ($request->filled('plan')) {
            $query->where('plan_slug', $request->plan);
        }

        // Search by user email/name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->latest()->paginate(20)->withQueryString();
        $plans = SubscriptionPlan::orderBy('sort_order')->get();

        // Stats
        $stats = [
            'total_active' => UserSubscription::where('status', 'active')->count(),
            'total_expired' => UserSubscription::where('status', 'expired')->count(),
            'total_cancelled' => UserSubscription::where('status', 'cancelled')->count(),
            'monthly_revenue' => UserSubscription::where('status', 'active')
                ->join('subscription_plans', 'user_subscriptions.plan_slug', '=', 'subscription_plans.slug')
                ->where('subscription_plans.type', 'monthly')
                ->sum('subscription_plans.price_idr'),
            'daily_revenue' => UserSubscription::where('status', 'active')
                ->join('subscription_plans', 'user_subscriptions.plan_slug', '=', 'subscription_plans.slug')
                ->where('subscription_plans.type', 'daily')
                ->sum('subscription_plans.price_idr'),
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'plans', 'stats'));
    }

    public function show(User $user)
    {
        // Active subscription
        $subscription = $user->activeSubscription();
        $plan = $user->getActivePlan();
        $quota = $user->getOrCreateQuota();

        // Token usage per day (last 30 days)
        $dailyTokens = TokenUsage::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(input_tokens) as total_input'),
                DB::raw('SUM(output_tokens) as total_output'),
                DB::raw('SUM(total_tokens) as total_tokens'),
                DB::raw('SUM(cost_idr) as total_cost'),
                DB::raw('COUNT(*) as request_count')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('date')
            ->get();

        // Model usage breakdown
        $modelUsage = TokenUsage::where('user_id', $user->id)
            ->select(
                'model',
                DB::raw('COUNT(*) as request_count'),
                DB::raw('SUM(total_tokens) as total_tokens'),
                DB::raw('SUM(cost_idr) as total_cost')
            )
            ->groupBy('model')
            ->orderByDesc('request_count')
            ->get();

        // Rate limit status
        $rateLimitStatus = [
            'per_minute_limit' => $plan->per_minute_limit,
            'daily_request_limit' => $plan->daily_request_limit,
            'daily_requests_used' => $subscription ? $subscription->daily_requests_used : 0,
            'requests_last_minute' => TokenUsage::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subMinute())
                ->count(),
        ];

        // Recent activity (last 20 requests)
        $recentActivity = TokenUsage::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Usage trend (last 7 days) for bar chart
        $usageTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayData = $dailyTokens->firstWhere('date', $date);
            $usageTrend[] = [
                'date' => $date,
                'label' => now()->subDays($i)->format('d M'),
                'tokens' => $dayData ? (int) $dayData->total_tokens : 0,
                'requests' => $dayData ? (int) $dayData->request_count : 0,
                'cost' => $dayData ? (float) $dayData->total_cost : 0,
            ];
        }
        $maxTokens = max(array_column($usageTrend, 'tokens')) ?: 1;
        $maxRequests = max(array_column($usageTrend, 'requests')) ?: 1;

        // Cost estimation totals
        $totalCost = TokenUsage::where('user_id', $user->id)->sum('cost_idr');
        $costThisMonth = TokenUsage::where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('cost_idr');
        $costToday = TokenUsage::where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->sum('cost_idr');

        // All subscriptions history
        $subscriptionHistory = UserSubscription::where('user_id', $user->id)
            ->with('plan')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.subscriptions.show', compact(
            'user', 'subscription', 'plan', 'quota',
            'dailyTokens', 'modelUsage', 'rateLimitStatus',
            'recentActivity', 'usageTrend', 'maxTokens', 'maxRequests',
            'totalCost', 'costThisMonth', 'costToday',
            'subscriptionHistory'
        ));
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_slug' => 'required|exists:subscription_plans,slug',
            'duration_days' => 'nullable|integer|min:1',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $plan = SubscriptionPlan::getBySlug($validated['plan_slug']);

        // Cancel existing active subscription
        UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        // Determine expiry
        $expiresAt = null;
        if ($plan->slug !== 'free') {
            if ($plan->type === 'daily') {
                $days = $validated['duration_days'] ?? 1;
            } else {
                $days = $validated['duration_days'] ?? 30;
            }
            $expiresAt = now()->addDays($days);
        }

        // Create new subscription
        UserSubscription::create([
            'user_id' => $user->id,
            'plan_slug' => $plan->slug,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => $expiresAt,
            'token_usage_total' => 0,
            'daily_requests_used' => 0,
            'daily_requests_reset_at' => now()->endOfDay(),
        ]);

        return back()->with('success', "Plan {$plan->name} berhasil di-assign ke {$user->email}.");
    }

    public function cancel(UserSubscription $subscription)
    {
        $subscription->update(['status' => 'cancelled']);

        // Auto-assign free plan
        $user = $subscription->user;
        $hasActive = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if (!$hasActive) {
            UserSubscription::create([
                'user_id' => $user->id,
                'plan_slug' => 'free',
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => null,
                'token_usage_total' => 0,
                'daily_requests_used' => 0,
                'daily_requests_reset_at' => now()->endOfDay(),
            ]);
        }

        return back()->with('success', 'Subscription berhasil di-cancel. User dialihkan ke Free plan.');
    }
}
