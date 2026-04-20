<?php

namespace App\Console\Commands;

use App\Models\TokenUsage;
use Illuminate\Console\Command;

class CleanupOldUsage extends Command
{
    protected $signature = 'tokens:cleanup {--days=90 : Number of days to keep}';
    protected $description = 'Delete token usage records older than specified days';

    public function handle(): int
    {
        $days = $this->option('days');
        $cutoff = now()->subDays($days);

        $count = TokenUsage::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$count} usage records older than {$days} days.");

        return Command::SUCCESS;
    }
}
