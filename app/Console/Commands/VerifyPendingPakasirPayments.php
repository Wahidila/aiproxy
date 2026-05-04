<?php

namespace App\Console\Commands;

use App\Models\Donation;
use App\Models\WalletTransaction;
use App\Services\PakasirService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifyPendingPakasirPayments extends Command
{
    protected $signature = 'pakasir:verify-pending';
    protected $description = 'Check pending Pakasir donations and auto-approve if payment confirmed by Pakasir API';

    public function handle(): int
    {
        $pendingDonations = Donation::where('status', 'pending')
            ->where('payment_gateway', 'pakasir')
            ->whereNotNull('gateway_order_id')
            ->where('created_at', '>=', now()->subHours(24)) // Only check last 24h
            ->get();

        if ($pendingDonations->isEmpty()) {
            $this->info('No pending Pakasir donations to verify.');
            return 0;
        }

        $pakasir = new PakasirService();
        $approved = 0;
        $stillPending = 0;

        foreach ($pendingDonations as $donation) {
            $result = $pakasir->verifyTransaction($donation->amount, $donation->gateway_order_id);

            if (empty($result) || ($result['status'] ?? null) !== 'completed') {
                $stillPending++;
                continue;
            }

            // Auto-approve
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
                        'admin_notes' => 'Auto-approved via scheduled verification',
                    ]);

                    // Credit balance
                    $quota = $donation->user->getOrCreateQuota();
                    $quota->addBalance(
                        $donation->amount,
                        WalletTransaction::TYPE_TOPUP,
                        'Top up via Pakasir Rp ' . number_format($donation->amount, 0, ',', '.'),
                        $donation
                    );

                    Log::info('Pakasir scheduled verify: auto-approved', [
                        'donation_id' => $donation->id,
                        'order_id' => $donation->gateway_order_id,
                        'amount' => $donation->amount,
                        'user_id' => $donation->user_id,
                    ]);
                });

                $approved++;
            } catch (\Exception $e) {
                Log::error('Pakasir scheduled verify: error', [
                    'donation_id' => $donation->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Error processing donation #{$donation->id}: {$e->getMessage()}");
            }
        }

        $this->info("Done. Approved: {$approved}, Still pending: {$stillPending}");

        return 0;
    }
}
