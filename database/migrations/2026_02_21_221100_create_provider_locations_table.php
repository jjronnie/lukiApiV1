<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->decimal('heading', 7, 2)->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->string('source', 32)->default('app');
            $table->timestampTz('recorded_at')->index();

            $table->index(['provider_profile_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_locations');
    }
};
