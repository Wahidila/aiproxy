<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use App\Models\TokenQuota;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\WalletTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateFreeToSubscription extends Command
{
    protected $signature = 'app:migrate-free-to-subscription {--dry-run : Run without committing changes}';

    protected $description = 'Migrate free tier users to subscription system';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🏃 DRY RUN MODE — no changes will be committed.');
        }

        $balancesZeroed = 0;
        $keysConverted = 0;
        $subscriptionsAssigned = 0;

        DB::beginTransaction();

        try {
            // Step A & B: Find users with free_balance > 0 and zero them out
            $quotas = TokenQuota::where('free_balance', '>', 0)->with('user')->get();

            foreach ($quotas as $quota) {
                $email = $quota->user->email ?? 'unknown';
                $freeBalance = (float) $quota->free_balance;

                $this->info("Zeroing free_balance for {$email}: Rp " . number_format($freeBalance, 0, ',', '.'));

                WalletTransaction::create([
                    'user_id' => $quota->user_id,
                    'type' => WalletTransaction::TYPE_ADJUSTMENT,
                    'amount' => -$freeBalance,
                    'balance_after' => (float) $quota->paid_balance,
                    'description' => 'Free tier removed - migrated to subscription',
                    'created_at' => now(),
                ]);

                $quota->update(['free_balance' => 0]);

                $balancesZeroed++;
            }

            // Step C & D: Convert all free API keys to subscription tier
            $freeKeys = ApiKey::where('tier', 'free')->with('user')->get();

            foreach ($freeKeys as $key) {
                $email = $key->user->email ?? 'unknown';
                $this->info("Converting API key [{$key->name}] for {$email} from 'free' to 'subscription'");

                $key->update(['tier' => 'subscription']);

                $keysConverted++;
            }

            // Step E & F: Assign free subscription to users without an active one
            $usersWithActiveSubscription = UserSubscription::where('status', 'active')
                ->pluck('user_id');

            $usersWithoutSubscription = User::whereNotIn('id', $usersWithActiveSubscription)->get();

            foreach ($usersWithoutSubscription as $user) {
                $this->info("Assigning free subscription to {$user->email}");

                UserSubscription::create([
                    'user_id' => $user->id,
                    'plan_slug' => 'free',
                    'status' => 'active',
                    'starts_at' => now(),
                    'expires_at' => null,
                ]);

                $subscriptionsAssigned++;
            }

            // Step G: Summary
            $this->info('');
            $this->info('=== Migration Summary ===');
            $this->info("✅ {$balancesZeroed} free balances zeroed");
            $this->info("✅ {$keysConverted} API keys converted from free → subscription");
            $this->info("✅ {$subscriptionsAssigned} free subscriptions assigned");

            // Step H: Rollback if dry-run
            if ($dryRun) {
                DB::rollBack();
                $this->warn('🔄 DRY RUN — all changes have been rolled back.');
            } else {
                DB::commit();
                $this->info('💾 All changes committed successfully.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("❌ Migration failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
