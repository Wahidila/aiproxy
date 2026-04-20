<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('token_quotas', function (Blueprint $table) {
            $table->decimal('free_balance', 12, 2)->default(0)->after('balance');
            $table->decimal('paid_balance', 12, 2)->default(0)->after('free_balance');
        });

        // Migrate existing data: if user has free_credit_claimed and no topup, move balance to free_balance
        // Otherwise move to paid_balance
        DB::statement("
            UPDATE token_quotas tq
            SET tq.free_balance = CASE
                WHEN tq.free_credit_claimed = 1
                     AND (SELECT COUNT(*) FROM wallet_transactions wt WHERE wt.user_id = tq.user_id AND wt.type = 'topup') = 0
                THEN tq.balance
                ELSE 0
            END,
            tq.paid_balance = CASE
                WHEN tq.free_credit_claimed = 1
                     AND (SELECT COUNT(*) FROM wallet_transactions wt WHERE wt.user_id = tq.user_id AND wt.type = 'topup') = 0
                THEN 0
                ELSE tq.balance
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Merge balances back
        DB::statement("UPDATE token_quotas SET balance = free_balance + paid_balance");

        Schema::table('token_quotas', function (Blueprint $table) {
            $table->dropColumn(['free_balance', 'paid_balance']);
        });
    }
};
