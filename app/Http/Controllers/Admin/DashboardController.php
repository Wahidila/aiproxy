<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\TokenUsage;
use App\Models\User;

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

        // Daily stats for last 7 days
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyStats[$date] = [
                'tokens' => TokenUsage::whereDate('created_at', $date)->sum('total_tokens'),
                'requests' => TokenUsage::whereDate('created_at', $date)->count(),
            ];
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalTokensUsed',
            'pendingDonations',
            'totalRevenue',
            'activeUsersToday',
            'recentDonations',
            'recentUsers',
            'dailyStats'
        ));
    }
}
