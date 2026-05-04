<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModelPricing;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModelLimitController extends Controller
{
    public function index()
    {
        $models = ModelPricing::orderBy('model_name')->get();

        // Load existing limit settings for each model
        $limits = [];
        foreach ($models as $model) {
            $key = 'model_daily_limit:' . $model->model_id;
            $raw = Setting::get($key);
            if ($raw) {
                $parsed = json_decode($raw, true);
                $limits[$model->model_id] = $parsed;
            }
        }

        // Get today's usage counts per model (global)
        $todayUsage = DB::table('token_usages')
            ->select('model', DB::raw('COUNT(*) as count'))
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->groupBy('model')
            ->pluck('count', 'model')
            ->toArray();

        return view('admin.model-limits.index', compact('models', 'limits', 'todayUsage'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'model_id' => 'required|string|max:100',
            'limit' => 'nullable|integer|min:1',
            'enabled' => 'boolean',
        ]);

        $modelId = $request->input('model_id');
        $limit = $request->input('limit');
        $enabled = $request->boolean('enabled');

        $key = 'model_daily_limit:' . $modelId;

        if ($limit === null && !$enabled) {
            // Remove the setting entirely if no limit and disabled
            Setting::where('key', $key)->delete();
        } else {
            $value = json_encode([
                'enabled' => $enabled,
                'limit' => (int) ($limit ?? 0),
            ]);
            Setting::set($key, $value);
        }

        return redirect()->route('admin.model-limits.index')
            ->with('success', "Limit untuk model {$modelId} berhasil diupdate.");
    }
}
