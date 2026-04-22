<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionApiKey;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionUsage;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Show user's subscription dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $subscription = Subscription::with(['plan', 'apiKeys'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'pending'])
            ->latest()
            ->first();

        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        $recentUsages = collect();
        $cycleCost = 0;
        $cycleStart = null;

        if ($subscription && $subscription->isActive()) {
            $cycleStart = $subscription->getCurrentCycleStart();
            $cycleCost = $subscription->getCurrentCycleCostUsd();

            $recentUsages = SubscriptionUsage::where('subscription_id', $subscription->id)
                ->orderByDesc('created_at')
                ->limit(30)
                ->get();
        }

        return view('subscriptions.index', compact('subscription', 'plans', 'recentUsages', 'cycleCost', 'cycleStart'));
    }

    /**
     * Submit a subscription request.
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        // Check if user already has an active or pending subscription
        $existing = Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($existing) {
            return back()->with('error', 'You already have an active or pending subscription.');
        }

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $request->plan_id,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Subscription request submitted. Please wait for admin approval.');
    }

    /**
     * Generate a new API key for the subscription.
     */
    public function createApiKey(Request $request)
    {
        $request->validate(['name' => 'nullable|string|max:255']);

        $user = auth()->user();
        $subscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->firstOrFail();

        // Limit to 5 keys per subscription
        if ($subscription->apiKeys()->count() >= 5) {
            return back()->with('error', 'Maximum 5 API keys per subscription.');
        }

        $key = SubscriptionApiKey::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'key' => SubscriptionApiKey::generateKey(),
            'name' => $request->name ?? 'API Key ' . ($subscription->apiKeys()->count() + 1),
            'is_active' => true,
        ]);

        return back()->with('success', 'API key created: ' . $key->key);
    }

    /**
     * Toggle API key active status.
     */
    public function toggleApiKey(SubscriptionApiKey $apiKey)
    {
        if ($apiKey->user_id !== auth()->id()) {
            abort(403);
        }

        $apiKey->update(['is_active' => !$apiKey->is_active]);

        $status = $apiKey->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "API key {$status}.");
    }

    /**
     * Delete an API key.
     */
    public function deleteApiKey(SubscriptionApiKey $apiKey)
    {
        if ($apiKey->user_id !== auth()->id()) {
            abort(403);
        }

        $apiKey->delete();
        return back()->with('success', 'API key deleted.');
    }
}
