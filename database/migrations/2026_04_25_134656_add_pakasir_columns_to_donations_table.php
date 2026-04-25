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
        Schema::table('donations', function (Blueprint $table) {
            $table->string('payment_gateway')->nullable()->default(null)->after('payment_proof');
            $table->string('gateway_order_id')->nullable()->unique()->after('payment_gateway');
            $table->string('gateway_payment_method')->nullable()->after('gateway_order_id');
            $table->timestamp('gateway_completed_at')->nullable()->after('gateway_payment_method');
            $table->timestamp('paid_at')->nullable()->after('gateway_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway',
                'gateway_order_id',
                'gateway_payment_method',
                'gateway_completed_at',
                'paid_at',
            ]);
        });
    }
};
