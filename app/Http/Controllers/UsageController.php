<?php

namespace App\Http\Controllers;

use App\Services\TokenTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UsageController extends Controller
{
    public function __construct(private TokenTrackingService $trackingService)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = $user->tokenUsages()->with('apiKey')->latest('created_at');

        // Filters
        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }
        if ($request->filled('api_key_id')) {
            $query->where('api_key_id', $request->api_key_id);
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $usages = $query->paginate(25)->withQueryString();

        $apiKeys = $user->apiKeys()->get();
        $models = $user->tokenUsages()->distinct()->pluck('model');

        // Determine date range from ?range= parameter (default: 14d)
        $validRanges = ['7d' => 7, '14d' => 14, '30d' => 30, '90d' => 90];
        $rangeParam = $request->input('range', '14d');
        $rangeDays = $validRanges[$rangeParam] ?? 14;
        $activeRange = array_key_exists($rangeParam, $validRanges) ? $rangeParam : '14d';

        // Chart data with caching (TTL 5 minutes)
        $cacheKey = "usage_chart_{$user->id}_{$activeRange}";
        $chartData = Cache::remember($cacheKey, 300, function () use ($user, $rangeDays) {
            $allUsages = $user->tokenUsages()
                ->where('created_at', '>=', now()->subDays($rangeDays))
                ->get();

            // Summary stats
            $summary = [
                'total_requests' => $allUsages->count(),
                'total_tokens' => $allUsages->sum('total_tokens'),
                'total_cost' => $allUsages->sum('cost_idr'),
                'avg_response' => $allUsages->count() > 0 ? round($allUsages->avg('response_time_ms')) : 0,
            ];

            // Daily chart
            $dailyChart = [];
            for ($i = $rangeDays - 1; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $dayUsages = $allUsages->filter(fn($u) => $u->created_at->format('Y-m-d') === $date);
                $dailyChart[$date] = [
                    'requests' => $dayUsages->count(),
                    'tokens' => $dayUsages->sum('total_tokens'),
                    'cost' => round($dayUsages->sum('cost_idr'), 2),
                ];
            }

            // By model breakdown
            $byModel = $allUsages->groupBy('model')->map(function ($group, $model) {
                return [
                    'requests' => $group->count(),
                    'tokens' => $group->sum('total_tokens'),
                    'cost' => round($group->sum('cost_idr'), 2),
                ];
            })->sortByDesc('cost');

            // By API key breakdown
            $byApiKey = $allUsages->groupBy('api_key_id')->map(function ($group) {
                $key = $group->first()->apiKey;
                return [
                    'name' => $key->name ?? 'Unknown',
                    'requests' => $group->count(),
                    'tokens' => $group->sum('total_tokens'),
                    'cost' => round($group->sum('cost_idr'), 2),
                ];
            })->sortByDesc('requests');

            return compact('summary', 'dailyChart', 'byModel', 'byApiKey');
        });

        $summary = $chartData['summary'];
        $dailyChart = $chartData['dailyChart'];
        $byModel = $chartData['byModel'];
        $byApiKey = $chartData['byApiKey'];

        return view('usage.index', compact(
            'usages', 'apiKeys', 'models',
            'summary', 'dailyChart', 'byModel', 'byApiKey',
            'activeRange', 'rangeDays'
        ));
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $query = $user->tokenUsages()->with('apiKey')->latest('created_at');

        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $usages = $query->get();

        $csv = "Date,Model,Input Tokens,Output Tokens,Total Tokens,Status,Response Time (ms)\n";
        foreach ($usages as $usage) {
            $csv .= implode(',', [
                $usage->created_at->format('Y-m-d H:i:s'),
                $usage->model,
                $usage->input_tokens,
                $usage->output_tokens,
                $usage->total_tokens,
                $usage->status_code,
                $usage->response_time_ms,
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="token-usage-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
