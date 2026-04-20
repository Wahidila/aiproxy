<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'qris_image' => Setting::get('qris_image'),
            'usd_to_idr_rate' => Setting::get('usd_to_idr_rate', 16500),
            'free_credit_amount' => Setting::get('free_credit_amount', config('enowxai.free_credit_amount')),
            'min_topup_amount' => Setting::get('min_topup_amount', config('enowxai.min_topup_amount')),
            'site_name' => Setting::get('site_name', config('app.name')),
            'site_description' => Setting::get('site_description', 'Akses AI Premium, Harga Terjangkau'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'qris_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'usd_to_idr_rate' => 'required|numeric|min:1',
            'free_credit_amount' => 'required|integer|min:0',
            'min_topup_amount' => 'required|integer|min:1000',
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
        ]);

        if ($request->hasFile('qris_image')) {
            $path = $request->file('qris_image')->store('qris', 'public');
            Setting::set('qris_image', $path);
        }

        Setting::set('usd_to_idr_rate', $request->usd_to_idr_rate);
        Setting::set('free_credit_amount', $request->free_credit_amount);
        Setting::set('min_topup_amount', $request->min_topup_amount);
        Setting::set('site_name', $request->site_name);
        Setting::set('site_description', $request->site_description);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
