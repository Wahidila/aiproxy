<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // free, pro, premium, daily
            $table->string('name');
            $table->enum('type', ['monthly', 'daily'])->default('monthly');
            $table->integer('price_idr')->default(0);
            $table->integer('daily_request_limit')->nullable(); // null = unlimited
            $table->integer('per_minute_limit')->default(6);
            $table->integer('concurrent_limit')->default(1);
            $table->bigInteger('max_token_usage')->nullable(); // null = unlimited
            $table->json('features')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
