<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 160)->unique();
            $table->decimal('center_lat', 10, 7)->nullable();
            $table->decimal('center_lng', 10, 7)->nullable();
            $table->decimal('radius_km', 8, 2)->nullable();
            $table->unsignedBigInteger('fee_amount')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_fallback')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_zones');
    }
};
