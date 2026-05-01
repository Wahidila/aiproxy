<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;

class PricingController extends Controller
{
    public function index()
    {
        $monthlyPlans = SubscriptionPlan::where('type', 'monthly')->orderBy('sort_order')->get();
        $dailyPlans = SubscriptionPlan::where('type', 'daily')->orderBy('sort_order')->get();
        
        return view('pricing.index', compact('monthlyPlans', 'dailyPlans'));
    }
}
