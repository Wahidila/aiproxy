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
        Schema::create('model_pricings', function (Blueprint $table) {
            $table->id();
            $table->string('model_id', 100)->unique(); // e.g. claude-sonnet-4.5
            $table->string('model_name', 255); // e.g. Claude Sonnet 4.5
            $table->decimal('input_price_usd', 10, 4)->default(0); // USD per 1M input tokens
            $table->decimal('output_price_usd', 10, 4)->default(0); // USD per 1M output tokens
            $table->unsignedTinyInteger('discount_percent')->default(0); // 0-100
            $table->boolean('is_free_tier')->default(false); // available in free tier
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_pricings');
    }
};
