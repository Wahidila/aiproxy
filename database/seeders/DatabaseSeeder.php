<?php

namespace Database\Seeders;

use App\Models\ModelPricing;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Default settings
        $defaults = [
            'site_name' => 'AI Token Dashboard',
            'site_description' => 'Akses AI Premium, Harga Terjangkau',
            'usd_to_idr_rate' => '16500',
            'free_credit_amount' => '100000',
            'min_topup_amount' => '10000',
            'laravel_fallback_enabled' => '0',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        // Model Pricing - Free Tier models
        $freeModels = [
            ['model_id' => 'claude-sonnet-4.5', 'model_name' => 'Assistant Sonnet 4.5', 'input_price_usd' => 3.00, 'output_price_usd' => 15.00, 'is_free_tier' => true],
            ['model_id' => 'deepseek-3.2', 'model_name' => 'DeepSeek 3.2', 'input_price_usd' => 0.14, 'output_price_usd' => 0.28, 'is_free_tier' => true],
            ['model_id' => 'minimax-m2.5', 'model_name' => 'MiniMax M2.5', 'input_price_usd' => 0.15, 'output_price_usd' => 1.20, 'is_free_tier' => true],
            ['model_id' => 'glm-5', 'model_name' => 'GLM-5 (Zhipu AI)', 'input_price_usd' => 1.00, 'output_price_usd' => 3.20, 'is_free_tier' => true],
        ];

        // Premium models (paid only)
        $premiumModels = [
            ['model_id' => 'claude-opus-4.6', 'model_name' => 'Assistant Opus 4.6', 'input_price_usd' => 15.00, 'output_price_usd' => 75.00, 'is_free_tier' => false],
            ['model_id' => 'claude-sonnet-4', 'model_name' => 'Assistant Sonnet 4', 'input_price_usd' => 3.00, 'output_price_usd' => 15.00, 'is_free_tier' => false],
            ['model_id' => 'claude-haiku-4.5', 'model_name' => 'Assistant Haiku 4.5', 'input_price_usd' => 0.80, 'output_price_usd' => 4.00, 'is_free_tier' => false],
            ['model_id' => 'gpt-5.4', 'model_name' => 'GPT-5.4', 'input_price_usd' => 10.00, 'output_price_usd' => 30.00, 'is_free_tier' => false],
            ['model_id' => 'gpt-5.2', 'model_name' => 'GPT-5.2', 'input_price_usd' => 5.00, 'output_price_usd' => 15.00, 'is_free_tier' => false],
            ['model_id' => 'gpt-5.3-codex', 'model_name' => 'GPT-5.3 Codex', 'input_price_usd' => 7.50, 'output_price_usd' => 30.00, 'is_free_tier' => false],
            ['model_id' => 'gpt-5.1', 'model_name' => 'GPT-5.1', 'input_price_usd' => 2.50, 'output_price_usd' => 10.00, 'is_free_tier' => false],
            ['model_id' => 'gemini-2.5-pro', 'model_name' => 'Gemini 2.5 Pro', 'input_price_usd' => 2.50, 'output_price_usd' => 10.00, 'is_free_tier' => false],
            ['model_id' => 'gemini-2.5-flash', 'model_name' => 'Gemini 2.5 Flash', 'input_price_usd' => 0.15, 'output_price_usd' => 0.60, 'is_free_tier' => false],
            ['model_id' => 'gemini-3.1-pro', 'model_name' => 'Gemini 3.1 Pro', 'input_price_usd' => 2.50, 'output_price_usd' => 10.00, 'is_free_tier' => false],
            ['model_id' => 'kimi-k2.5', 'model_name' => 'Kimi K2.5', 'input_price_usd' => 0.60, 'output_price_usd' => 2.40, 'is_free_tier' => false],
            ['model_id' => 'minimax-m2.1', 'model_name' => 'MiniMax M2.1', 'input_price_usd' => 0.12, 'output_price_usd' => 0.99, 'is_free_tier' => false],
            ['model_id' => 'qwen3-coder-next', 'model_name' => 'Qwen3 Coder Next', 'input_price_usd' => 0.50, 'output_price_usd' => 2.00, 'is_free_tier' => false],
            ['model_id' => 'auto', 'model_name' => 'Auto (Best Available)', 'input_price_usd' => 3.00, 'output_price_usd' => 15.00, 'is_free_tier' => false],
        ];

        foreach (array_merge($freeModels, $premiumModels) as $model) {
            ModelPricing::firstOrCreate(
                ['model_id' => $model['model_id']],
                [
                    'model_name' => $model['model_name'],
                    'input_price_usd' => $model['input_price_usd'],
                    'output_price_usd' => $model['output_price_usd'],
                    'discount_percent' => 0,
                    'is_free_tier' => $model['is_free_tier'],
                    'is_active' => true,
                ]
            );
        }
    }
}
