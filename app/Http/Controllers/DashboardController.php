<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\ModelPricing;
use App\Models\Setting;
use App\Models\TokenUsage;
use App\Services\TokenTrackingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private TokenTrackingService $trackingService)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $quota = $user->getOrCreateQuota();
        $stats = $this->trackingService->getUserStats($user, 30);

        $recentUsages = $user->tokenUsages()
            ->with('apiKey')
            ->latest('created_at')
            ->take(10)
            ->get();

        $recentTransactions = $user->walletTransactions()
            ->latest('created_at')
            ->take(10)
            ->get();

        $showBalanceAlert = $quota->isBelowThreshold();

        // Build model comparison data
        $userModelStats = TokenUsage::where('user_id', $user->id)
            ->select(
                'model',
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('AVG(response_time_ms) as avg_response_time'),
                DB::raw('SUM(cost_idr) as total_cost_idr'),
                DB::raw('SUM(total_tokens) as total_tokens')
            )
            ->groupBy('model')
            ->having('total_requests', '>=', 1)
            ->orderByDesc('total_requests')
            ->get()
            ->keyBy('model');

        $modelComparison = collect();

        if ($userModelStats->isNotEmpty()) {
            $activePricings = ModelPricing::where('is_active', true)
                ->whereIn('model_id', $userModelStats->keys())
                ->get()
                ->keyBy('model_id');

            $exchangeRate = ModelPricing::getExchangeRate();

            foreach ($userModelStats as $modelId => $usageStats) {
                $pricing = $activePricings->get($modelId);

                // Calculate discounted IDR prices per 1M tokens
                $discountMultiplier = $pricing ? (1 - $pricing->discount_percent / 100) : 1;
                $inputPriceIdr = $pricing
                    ? round($pricing->input_price_usd * $exchangeRate * $discountMultiplier, 2)
                    : 0;
                $outputPriceIdr = $pricing
                    ? round($pricing->output_price_usd * $exchangeRate * $discountMultiplier, 2)
                    : 0;

                $modelComparison->push([
                    'model_name' => $pricing->model_name ?? $modelId,
                    'model_id' => $modelId,
                    'input_price_idr' => $inputPriceIdr,
                    'output_price_idr' => $outputPriceIdr,
                    'is_free_tier' => $pricing->is_free_tier ?? false,
                    'user_total_requests' => (int) $usageStats->total_requests,
                    'user_avg_response_time' => round((float) $usageStats->avg_response_time),
                    'user_total_cost_idr' => round((float) $usageStats->total_cost_idr, 2),
                ]);
            }

            // Sort by total requests descending (already ordered by query, but ensure after merge)
            $modelComparison = $modelComparison->sortByDesc('user_total_requests')->values();
        }

        // --- Spending Forecast ---
        $now = Carbon::now();
        $sevenDaysAgo = $now->copy()->subDays(7)->startOfDay();

        // Get daily costs for the last 7 days
        $dailyCostsRaw = TokenUsage::where('user_id', $user->id)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(cost_idr) as daily_cost')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->pluck('daily_cost', 'date');

        // Build a full 7-day array (fill missing days with 0)
        $dailyCosts7days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $dailyCosts7days[$date] = round((float) ($dailyCostsRaw[$date] ?? 0), 2);
        }

        $totalCost7days = array_sum($dailyCosts7days);
        $avgDailyCost = round($totalCost7days / 7, 2);

        // Trend: compare last 3 days avg vs previous 4 days avg
        $dailyValues = array_values($dailyCosts7days);
        $prev4Avg = array_sum(array_slice($dailyValues, 0, 4)) / 4;
        $last3Avg = array_sum(array_slice($dailyValues, 4, 3)) / 3;

        if ($avgDailyCost == 0) {
            $trend = 'stable';
        } elseif ($last3Avg > $prev4Avg * 1.1) {
            $trend = 'up';
        } elseif ($last3Avg < $prev4Avg * 0.9) {
            $trend = 'down';
        } else {
            $trend = 'stable';
        }

        // Calculate days remaining
        $freeDaysRemaining = null;
        $paidDaysRemaining = null;
        $freeEstimatedEmptyDate = null;
        $paidEstimatedEmptyDate = null;

        if ($avgDailyCost > 0) {
            $freeDaysRemaining = (int) floor($quota->free_balance / $avgDailyCost);
            $paidDaysRemaining = (int) floor($quota->paid_balance / $avgDailyCost);
            $freeEstimatedEmptyDate = $now->copy()->addDays($freeDaysRemaining);
            $paidEstimatedEmptyDate = $now->copy()->addDays($paidDaysRemaining);
        }

        $spendingForecast = [
            'avg_daily_cost' => $avgDailyCost,
            'free_days_remaining' => $freeDaysRemaining,
            'paid_days_remaining' => $paidDaysRemaining,
            'free_estimated_empty_date' => $freeEstimatedEmptyDate,
            'paid_estimated_empty_date' => $paidEstimatedEmptyDate,
            'daily_costs_7days' => $dailyCosts7days,
            'trend' => $trend,
        ];

        // Subscription plan info
        $activeSubscription = $user->activeSubscription();
        $activePlan = $user->getActivePlan();

        // Playground data
        $apiKeys = $user->apiKeys()->where('is_active', true)->latest()->get();
        $freeModels = ModelPricing::where('is_active', true)->where('is_free_tier', true)->orderBy('model_name')->get();
        $paidModels = ModelPricing::where('is_active', true)->where('is_free_tier', false)->orderBy('model_name')->get();

        return view('dashboard', compact('quota', 'stats', 'recentUsages', 'recentTransactions', 'showBalanceAlert', 'modelComparison', 'spendingForecast', 'activeSubscription', 'activePlan', 'apiKeys', 'freeModels', 'paidModels'));
    }

    public function saveAlertSettings(Request $request)
    {
        $request->validate([
            'balance_alert_enabled' => 'required|boolean',
            'balance_alert_threshold' => 'required|integer|min:0|max:100000000',
        ]);

        $quota = $request->user()->getOrCreateQuota();
        $quota->update([
            'balance_alert_enabled' => $request->boolean('balance_alert_enabled'),
            'balance_alert_threshold' => $request->integer('balance_alert_threshold'),
        ]);

        return back()->with('alert_settings_saved', true);
    }
}
