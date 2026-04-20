<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\ModelPricing;
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

        return view('api-keys.index', compact('apiKeys', 'quota', 'baseUrl', 'freeModels', 'paidModels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tier' => 'required|in:free,paid',
        ]);

        $quota = $request->user()->getOrCreateQuota();

        // Validate user has balance for the chosen tier
        if ($request->tier === 'free' && $quota->free_balance <= 0) {
            return redirect()->route('api-keys.index')
                ->with('error', 'Saldo free trial habis. Tidak bisa membuat API key free tier.');
        }
        if ($request->tier === 'paid' && $quota->paid_balance <= 0) {
            return redirect()->route('api-keys.index')
                ->with('error', 'Saldo top up kosong. Silakan top up terlebih dahulu.');
        }

        $key = ApiKey::generateKey();

        $apiKey = $request->user()->apiKeys()->create([
            'key' => $key,
            'name' => $request->name,
            'tier' => $request->tier,
        ]);

        return redirect()->route('api-keys.index')
            ->with('success', 'API key (' . ($request->tier === 'free' ? 'Free Tier' : 'Paid') . ') created successfully.')
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
