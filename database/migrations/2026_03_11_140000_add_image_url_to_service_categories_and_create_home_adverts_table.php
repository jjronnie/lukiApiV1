<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('service_categories', 'image_url')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->string('image_url')->nullable()->after('icon_name');
            });
        }

        if (!Schema::hasTable('home_adverts')) {
            Schema::create('home_adverts', function (Blueprint $table) {
                $table->id();
                $table->ulid('public_id')->unique();
                $table->string('title', 120);
                $table->string('headline', 160)->nullable();
                $table->text('description')->nullable();
                $table->string('button_text', 60)->nullable();
                $table->string('link_type', 16)->default('none');
                $table->string('link_target', 255)->nullable();
                $table->string('image_url');
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestampTz('starts_at')->nullable()->index();
                $table->timestampTz('ends_at')->nullable()->index();
                $table->timestampsTz();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_adverts');

        if (Schema::hasColumn('service_categories', 'image_url')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropColumn('image_url');
            });
        }
    }
};
