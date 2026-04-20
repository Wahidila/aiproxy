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
