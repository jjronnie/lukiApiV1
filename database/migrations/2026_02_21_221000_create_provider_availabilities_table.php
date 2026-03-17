<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_online')->default(false)->index();
            $table->timestampTz('last_seen_at')->nullable()->index();
            $table->string('timezone', 64)->default('Africa/Kampala');
            $table->jsonb('weekly_schedule')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_availabilities');
    }
};
