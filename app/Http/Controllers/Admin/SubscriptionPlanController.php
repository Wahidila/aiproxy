<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        
        // Stats per plan
        $plans->each(function ($plan) {
            $plan->active_subs_count = UserSubscription::where('plan_slug', $plan->slug)
                ->where('status', 'active')
                ->count();
            $plan->total_revenue = UserSubscription::where('plan_slug', $plan->slug)
                ->where('status', 'active')
                ->count() * $plan->price_idr;
        });

        return view('admin.subscriptions.plans', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:50|unique:subscription_plans,slug',
            'type' => 'required|in:monthly,daily',
            'price_idr' => 'required|integer|min:0',
            'daily_request_limit' => 'nullable|integer|min:1',
            'per_minute_limit' => 'required|integer|min:1',
            'concurrent_limit' => 'required|integer|min:1',
            'max_token_usage' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string|max:200',
            'is_popular' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_popular'] = $request->boolean('is_popular');
        $validated['sort_order'] = $validated['sort_order'] ?? SubscriptionPlan::max('sort_order') + 1;

        SubscriptionPlan::create($validated);

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Plan berhasil ditambahkan.');
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:monthly,daily',
            'price_idr' => 'required|integer|min:0',
            'daily_request_limit' => 'nullable|integer|min:1',
            'per_minute_limit' => 'required|integer|min:1',
            'concurrent_limit' => 'required|integer|min:1',
            'max_token_usage' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string|max:200',
            'is_popular' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_popular'] = $request->boolean('is_popular');
        $validated['daily_request_limit'] = $validated['daily_request_limit'] ?? null;
        $validated['max_token_usage'] = $validated['max_token_usage'] ?? null;

        $subscriptionPlan->update($validated);

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Plan berhasil diupdate.');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        // Don't allow deleting free plan
        if ($subscriptionPlan->slug === 'free') {
            return back()->with('error', 'Plan Free tidak bisa dihapus.');
        }

        // Check active subscriptions
        $activeSubs = UserSubscription::where('plan_slug', $subscriptionPlan->slug)
            ->where('status', 'active')
            ->count();

        if ($activeSubs > 0) {
            return back()->with('error', "Tidak bisa hapus plan ini. Masih ada {$activeSubs} subscriber aktif.");
        }

        $subscriptionPlan->delete();

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Plan berhasil dihapus.');
    }
}
