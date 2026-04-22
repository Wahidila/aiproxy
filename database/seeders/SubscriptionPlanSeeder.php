<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('subscription_plans')->upsert([
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'price_idr' => 19900,
                'rpm_limit' => 30,
                'parallel_limit' => 3,
                'budget_usd_per_cycle' => 5.00,
                'cycle_hours' => 6,
                'allowed_models' => null,
                'description' => 'Perfect for personal projects and learning. Get access to AI models with generous limits.',
                'features' => json_encode([
                    '30 requests per minute',
                    '3 parallel connections',
                    '$5 budget per 6 hours',
                    'Access to all available models',
                    'Email support',
                ]),
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price_idr' => 49900,
                'rpm_limit' => 30,
                'parallel_limit' => 3,
                'budget_usd_per_cycle' => 15.00,
                'cycle_hours' => 6,
                'allowed_models' => null,
                'description' => 'For professionals and teams who need higher limits and priority access.',
                'features' => json_encode([
                    '30 requests per minute',
                    '3 parallel connections',
                    '$15 budget per 6 hours',
                    'Access to all available models',
                    'Priority support',
                    'Usage analytics dashboard',
                ]),
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['slug'], ['name', 'price_idr', 'rpm_limit', 'parallel_limit', 'budget_usd_per_cycle', 'cycle_hours', 'description', 'features', 'updated_at']);
    }
}
