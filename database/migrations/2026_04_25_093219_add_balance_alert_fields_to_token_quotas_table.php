<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('token_quotas', function (Blueprint $table) {
            $table->integer('balance_alert_threshold')->default(10000)->after('paid_expires_at');
            $table->boolean('balance_alert_enabled')->default(true)->after('balance_alert_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('token_quotas', function (Blueprint $table) {
            $table->dropColumn(['balance_alert_threshold', 'balance_alert_enabled']);
        });
    }
};
