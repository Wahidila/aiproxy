<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        $query = Donation::with('user', 'approver');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: show pending first
            $query->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected', 'expired')");
        }

        $donations = $query->latest()->paginate(20)->withQueryString();

        $pendingCount = Donation::where('status', 'pending')->count();

        return view('admin.donations.index', compact('donations', 'pendingCount'));
    }

    public function approve(Request $request, Donation $donation)
    {
        if (!$donation->isPending()) {
            return redirect()->route('admin.donations.index')
                ->with('error', 'Donation is not in pending status.');
        }

        $donation->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        // Add IDR balance to user's wallet
        $quota = $donation->user->getOrCreateQuota();
        $quota->addBalance(
            $donation->amount,
            \App\Models\WalletTransaction::TYPE_TOPUP,
            'Top up saldo Rp ' . number_format($donation->amount, 0, ',', '.'),
            $donation
        );

        $formatted = 'Rp ' . number_format($donation->amount, 0, ',', '.');
        return redirect()->route('admin.donations.index')
            ->with('success', "Top up {$formatted} dari {$donation->user->name} berhasil disetujui.");
    }

    public function reject(Request $request, Donation $donation)
    {
        if (!$donation->isPending()) {
            return redirect()->route('admin.donations.index')
                ->with('error', 'Donation is not in pending status.');
        }

        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        $donation->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.donations.index')
            ->with('success', 'Donasi ditolak.');
    }

    public function showProof(Donation $donation)
    {
        if (!$donation->payment_proof) {
            abort(404);
        }

        // Pakasir payments store JSON webhook data as proof
        if ($donation->isPakasir()) {
            $proofData = json_decode($donation->payment_proof, true);

            return view('admin.donations.proof-pakasir', [
                'donation' => $donation,
                'proofData' => $proofData,
            ]);
        }

        // Manual payments store file paths
        $path = storage_path('app/' . $donation->payment_proof);
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}
