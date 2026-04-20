<?php

namespace App\Http\Controllers;

use App\Services\TokenTrackingService;
use Illuminate\Http\Request;

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

        return view('dashboard', compact('quota', 'stats', 'recentUsages', 'recentTransactions'));
    }
}
