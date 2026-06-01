<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('request_timeout');
            $table->string('method', 4);
            $table->unsignedSmallInteger('check_interval');
            $table->foreignId('notification_settings_id')
                ->constrained('notification_settings')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_settings');
    }
};
