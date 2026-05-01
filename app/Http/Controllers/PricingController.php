<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SubscriptionPlan;

class PricingController extends Controller
{
    public function index()
    {
        // Return 404 if subscription feature is disabled
        if (Setting::get('subscription_enabled', '0') != '1') {
            abort(404);
        }

        $monthlyPlans = SubscriptionPlan::where('type', 'monthly')
            ->orderBy('sort_order')
            ->get();

        $dailyPlans = SubscriptionPlan::where('type', 'daily')
            ->orderBy('sort_order')
            ->get();

        return view('pricing.index', compact('monthlyPlans', 'dailyPlans'));
    }
}
