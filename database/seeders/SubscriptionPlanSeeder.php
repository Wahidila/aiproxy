<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'free',
                'name' => 'FREE',
                'type' => 'monthly',
                'price_idr' => 0,
                'daily_request_limit' => 50,
                'per_minute_limit' => 6,
                'concurrent_limit' => 1,
                'max_token_usage' => null,
                'is_popular' => false,
                'sort_order' => 1,
                'features' => [
                    'Akses model dasar',
                    '50 request/hari',
                    '6 request/menit',
                    '1 concurrent request',
                    'Cocok untuk coba-coba',
                ],
            ],
            [
                'slug' => 'pro',
                'name' => 'PRO',
                'type' => 'monthly',
                'price_idr' => 29000,
                'daily_request_limit' => 3000,
                'per_minute_limit' => 30,
                'concurrent_limit' => 2,
                'max_token_usage' => null,
                'is_popular' => false,
                'sort_order' => 2,
                'features' => [
                    'Semua model Free + Claude Opus 4.6',
                    'GPT-5.4',
                    'Gemini 2.5 Pro, Gemini 3 Flash, Gemini 3.1 Pro',
                    'Kimi K2.5',
                    '3.000 request per hari',
                    '30 request per menit',
                    '2 request bersamaan',
                    'Email support',
                    'Riwayat usage',
                ],
            ],
            [
                'slug' => 'premium',
                'name' => 'PREMIUM',
                'type' => 'monthly',
                'price_idr' => 59000,
                'daily_request_limit' => 10000,
                'per_minute_limit' => 90,
                'concurrent_limit' => 4,
                'max_token_usage' => null,
                'is_popular' => true,
                'sort_order' => 3,
                'features' => [
                    'SEMUA model Pro',
                    '10.000 request per hari',
                    '90 request per menit',
                    '4 request bersamaan',
                    'Priority support',
                    'Riwayat usage',
                ],
            ],
            [
                'slug' => 'daily',
                'name' => 'Harian',
                'type' => 'daily',
                'price_idr' => 29000,
                'daily_request_limit' => null, // unlimited requests
                'per_minute_limit' => 60,
                'concurrent_limit' => 3,
                'max_token_usage' => 100000000, // 100M tokens
                'is_popular' => false,
                'sort_order' => 4,
                'features' => [
                    'Semua model Pro + Free',
                    'Unlimited Request',
                    'Max Penggunaan 100M Token',
                    'Email support',
                    'Priority support',
                    'Riwayat usage',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
