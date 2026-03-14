<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_device_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('app_type', 20)->index();
            $table->string('platform', 20)->index();
            $table->text('token');
            $table->string('token_hash', 64)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestampTz('last_seen_at')->nullable()->index();
            $table->timestampsTz();

            $table->index(['user_id', 'app_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_device_tokens');
    }
};
