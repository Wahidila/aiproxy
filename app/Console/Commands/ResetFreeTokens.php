<?php

namespace App\Console\Commands;

use App\Models\TokenQuota;
use Illuminate\Console\Command;

class ResetFreeTokens extends Command
{
    protected $signature = 'tokens:reset-free';
    protected $description = 'Reset free tier token usage for users whose reset date has passed';

    public function handle(): int
    {
        $quotas = TokenQuota::where('free_tokens_reset_at', '<=', now())
            ->where('free_tokens_used', '>', 0)
            ->get();

        $count = 0;
        foreach ($quotas as $quota) {
            $quota->update([
                'free_tokens_used' => 0,
                'free_tokens_reset_at' => now()->addMonth(),
            ]);
            $count++;
        }

        $this->info("Reset free tokens for {$count} users.");

        return Command::SUCCESS;
    }
}
