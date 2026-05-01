<?php

namespace Database\Seeders;

use App\Models\PlanModelAccess;
use Illuminate\Database\Seeder;

class PlanModelAccessSeeder extends Seeder
{
    public function run(): void
    {
        // Free plan models
        $freeModels = [
            'glm-5',
            'claude-sonnet-4-5-20250514',
            'claude-haiku-4-5-20250514',
            'MiniMax-M1',
        ];

        // Pro plan = free + additional models
        $proAdditionalModels = [
            'claude-opus-4-20250514',
            'gpt-4.1',
            'gemini-2.5-pro',
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'kimi-k2',
        ];

        $proModels = array_merge($freeModels, $proAdditionalModels);

        // Premium = all pro models
        $premiumModels = $proModels;

        // Daily = all pro models
        $dailyModels = $proModels;

        $planModels = [
            'free' => $freeModels,
            'pro' => $proModels,
            'premium' => $premiumModels,
            'daily' => $dailyModels,
        ];

        // Clear existing data
        PlanModelAccess::truncate();

        foreach ($planModels as $planSlug => $models) {
            foreach ($models as $modelId) {
                PlanModelAccess::create([
                    'plan_slug' => $planSlug,
                    'model_id' => $modelId,
                ]);
            }
        }
    }
}
