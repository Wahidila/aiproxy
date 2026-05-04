<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('model_pricings', function (Blueprint $table) {
            $table->unsignedInteger('max_context_tokens')->nullable()->after('is_free_tier');
        });

        // Seed real context window values by model_id
        $contextWindows = [
            'claude-opus-4.6'   => 200000,
            'claude-sonnet-4.5' => 200000,
            'claude-haiku-4.5'  => 200000,
            'gpt-4.1'           => 1047576,
            'gpt-4.1-mini'      => 1047576,
            'gpt-4.1-nano'      => 1047576,
            'gpt-5.5'           => 200000,
            'gpt-5.3-codex'     => 200000,
            'gpt-5.4'           => 200000,
            'o3'                => 200000,
            'o4-mini'           => 200000,
            'gemini-2.5-pro'    => 1048576,
            'gemini-2.5-flash'  => 1048576,
            'deepseek-r1'       => 131072,
            'deepseek-3.2'      => 131072,
            'minimax-m2.5'      => 1048576,
            'minimax-m2.1'      => 245760,
            'glm-5'             => 131072,
        ];

        foreach ($contextWindows as $modelId => $maxTokens) {
            DB::table('model_pricings')
                ->where('model_id', $modelId)
                ->update(['max_context_tokens' => $maxTokens]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('model_pricings', function (Blueprint $table) {
            $table->dropColumn('max_context_tokens');
        });
    }
};
