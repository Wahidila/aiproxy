<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        $activeSubscription = $user->activeSubscription();
        $activePlan = $user->getActivePlan();
        $quota = $user->getOrCreateQuota();

        // Subscription history (last 10)
        $subscriptionHistory = $user->subscriptions()
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Usage stats
        $usageStats = [
            'today_requests' => $activeSubscription ? (int) $activeSubscription->daily_requests_used : 0,
            'daily_limit' => $activePlan ? $activePlan->daily_request_limit : 0,
            'token_total' => $activeSubscription ? (int) $activeSubscription->token_usage_total : 0,
            'token_cap' => $activePlan ? $activePlan->max_token_usage : 0,
        ];

        return view('subscriptions.index', compact(
            'plans', 'activeSubscription', 'activePlan', 'quota',
            'subscriptionHistory', 'usageStats'
        ));
    }

    public function purchase(Request $request)
    {
        // Block if subscription feature is disabled
        if (Setting::get('subscription_enabled', '0') != '1') {
            abort(404);
        }

        $request->validate([
            'plan_slug' => 'required|string|exists:subscription_plans,slug',
        ]);

        $user = $request->user();
        $plan = SubscriptionPlan::getBySlug($request->plan_slug);

        if (!$plan) {
            return back()->with('error', 'Plan tidak ditemukan.');
        }

        // Free plan doesn't need payment
        if ($plan->slug === 'free') {
            $user->subscribeTo('free');
            return redirect()->route('subscriptions.index')
                ->with('success', 'Berhasil beralih ke plan FREE.');
        }

        // Check wallet balance
        $quota = $user->getOrCreateQuota();
        $totalBalance = (float) $quota->paid_balance;

        if ($totalBalance < $plan->price_idr) {
            return back()->with('error', "Saldo tidak mencukupi. Dibutuhkan {$plan->formatted_price}, saldo Anda: Rp " . number_format($totalBalance, 0, ',', '.') . ". Silakan top up terlebih dahulu.");
        }

        // Deduct from paid balance
        $description = "Pembelian plan {$plan->name} ({$plan->formatted_price})";
        $quota->deductBalance($plan->price_idr, $description, null, 'paid');

        // Set expiry
        if ($plan->type === 'daily') {
            $expiresAt = now()->addDay();
        } else {
            $expiresAt = now()->addDays(30);
        }

        // Create subscription
        $user->subscribeTo($plan->slug, $expiresAt);

        return redirect()->route('subscriptions.index')
            ->with('success', "Berhasil berlangganan plan {$plan->name}! Berlaku hingga {$expiresAt->format('d M Y H:i')}.");
    }

    /**
     * Cancel the user's active subscription and revert to free plan.
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        $activeSubscription = $user->activeSubscription();

        if (!$activeSubscription) {
            return back()->with('error', 'Tidak ada langganan aktif untuk dibatalkan.');
        }

        // Don't allow cancelling if already on free plan
        if ($activeSubscription->plan_slug === 'free') {
            return back()->with('error', 'Anda sudah menggunakan plan FREE.');
        }

        $planName = $activeSubscription->plan ? $activeSubscription->plan->name : $activeSubscription->plan_slug;

        // Switch to free plan (subscribeTo cancels existing subscription automatically)
        $user->subscribeTo('free');

        return back()->with('success', "Langganan {$planName} berhasil dibatalkan. Anda sekarang menggunakan plan FREE.");
    }

    /**
     * Renew/extend the current subscription.
     */
    public function renew(Request $request)
    {
        // Block if subscription feature is disabled
        if (Setting::get('subscription_enabled', '0') != '1') {
            abort(404);
        }

        $user = $request->user();
        $activeSubscription = $user->activeSubscription();

        if (!$activeSubscription) {
            return back()->with('error', 'Tidak ada langganan aktif untuk diperpanjang.');
        }

        $plan = SubscriptionPlan::getBySlug($activeSubscription->plan_slug);

        if (!$plan) {
            return back()->with('error', 'Plan tidak ditemukan.');
        }

        // Can't renew free plan
        if ($plan->slug === 'free') {
            return back()->with('error', 'Plan FREE tidak perlu diperpanjang.');
        }

        // Check wallet balance
        $quota = $user->getOrCreateQuota();
        $totalBalance = (float) $quota->paid_balance;

        if ($totalBalance < $plan->price_idr) {
            return back()->with('error', "Saldo tidak mencukupi. Dibutuhkan {$plan->formatted_price}, saldo Anda: Rp " . number_format($totalBalance, 0, ',', '.') . ". Silakan top up terlebih dahulu.");
        }

        // Deduct from paid balance
        $description = "Perpanjangan plan {$plan->name} ({$plan->formatted_price})";
        $quota->deductBalance($plan->price_idr, $description, null, 'paid');

        // Calculate new expiry: extend from current expiry if still active, or from now if expired
        $baseDate = ($activeSubscription->expires_at && $activeSubscription->expires_at->isFuture())
            ? $activeSubscription->expires_at
            : now();

        if ($plan->type === 'daily') {
            $expiresAt = $baseDate->copy()->addDay();
        } else {
            $expiresAt = $baseDate->copy()->addDays(30);
        }

        // Create new subscription with extended expiry
        $user->subscribeTo($plan->slug, $expiresAt);

        return back()->with('success', "Berhasil memperpanjang plan {$plan->name}! Berlaku hingga {$expiresAt->format('d M Y H:i')}.");
    }
}
