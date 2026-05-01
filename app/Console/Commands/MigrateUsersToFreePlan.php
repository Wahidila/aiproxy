<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MigrateUsersToFreePlan extends Command
{
    protected $signature = 'users:migrate-free-plan';
    protected $description = 'Assign FREE subscription plan to all existing users who do not have an active subscription';

    public function handle(): int
    {
        $users = User::whereDoesntHave('subscriptions', function ($q) {
            $q->where('status', 'active');
        })->get();

        $count = 0;
        foreach ($users as $user) {
            $user->subscribeTo('free');
            $count++;
        }

        $this->info("Assigned FREE plan to {$count} users.");

        return Command::SUCCESS;
    }
}
