<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->index()->after('image_url');
        });

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

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('transport_zone_id')->nullable()->after('service_tier_id')->constrained('transport_zones')->nullOnDelete();
            $table->string('transport_zone_name_snapshot', 120)->nullable()->after('service_tier_name_snapshot');
            $table->integer('transport_fee_amount')->default(0)->after('subtotal_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transport_zone_id');
            $table->dropColumn(['transport_zone_name_snapshot', 'transport_fee_amount']);
        });

        Schema::dropIfExists('transport_zones');

        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropColumn('is_featured');
        });
    }
};
