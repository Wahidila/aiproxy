<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        return view('admin.subscriptions.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.subscriptions.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscription_plans',
            'price_idr' => 'required|integer|min:0',
            'rpm_limit' => 'required|integer|min:1',
            'parallel_limit' => 'required|integer|min:1',
            'budget_usd_per_cycle' => 'required|numeric|min:0',
            'cycle_hours' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'allowed_models' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if (!empty($validated['allowed_models'])) {
            $validated['allowed_models'] = array_map('trim', explode(',', $validated['allowed_models']));
        } else {
            $validated['allowed_models'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active');

        SubscriptionPlan::create($validated);

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Plan created successfully.');
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        return view('admin.subscriptions.plans.edit', ['plan' => $subscriptionPlan]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscription_plans,slug,' . $subscriptionPlan->id,
            'price_idr' => 'required|integer|min:0',
            'rpm_limit' => 'required|integer|min:1',
            'parallel_limit' => 'required|integer|min:1',
            'budget_usd_per_cycle' => 'required|numeric|min:0',
            'cycle_hours' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'allowed_models' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if (!empty($validated['allowed_models'])) {
            $validated['allowed_models'] = array_map('trim', explode(',', $validated['allowed_models']));
        } else {
            $validated['allowed_models'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active');

        $subscriptionPlan->update($validated);

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Plan updated successfully.');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        if ($subscriptionPlan->activeSubscriptions()->count() > 0) {
            return back()->with('error', 'Cannot delete plan with active subscriptions.');
        }

        $subscriptionPlan->delete();
        return redirect()->route('admin.subscription-plans.index')->with('success', 'Plan deleted successfully.');
    }
}
