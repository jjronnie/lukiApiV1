<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_service_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_tier_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->unique(['provider_service_id', 'service_tier_id'], 'pst_provider_tier_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_service_tiers');
    }
};
