<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

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
