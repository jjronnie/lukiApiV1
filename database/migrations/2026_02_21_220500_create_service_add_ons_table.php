<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->ulid('public_id')->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->integer('price_amount');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestampsTz();

            $table->index(['service_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_add_ons');
    }
};
