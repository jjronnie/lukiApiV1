<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('service_category_id')->nullable()->constrained('service_categories')->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name', 120);
            $table->string('icon_name', 80)->nullable();
            $table->string('image_url', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('currency', 3)->default('UGX');
            $table->integer('base_price_amount');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestampsTz();

            $table->index(['service_category_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
