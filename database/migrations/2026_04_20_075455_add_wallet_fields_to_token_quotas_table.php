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
            $table->decimal('balance', 12, 2)->default(0)->after('user_id'); // IDR balance
            $table->boolean('free_credit_claimed')->default(false)->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('token_quotas', function (Blueprint $table) {
            $table->dropColumn(['balance', 'free_credit_claimed']);
        });
    }
};
