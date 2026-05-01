<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\ModelPricing;
use App\Models\Setting;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    public function index(Request $request)
    {
        $apiKeys = $request->user()->apiKeys()->latest()->get();
        $quota = $request->user()->getOrCreateQuota();
        $baseUrl = url('/api/v1');

        $freeModels = ModelPricing::where('is_active', true)->where('is_free_tier', true)->orderBy('model_name')->get();
        $paidModels = ModelPricing::where('is_active', true)->where('is_free_tier', false)->orderBy('model_name')->get();

        $subscriptionEnabled = Setting::get('subscription_enabled', '0') == '1';
        $activeSubscription = $subscriptionEnabled ? $request->user()->activeSubscription() : null;
        $activePlan = $subscriptionEnabled ? $request->user()->getActivePlan() : null;

        return view('api-keys.index', compact(
            'apiKeys', 'quota', 'baseUrl', 'freeModels', 'paidModels',
            'subscriptionEnabled', 'activeSubscription', 'activePlan'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tier' => 'required|in:free,paid,subscription',
        ]);

        $user = $request->user();
        $quota = $user->getOrCreateQuota();

        // Validate based on tier
        if ($request->tier === 'free' && $quota->free_balance <= 0) {
            return redirect()->route('api-keys.index')
                ->with('error', 'Saldo free trial habis. Tidak bisa membuat API key free tier.');
        }
        if ($request->tier === 'paid' && $quota->paid_balance <= 0) {
            return redirect()->route('api-keys.index')
                ->with('error', 'Saldo top up kosong. Silakan top up terlebih dahulu.');
        }
        if ($request->tier === 'subscription') {
            // Must have subscription feature enabled and an active subscription
            if (Setting::get('subscription_enabled', '0') != '1') {
                return redirect()->route('api-keys.index')
                    ->with('error', 'Fitur subscription belum diaktifkan.');
            }
            $activeSubscription = $user->activeSubscription();
            if (!$activeSubscription || !$activeSubscription->isActive()) {
                return redirect()->route('api-keys.index')
                    ->with('error', 'Anda belum memiliki subscription aktif. Silakan beli plan terlebih dahulu.');
            }
        }

        $key = ApiKey::generateKey();

        $apiKey = $user->apiKeys()->create([
            'key' => $key,
            'name' => $request->name,
            'tier' => $request->tier,
        ]);

        $tierLabel = match ($request->tier) {
            'free' => 'Free Tier',
            'paid' => 'Paid',
            'subscription' => 'Subscription',
        };

        return redirect()->route('api-keys.index')
            ->with('success', "API key ({$tierLabel}) created successfully.")
            ->with('new_key', $key);
    }

    public function destroy(Request $request, ApiKey $apiKey)
    {
        // Ensure user owns this key
        if ($apiKey->user_id !== $request->user()->id) {
            abort(403);
        }

        $apiKey->delete();

        return redirect()->route('api-keys.index')
            ->with('success', 'API key deleted successfully.');
    }

    public function toggleActive(Request $request, ApiKey $apiKey)
    {
        if ($apiKey->user_id !== $request->user()->id) {
            abort(403);
        }

        $apiKey->update(['is_active' => !$apiKey->is_active]);

        return redirect()->route('api-keys.index')
            ->with('success', 'API key ' . ($apiKey->is_active ? 'activated' : 'deactivated') . '.');
    }
}
