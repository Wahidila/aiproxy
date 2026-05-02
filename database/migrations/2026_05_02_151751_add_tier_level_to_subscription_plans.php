<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('subscription_plans', 'tier_level')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->unsignedTinyInteger('tier_level')->default(0)->after('sort_order')
                    ->comment('Hierarki plan: 0=free, 1=pro, 2=premium, dst. Digunakan untuk logika upgrade/downgrade.');
            });
        }

        // Set default tier levels based on existing plans
        DB::table('subscription_plans')->where('slug', 'free')->update(['tier_level' => 0]);
        DB::table('subscription_plans')->where('slug', 'pro')->update(['tier_level' => 1]);
        DB::table('subscription_plans')->where('slug', 'daily')->update(['tier_level' => 1]);
        DB::table('subscription_plans')->where('slug', 'premium')->update(['tier_level' => 2]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('subscription_plans', 'tier_level')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->dropColumn('tier_level');
            });
        }
    }
};
