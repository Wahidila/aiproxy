<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->enum('category', ['umum', 'teknis', 'pembayaran', 'saran', 'lainnya'])->default('umum');
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
