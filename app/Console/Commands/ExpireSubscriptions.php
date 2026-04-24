<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Expire active subscriptions that have passed their expiration date';

    public function handle(): int
    {
        $expired = Subscription::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No subscriptions to expire.');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($expired as $subscription) {
            $subscription->update(['status' => 'expired']);

            // Deactivate all API keys for this subscription
            $subscription->apiKeys()->update(['is_active' => false]);

            $count++;

            Log::info("Subscription #{$subscription->id} expired for user #{$subscription->user_id}", [
                'plan' => $subscription->plan->name ?? $subscription->plan_id,
                'expired_at' => $subscription->expires_at->toDateTimeString(),
            ]);
        }

        $this->info("Expired {$count} subscription(s) and deactivated their API keys.");

        return self::SUCCESS;
    }
}
