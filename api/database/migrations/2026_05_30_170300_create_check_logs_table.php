<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->cascadeOnDelete();
            $table->timestamp('checked_at');
            $table->unsignedInteger('redirects_count')->default(0);
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->unsignedInteger('response_time')->nullable();
            $table->json('response_headers')->nullable();
            $table->text('response_body')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_logs');
    }
};
