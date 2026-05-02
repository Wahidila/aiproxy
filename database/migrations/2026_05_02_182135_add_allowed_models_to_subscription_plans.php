<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->json('allowed_models')->nullable()->after('max_token_usage');
        });

        // Seed default allowed_models based on current logic:
        // FREE: only is_free_tier models
        // PRO: free_tier + some premium models
        // PREMIUM & DAILY: all active models (null = all)
        $freeModels = DB::table('model_pricings')
            ->where('is_active', 1)
            ->where('is_free_tier', 1)
            ->pluck('model_id')
            ->toArray();

        $allModels = DB::table('model_pricings')
            ->where('is_active', 1)
            ->pluck('model_id')
            ->toArray();

        // FREE plan: only free tier models
        DB::table('subscription_plans')
            ->where('slug', 'free')
            ->update(['allowed_models' => json_encode($freeModels)]);

        // PRO plan: all active models
        DB::table('subscription_plans')
            ->where('slug', 'pro')
            ->update(['allowed_models' => json_encode($allModels)]);

        // PREMIUM & DAILY: null means ALL models (no restriction)
        DB::table('subscription_plans')
            ->whereIn('slug', ['premium', 'daily'])
            ->update(['allowed_models' => null]);
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('allowed_models');
        });
    }
};
