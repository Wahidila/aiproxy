<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Setting;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $quota = $user->getOrCreateQuota();
        $qrisImage = Setting::get('qris_image');
        $minTopup = config('enowxai.min_topup_amount', 10000);

        $pendingDonation = $user->donations()->where('status', 'pending')->latest()->first();

        return view('donations.index', compact('quota', 'qrisImage', 'minTopup', 'pendingDonation'));
    }

    public function store(Request $request)
    {
        $minTopup = config('enowxai.min_topup_amount', 10000);

        $request->validate([
            'amount' => "required|integer|min:{$minTopup}",
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Check if user already has a pending donation
        $pending = $request->user()->donations()->where('status', 'pending')->exists();
        if ($pending) {
            return redirect()->route('donations.index')
                ->with('error', 'Anda sudah memiliki top up yang menunggu persetujuan.');
        }

        $path = $request->file('payment_proof')->store('payment-proofs', 'local');

        $request->user()->donations()->create([
            'amount' => $request->amount,
            'status' => 'pending',
            'payment_proof' => $path,
        ]);

        return redirect()->route('donations.index')
            ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu persetujuan admin.');
    }

    public function history(Request $request)
    {
        $donations = $request->user()->donations()->latest()->paginate(15);

        return view('donations.history', compact('donations'));
    }
}
