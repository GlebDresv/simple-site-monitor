<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('check_logs', function (Blueprint $table) {
            $table->index(['domain_id', 'checked_at']);
            $table->index('checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('check_logs', function (Blueprint $table) {
            $table->dropIndex(['domain_id', 'checked_at']);
            $table->dropIndex(['checked_at']);
        });
    }
};
