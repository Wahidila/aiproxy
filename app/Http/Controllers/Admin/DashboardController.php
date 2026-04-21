<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Setting;
use App\Models\TokenUsage;
use App\Models\TrialRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::where('role', 'user')->count();
        $totalTokensUsed = TokenUsage::sum('total_tokens');
        $pendingDonations = Donation::where('status', 'pending')->count();
        $totalRevenue = Donation::where('status', 'approved')->sum('amount');
        $activeUsersToday = TokenUsage::where('created_at', '>=', now()->startOfDay())
            ->distinct('user_id')
            ->count('user_id');

        $recentDonations = Donation::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $recentUsers = User::where('role', 'user')
            ->latest()
            ->take(5)
            ->get();

        // Trial requests
        $pendingTrialRequests = TrialRequest::pending()->count();
        $recentTrialRequests = TrialRequest::pending()
            ->latest()
            ->take(5)
            ->get();

        // Daily stats for last 7 days
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyStats[$date] = [
                'tokens' => TokenUsage::whereDate('created_at', $date)->sum('total_tokens'),
                'requests' => TokenUsage::whereDate('created_at', $date)->count(),
            ];
        }

        // Proxy status
        try {
            $golangStatus = $this->checkGolangProxy();
        } catch (\Throwable $e) {
            $golangStatus = ['online' => false, 'error' => 'Check failed'];
        }
        $laravelFallback = Setting::get('laravel_fallback_enabled', '0') === '1';

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalTokensUsed',
            'pendingDonations',
            'totalRevenue',
            'activeUsersToday',
            'recentDonations',
            'recentUsers',
            'pendingTrialRequests',
            'recentTrialRequests',
            'dailyStats',
            'golangStatus',
            'laravelFallback'
        ));
    }

    public function toggleLaravelFallback(Request $request)
    {
        $current = Setting::get('laravel_fallback_enabled', '0');
        $new = $current === '1' ? '0' : '1';
        Setting::set('laravel_fallback_enabled', $new);

        // Clear cached value
        Cache::forget('laravel_fallback_enabled');

        $status = $new === '1' ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.dashboard')
            ->with('success', "Laravel Fallback API berhasil {$status}.");
    }

    public function golangProxyStatus()
    {
        $status = $this->checkGolangProxy();
        return response()->json($status);
    }

    private function checkGolangProxy(): array
    {
        try {
            $response = Http::connectTimeout(2)->timeout(3)->get('http://127.0.0.1:8080/v1/health');
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'online' => true,
                    'proxy' => $data['proxy'] ?? 'golang',
                    'timestamp' => $data['timestamp'] ?? null,
                ];
            }
            return ['online' => false, 'error' => 'Unhealthy response: ' . $response->status()];
        } catch (\Throwable $e) {
            return ['online' => false, 'error' => 'Connection refused'];
        }
    }
}
