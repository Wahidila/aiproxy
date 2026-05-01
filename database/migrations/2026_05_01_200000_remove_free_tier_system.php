<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration removes the free tier system.
     * Note: free_balance column is kept for historical data purposes.
     * We add an index on user_subscriptions for faster subscription lookups.
     */
    public function up(): void
    {
        // Remove free tier system - add index for faster subscription-based lookups
        // Only add if not already exists
        if (!Schema::hasIndex('user_subscriptions', 'user_subscriptions_user_id_status_index')) {
            Schema::table('user_subscriptions', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'user_subscriptions_user_id_status_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropIndex('user_subscriptions_user_id_status_index');
        });
    }
};
