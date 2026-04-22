<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionUsage;
use Illuminate\Http\Request;

class SubscriptionManageController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with(['user', 'plan', 'approvedBy'])
            ->orderByDesc('created_at');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'pending' => Subscription::where('status', 'pending')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'stats'));
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['user', 'plan', 'apiKeys', 'approvedBy']);

        $recentUsages = SubscriptionUsage::where('subscription_id', $subscription->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $cycleStart = $subscription->getCurrentCycleStart();
        $cycleCost = $subscription->getCurrentCycleCostUsd();

        return view('admin.subscriptions.show', compact('subscription', 'recentUsages', 'cycleStart', 'cycleCost'));
    }

    public function approve(Subscription $subscription)
    {
        if ($subscription->status !== 'pending') {
            return back()->with('error', 'Only pending subscriptions can be approved.');
        }

        $subscription->update([
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Auto-generate API key for the user
        $subscription->apiKeys()->create([
            'user_id' => $subscription->user_id,
            'key' => \App\Models\SubscriptionApiKey::generateKey(),
            'name' => 'Default',
            'is_active' => true,
        ]);

        return back()->with('success', 'Subscription approved. API key generated automatically.');
    }

    public function reject(Subscription $subscription)
    {
        if ($subscription->status !== 'pending') {
            return back()->with('error', 'Only pending subscriptions can be rejected.');
        }

        $subscription->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Subscription rejected.');
    }

    public function extend(Request $request, Subscription $subscription)
    {
        $request->validate(['days' => 'required|integer|min:1|max:365']);

        $currentExpiry = $subscription->expires_at ?? now();
        $subscription->update([
            'expires_at' => $currentExpiry->addDays($request->days),
        ]);

        return back()->with('success', "Subscription extended by {$request->days} days.");
    }

    public function cancel(Subscription $subscription)
    {
        $subscription->update(['status' => 'cancelled']);
        $subscription->apiKeys()->update(['is_active' => false]);

        return back()->with('success', 'Subscription cancelled. All API keys deactivated.');
    }
}
