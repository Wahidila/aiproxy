<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plan_slug');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable(); // null = forever (free)
            $table->bigInteger('token_usage_total')->default(0);
            $table->integer('daily_requests_used')->default(0);
            $table->timestamp('daily_requests_reset_at')->nullable();
            $table->timestamps();

            $table->foreign('plan_slug')->references('slug')->on('subscription_plans');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
