<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Expand api_keys.tier from 'free'|'paid' to 'free'|'paid'|'subscription'.
     * The column is already varchar(10), so no schema change needed — just update the comment.
     */
    public function up(): void
    {
        // Widen the column to 20 chars to be safe for future tiers
        Schema::table('api_keys', function (Blueprint $table) {
            $table->string('tier', 20)->default('free')->change(); // 'free', 'paid', or 'subscription'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert subscription keys to paid before shrinking
        DB::table('api_keys')->where('tier', 'subscription')->update(['tier' => 'paid']);

        Schema::table('api_keys', function (Blueprint $table) {
            $table->string('tier', 10)->default('free')->change();
        });
    }
};
