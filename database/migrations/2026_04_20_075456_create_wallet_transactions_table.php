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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30); // free_credit, topup, usage, refund, adjustment
            $table->decimal('amount', 12, 2); // positive=credit, negative=debit
            $table->decimal('balance_after', 12, 2);
            $table->string('description', 500);
            $table->nullableMorphs('reference'); // polymorphic: donation, token_usage, etc.
            $table->timestamp('created_at')->nullable();

            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
