<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_categories') && !Schema::hasColumn('service_categories', 'is_featured')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->boolean('is_featured')->default(false)->index()->after('image_url');
            });
        }

        if (!Schema::hasTable('transport_zones')) {
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

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'transport_zone_id')) {
                    $table->foreignId('transport_zone_id')->nullable()->after('service_tier_id')->constrained('transport_zones')->nullOnDelete();
                }
                if (!Schema::hasColumn('orders', 'transport_zone_name_snapshot')) {
                    $table->string('transport_zone_name_snapshot', 120)->nullable()->after('service_tier_name_snapshot');
                }
                if (!Schema::hasColumn('orders', 'transport_fee_amount')) {
                    $table->integer('transport_fee_amount')->default(0)->after('subtotal_amount');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'transport_zone_id')) {
                    $table->dropConstrainedForeignId('transport_zone_id');
                }
                $columnsToDrop = [];
                if (Schema::hasColumn('orders', 'transport_zone_name_snapshot')) {
                    $columnsToDrop[] = 'transport_zone_name_snapshot';
                }
                if (Schema::hasColumn('orders', 'transport_fee_amount')) {
                    $columnsToDrop[] = 'transport_fee_amount';
                }
                if ($columnsToDrop !== []) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }

        if (Schema::hasTable('transport_zones')) {
            Schema::drop('transport_zones');
        }

        if (Schema::hasTable('service_categories') && Schema::hasColumn('service_categories', 'is_featured')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropColumn('is_featured');
            });
        }
    }
};
