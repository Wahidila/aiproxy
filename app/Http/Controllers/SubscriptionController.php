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
        // Return 404 if subscription feature is disabled
        if (Setting::get('subscription_enabled', '0') != '1') {
            abort(404);
        }

        $user = $request->user();
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        $activeSubscription = $user->activeSubscription();
        $activePlan = $user->getActivePlan();
        $quota = $user->getOrCreateQuota();

        return view('subscriptions.index', compact('plans', 'activeSubscription', 'activePlan', 'quota'));
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
}
