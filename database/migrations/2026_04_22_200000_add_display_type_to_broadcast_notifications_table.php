<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('broadcast_notifications', function (Blueprint $table) {
            $table->string('display_type', 20)->default('both')->after('type'); // banner, popup, both
        });
    }

    public function down(): void
    {
        Schema::table('broadcast_notifications', function (Blueprint $table) {
            $table->dropColumn('display_type');
        });
    }
};
