<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModelPricing;
use App\Models\Setting;
use Illuminate\Http\Request;

class ModelPricingController extends Controller
{
    public function index()
    {
        $models = ModelPricing::orderBy('model_name')->get();
        $exchangeRate = (float) Setting::get('usd_to_idr_rate', 16500);

        return view('admin.model-pricing.index', compact('models', 'exchangeRate'));
    }

    /**
     * Check upstream model availability.
     * Makes 1 GET request to Go proxy /v1/models and compares with DB.
     */
    public function checkStatus()
    {
        try {
            // Use any active API key to call our own proxy
            $apiKey = \App\Models\ApiKey::where('is_active', 1)->first();
            if (!$apiKey) {
                return response()->json(['error' => 'No active API key found'], 500);
            }

            $response = \Illuminate\Support\Facades\Http::timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey->key,
                ])
                ->get('http://127.0.0.1:8080/v1/models');

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Upstream returned HTTP ' . $response->status(),
                ], 502);
            }

            $data = $response->json('data', []);
            $upstreamModels = collect($data)->pluck('id')->toArray();

            // Get all models from our DB
            $dbModels = ModelPricing::orderBy('model_name')->get(['model_id', 'model_name', 'is_active']);

            $results = [];
            foreach ($dbModels as $model) {
                $results[] = [
                    'model_id' => $model->model_id,
                    'model_name' => $model->model_name,
                    'is_active' => (bool) $model->is_active,
                    'upstream_available' => in_array($model->model_id, $upstreamModels),
                ];
            }

            // Also find models available upstream but not in our DB
            $dbModelIds = $dbModels->pluck('model_id')->toArray();
            $newModels = array_diff($upstreamModels, $dbModelIds);

            return response()->json([
                'models' => $results,
                'upstream_total' => count($upstreamModels),
                'new_models' => array_values($newModels),
                'checked_at' => now()->format('H:i:s'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'model_id' => 'required|string|max:100|unique:model_pricings,model_id',
            'model_name' => 'required|string|max:255',
            'input_price_usd' => 'required|numeric|min:0',
            'output_price_usd' => 'required|numeric|min:0',
            'discount_percent' => 'required|integer|min:0|max:100',
            'is_free_tier' => 'boolean',
        ]);

        ModelPricing::create([
            'model_id' => $request->model_id,
            'model_name' => $request->model_name,
            'input_price_usd' => $request->input_price_usd,
            'output_price_usd' => $request->output_price_usd,
            'discount_percent' => $request->discount_percent,
            'is_free_tier' => $request->boolean('is_free_tier'),
            'is_active' => true,
        ]);

        return redirect()->route('admin.model-pricing.index')
            ->with('success', "Model {$request->model_name} berhasil ditambahkan.");
    }

    public function update(Request $request, ModelPricing $modelPricing)
    {
        $request->validate([
            'input_price_usd' => 'required|numeric|min:0',
            'output_price_usd' => 'required|numeric|min:0',
            'discount_percent' => 'required|integer|min:0|max:100',
            'is_free_tier' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $modelPricing->update([
            'input_price_usd' => $request->input_price_usd,
            'output_price_usd' => $request->output_price_usd,
            'discount_percent' => $request->discount_percent,
            'is_free_tier' => $request->boolean('is_free_tier'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.model-pricing.index')
            ->with('success', "Model {$modelPricing->model_name} berhasil diupdate.");
    }

    public function destroy(ModelPricing $modelPricing)
    {
        $name = $modelPricing->model_name;
        $modelPricing->delete();

        return redirect()->route('admin.model-pricing.index')
            ->with('success', "Model {$name} berhasil dihapus.");
    }
}
