<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_tier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transport_zone_id')->nullable()->constrained('transport_zones')->nullOnDelete();
            $table->string('status', 32)->default('created')->index();
            $table->string('booking_mode', 16)->default('normal')->index();
            $table->unsignedInteger('pair_provider_number')->nullable()->index();
            $table->string('service_name_snapshot', 120)->nullable();
            $table->string('service_tier_name_snapshot', 120)->nullable();
            $table->string('transport_zone_name_snapshot', 120)->nullable();
            $table->boolean('is_scheduled')->default(false)->index();
            $table->timestampTz('scheduled_at')->nullable()->index();
            $table->timestampTz('offering_started_at')->nullable();
            $table->timestampTz('accepted_at')->nullable();
            $table->timestampTz('on_the_way_at')->nullable();
            $table->timestampTz('arrived_at')->nullable();
            $table->timestampTz('in_service_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->timestampTz('expired_at')->nullable();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason', 255)->nullable();
            $table->integer('cancellation_fee_amount')->default(0);
            $table->string('address_text', 255);
            $table->decimal('location_lat', 10, 7);
            $table->decimal('location_lng', 10, 7);
            $table->decimal('provider_last_location_lat', 10, 7)->nullable();
            $table->decimal('provider_last_location_lng', 10, 7)->nullable();
            $table->timestampTz('provider_last_location_at')->nullable();
            $table->unsignedInteger('provider_eta_minutes')->nullable();
            $table->unsignedInteger('provider_distance_meters')->nullable();
            $table->string('place_id', 120)->nullable();
            $table->string('payment_method', 16);
            $table->string('payment_status', 16)->default('unpaid')->index();
            $table->timestampTz('paid_at')->nullable();
            $table->integer('subtotal_amount')->default(0);
            $table->integer('transport_fee_amount')->default(0);
            $table->integer('distance_fee_amount')->default(0);
            $table->integer('overtime_fee_amount')->default(0);
            $table->integer('peak_fee_amount')->default(0);
            $table->integer('tax_amount')->default(0);
            $table->integer('discount_amount')->default(0);
            $table->integer('total_amount')->default(0);
            $table->jsonb('price_breakdown');
            $table->string('promo_code', 40)->nullable();
            $table->unsignedTinyInteger('provider_rating')->nullable();
            $table->text('provider_review')->nullable();
            $table->timestampTz('rated_at')->nullable();
            $table->timestampsTz();

            $table->index(['provider_profile_id', 'status']);
            $table->index(['provider_profile_id', 'scheduled_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['service_id', 'status']);
            $table->index(['status', 'scheduled_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
