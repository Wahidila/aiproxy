<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('api_key_id')->constrained('subscription_api_keys')->onDelete('cascade');
            $table->string('model');
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->decimal('cost_usd', 10, 6)->default(0);
            $table->decimal('cost_idr', 12, 2)->default(0);
            $table->string('request_path')->nullable();
            $table->integer('status_code')->default(200);
            $table->integer('response_time_ms')->default(0);
            $table->timestamp('cycle_start');                // start of the 6-hour cycle
            $table->timestamp('created_at')->nullable();

            $table->index(['subscription_id', 'cycle_start']);
            $table->index(['api_key_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_usages');
    }
};
