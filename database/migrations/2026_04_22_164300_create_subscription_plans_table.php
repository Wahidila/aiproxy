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
            $table->string('name');                          // "Basic", "Pro"
            $table->string('slug')->unique();                // "basic", "pro"
            $table->integer('price_idr');                    // 19900, 49900
            $table->integer('rpm_limit')->default(30);       // requests per minute
            $table->integer('parallel_limit')->default(3);   // concurrent requests
            $table->decimal('budget_usd_per_cycle', 10, 2);  // $5.00, $15.00
            $table->integer('cycle_hours')->default(6);      // budget reset every N hours
            $table->json('allowed_models')->nullable();      // null = all models
            $table->text('description')->nullable();
            $table->json('features')->nullable();            // feature list for landing page
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
