<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_model_access', function (Blueprint $table) {
            $table->id();
            $table->string('plan_slug');
            $table->string('model_id');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('plan_slug')->references('slug')->on('subscription_plans');
            $table->unique(['plan_slug', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_model_access');
    }
};
