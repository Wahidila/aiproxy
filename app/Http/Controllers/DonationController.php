<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Services\PakasirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $quota = $user->getOrCreateQuota();
        $qrisImage = Setting::get('qris_image');
        $minTopup = config('AI service.min_topup_amount', 10000);

        $gatewayPakasirEnabled = Setting::get('gateway_pakasir_enabled', '1') == '1';
        $gatewayManualEnabled = Setting::get('gateway_manual_enabled', '1') == '1';

        $pendingManual = $user->donations()
            ->where('status', 'pending')
            ->whereNull('payment_gateway')
            ->latest()
            ->first();

        $pendingPakasir = $user->donations()
            ->where('status', 'pending')
            ->where('payment_gateway', 'pakasir')
            ->latest()
            ->first();

        return view('donations.index', compact('quota', 'qrisImage', 'minTopup', 'pendingManual', 'pendingPakasir', 'gatewayPakasirEnabled', 'gatewayManualEnabled'));
    }

    /**
     * Store manual donation (upload bukti transfer).
     */
    public function store(Request $request)
    {
        // Check if manual gateway is enabled
        if (Setting::get('gateway_manual_enabled', '1') != '1') {
            return redirect()->route('donations.index')
                ->with('error', 'Metode pembayaran manual sedang tidak tersedia.');
        }

        $minTopup = config('AI service.min_topup_amount', 10000);

        $request->validate([
            'amount' => "required|integer|min:{$minTopup}",
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Check if user already has a pending manual donation
        $pending = $request->user()->donations()
            ->where('status', 'pending')
            ->whereNull('payment_gateway')
            ->exists();

        if ($pending) {
            return redirect()->route('donations.index')
                ->with('error', 'Anda sudah memiliki top up manual yang menunggu persetujuan.');
        }

        $path = $request->file('payment_proof')->store('payment-proofs', 'local');

        $request->user()->donations()->create([
            'amount' => $request->amount,
            'status' => 'pending',
            'payment_proof' => $path,
            'payment_gateway' => null,
        ]);

        return redirect()->route('donations.index')
            ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu persetujuan admin.');
    }

    /**
     * Create Pakasir payment and redirect to payment page.
     */
    public function pakasirPayment(Request $request)
    {
        // Check if Pakasir gateway is enabled
        if (Setting::get('gateway_pakasir_enabled', '1') != '1') {
            return redirect()->route('donations.index')
                ->with('error', 'Metode pembayaran Pakasir sedang tidak tersedia.');
        }

        $minTopup = config('AI service.min_topup_amount', 10000);

        $request->validate([
            'amount' => "required|integer|min:{$minTopup}",
        ]);

        // Check if user already has a pending Pakasir donation
        $pending = $request->user()->donations()
            ->where('status', 'pending')
            ->where('payment_gateway', 'pakasir')
            ->exists();

        if ($pending) {
            return redirect()->route('donations.index')
                ->with('error', 'Anda sudah memiliki pembayaran Pakasir yang belum selesai.');
        }

        $pakasir = new PakasirService();

        // Create donation record first
        $donation = $request->user()->donations()->create([
            'amount' => $request->amount,
            'status' => 'pending',
            'payment_gateway' => 'pakasir',
        ]);

        // Generate order ID and update
        $orderId = $pakasir->generateOrderId($donation->id);
        $donation->update(['gateway_order_id' => $orderId]);

        // Build payment URL
        $callbackUrl = route('donations.pakasir.callback');
        $paymentUrl = $pakasir->createPaymentUrl($request->amount, $orderId, $callbackUrl);

        return redirect()->away($paymentUrl);
    }

    /**
     * Resume a pending Pakasir payment (reconstruct payment URL).
     */
    public function pakasirResume(Request $request)
    {
        $user = $request->user();

        $donation = $user->donations()
            ->where('status', 'pending')
            ->where('payment_gateway', 'pakasir')
            ->latest()
            ->first();

        if (!$donation) {
            return redirect()->route('donations.index')
                ->with('error', 'Tidak ada pembayaran yang tertunda.');
        }

        // Reconstruct payment URL
        $pakasir = new PakasirService();
        $callbackUrl = route('donations.pakasir.callback');
        $paymentUrl = $pakasir->createPaymentUrl($donation->amount, $donation->gateway_order_id, $callbackUrl);

        return redirect()->away($paymentUrl);
    }

    /**
     * Cancel a pending Pakasir payment.
     */
    public function pakasirCancel(Request $request)
    {
        $user = $request->user();

        $donation = $user->donations()
            ->where('status', 'pending')
            ->where('payment_gateway', 'pakasir')
            ->latest()
            ->first();

        if (!$donation) {
            return redirect()->route('donations.index')
                ->with('error', 'Tidak ada pembayaran yang tertunda.');
        }

        $donation->update(['status' => 'cancelled']);

        return redirect()->route('donations.index')
            ->with('success', 'Pembayaran dibatalkan. Anda bisa membuat pembayaran baru.');
    }

    /**
     * Handle redirect back from Pakasir after payment.
     */
    public function pakasirCallback(Request $request)
    {
        $user = $request->user();

        // Find the latest Pakasir donation for this user (pending or recently approved)
        $donation = $user->donations()
            ->where('payment_gateway', 'pakasir')
            ->whereIn('status', ['pending', 'approved'])
            ->latest()
            ->first();

        if (!$donation) {
            return redirect()->route('donations.index')
                ->with('info', 'Tidak ada pembayaran yang ditemukan.');
        }

        // If already approved by webhook, show success
        if ($donation->isApproved()) {
            return redirect()->route('donations.index')
                ->with('success', 'Pembayaran berhasil! Saldo telah ditambahkan.');
        }

        // Verify transaction status via API
        $pakasir = new PakasirService();
        $result = $pakasir->verifyTransaction($donation->amount, $donation->gateway_order_id);

        if (!empty($result) && isset($result['status']) && $result['status'] === 'completed') {
            // Already completed - webhook may have processed it
            if ($donation->isApproved()) {
                return redirect()->route('donations.index')
                    ->with('success', 'Pembayaran berhasil! Saldo telah ditambahkan.');
            }

            // Auto-approve: Pakasir verified, no admin approval needed
            try {
                DB::transaction(function () use ($donation, $result) {
                    $donation = Donation::where('id', $donation->id)->lockForUpdate()->first();

                    if ($donation->isApproved()) {
                        return;
                    }

                    $now = now();

                    $donation->update([
                        'status' => 'approved',
                        'payment_proof' => json_encode($result),
                        'gateway_payment_method' => $result['payment_method'] ?? null,
                        'gateway_completed_at' => $result['completed_at'] ?? $now,
                        'paid_at' => $now,
                        'approved_at' => $now,
                        'admin_notes' => 'Auto-approved via Pakasir',
                    ]);

                    // Credit balance
                    $quota = $donation->user->getOrCreateQuota();
                    $quota->addBalance(
                        $donation->amount,
                        WalletTransaction::TYPE_TOPUP,
                        'Top up via Pakasir Rp ' . number_format($donation->amount, 0, ',', '.'),
                        $donation
                    );

                    Log::info('Pakasir callback: auto-approved and balance credited', [
                        'donation_id' => $donation->id,
                        'order_id' => $donation->gateway_order_id,
                        'amount' => $donation->amount,
                        'user_id' => $donation->user_id,
                    ]);
                });

                return redirect()->route('donations.index')
                    ->with('success', 'Pembayaran berhasil! Saldo telah ditambahkan.');
            } catch (\Exception $e) {
                Log::error('Pakasir callback: auto-approve error', [
                    'donation_id' => $donation->id,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->route('donations.index')
                    ->with('error', 'Pembayaran terverifikasi tapi gagal memproses saldo. Hubungi admin.');
            }
        }

        return redirect()->route('donations.index')
            ->with('info', 'Pembayaran sedang diproses. Saldo akan otomatis ditambahkan setelah pembayaran dikonfirmasi.');
    }

    /**
     * Handle Pakasir webhook (POST, no auth required).
     */
    public function pakasirWebhook(Request $request)
    {
        $payload = $request->all();

        Log::info('Pakasir webhook received', ['payload' => $payload]);

        // Validate required fields
        $orderId = $payload['order_id'] ?? null;
        $amount = $payload['amount'] ?? null;
        $project = $payload['project'] ?? null;
        $status = $payload['status'] ?? null;

        if (!$orderId || !$amount || !$project || !$status) {
            Log::warning('Pakasir webhook: missing required fields', ['payload' => $payload]);
            return response()->json(['message' => 'Missing required fields'], 400);
        }

        // Validate project matches our slug
        $expectedSlug = config('services.pakasir.slug', 'aimurah');
        if ($project !== $expectedSlug) {
            Log::warning('Pakasir webhook: project mismatch', [
                'expected' => $expectedSlug,
                'received' => $project,
            ]);
            return response()->json(['message' => 'Invalid project'], 400);
        }

        // Find the donation
        $donation = Donation::where('gateway_order_id', $orderId)
            ->where('payment_gateway', 'pakasir')
            ->first();

        if (!$donation) {
            Log::warning('Pakasir webhook: donation not found', ['order_id' => $orderId]);
            return response()->json(['message' => 'Donation not found'], 404);
        }

        // Prevent double-crediting
        if ($donation->isApproved()) {
            Log::info('Pakasir webhook: donation already approved', ['order_id' => $orderId]);
            return response()->json(['message' => 'Already processed'], 200);
        }

        // Only process completed status
        if ($status !== 'completed') {
            Log::info('Pakasir webhook: non-completed status', [
                'order_id' => $orderId,
                'status' => $status,
            ]);
            return response()->json(['message' => 'Status noted'], 200);
        }

        // Verify via Pakasir API
        $pakasir = new PakasirService();
        $verification = $pakasir->verifyTransaction((int) $amount, $orderId);

        if (empty($verification) || ($verification['status'] ?? null) !== 'completed') {
            Log::warning('Pakasir webhook: verification failed', [
                'order_id' => $orderId,
                'verification' => $verification,
            ]);
            return response()->json(['message' => 'Verification failed'], 400);
        }

        // Process payment in a DB transaction with lock
        try {
            DB::transaction(function () use ($donation, $payload) {
                // Lock the donation row to prevent race conditions
                $donation = Donation::where('id', $donation->id)->lockForUpdate()->first();

                // Double-check not already approved (inside lock)
                if ($donation->isApproved()) {
                    return;
                }

                $now = now();

                $donation->update([
                    'status' => 'approved',
                    'payment_proof' => json_encode($payload),
                    'gateway_payment_method' => $payload['payment_method'] ?? null,
                    'gateway_completed_at' => $payload['completed_at'] ?? $now,
                    'paid_at' => $now,
                    'approved_at' => $now,
                    'admin_notes' => 'Auto-approved via Pakasir webhook',
                ]);

                // Credit balance
                $quota = $donation->user->getOrCreateQuota();
                $quota->addBalance(
                    $donation->amount,
                    WalletTransaction::TYPE_TOPUP,
                    'Top up via Pakasir Rp ' . number_format($donation->amount, 0, ',', '.'),
                    $donation
                );

                Log::info('Pakasir webhook: donation approved and balance credited', [
                    'donation_id' => $donation->id,
                    'order_id' => $donation->gateway_order_id,
                    'amount' => $donation->amount,
                    'user_id' => $donation->user_id,
                ]);
            });

            return response()->json(['message' => 'OK'], 200);
        } catch (\Exception $e) {
            Log::error('Pakasir webhook: processing error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Processing error'], 500);
        }
    }

    public function history(Request $request)
    {
        $user = $request->user();
        $donations = $user->donations()->latest()->paginate(15);

        $totalApproved = $user->donations()->where('status', 'approved')->sum('amount');
        $totalCount = $user->donations()->count();
        $pendingCount = $user->donations()->where('status', 'pending')->count();

        return view('donations.history', compact('donations', 'totalApproved', 'totalCount', 'pendingCount'));
    }
}
