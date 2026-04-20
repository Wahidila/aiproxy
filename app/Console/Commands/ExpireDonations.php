<?php

namespace App\Console\Commands;

use App\Models\TokenQuota;
use Illuminate\Console\Command;

class ExpireDonations extends Command
{
    protected $signature = 'tokens:expire-donations';
    protected $description = 'Expire paid access for users whose donation period has ended';

    public function handle(): int
    {
        $quotas = TokenQuota::where('paid_expires_at', '<=', now())
            ->where('paid_tokens_limit', '>', 0)
            ->get();

        $count = 0;
        foreach ($quotas as $quota) {
            $quota->update([
                'paid_tokens_used' => 0,
                'paid_tokens_limit' => 0,
                'paid_expires_at' => null,
            ]);
            $count++;
        }

        $this->info("Expired paid access for {$count} users.");

        return Command::SUCCESS;
    }
}
