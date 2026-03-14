<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('name', 120);
            $table->string('slug', 160)->unique();
            $table->string('icon_name', 80);
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestampsTz();
        });

        Schema::create('provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->ulid('public_id')->unique();
            $table->unsignedInteger('provider_number')->nullable()->unique();
            $table->string('provider_type', 32)->default('individual');
            $table->string('display_name', 120);
            $table->string('legal_name', 160)->nullable();
            $table->text('bio')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('address_text', 255)->nullable();
            $table->string('business_name', 160)->nullable();
            $table->string('business_address', 255)->nullable();
            $table->timestampTz('onboarding_completed_at')->nullable();
            $table->string('avatar_path')->nullable();
            $table->timestampTz('avatar_locked_at')->nullable();
            $table->string('verification_status', 32)->default('pending')->index();
            $table->timestampTz('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('completed_orders_count')->default(0);
            $table->unsignedInteger('cancelled_orders_count')->default(0);
            $table->timestampsTz();
        });

        Schema::create('provider_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 40);
            $table->string('status', 32)->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('file_hash', 128)->nullable();
            $table->timestampsTz();

            $table->index(['provider_profile_id', 'document_type']);
        });

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
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestampsTz();
        });

        Schema::create('service_add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->ulid('public_id')->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->integer('price_amount');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestampsTz();

            $table->index(['service_id', 'is_active']);
        });

        Schema::create('service_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rule_type', 40);
            $table->jsonb('config');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('priority')->default(100);
            $table->timestampsTz();

            $table->index(['service_id', 'rule_type', 'is_active']);
        });

        Schema::create('service_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->ulid('public_id')->unique();
            $table->string('name', 80);
            $table->string('slug', 120);
            $table->integer('price_amount');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestampsTz();

            $table->unique(['service_id', 'slug']);
            $table->index(['service_id', 'is_active', 'sort_order']);
        });

        Schema::create('provider_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->string('approval_status', 24)->default('pending')->index();
            $table->timestampTz('requested_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->text('review_reason')->nullable();
            $table->timestampsTz();

            $table->unique(['provider_profile_id', 'service_id']);
        });

        Schema::create('provider_service_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_tier_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->unique(['provider_service_id', 'service_tier_id'], 'pst_provider_tier_unique');
        });

        Schema::create('provider_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_online')->default(false)->index();
            $table->timestampTz('last_seen_at')->nullable()->index();
            $table->string('timezone', 64)->default('Africa/Kampala');
            $table->jsonb('weekly_schedule')->nullable();
            $table->timestampsTz();
        });

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

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->unique()->constrained()->cascadeOnDelete();
            $table->ulid('public_id')->unique();
            $table->string('currency', 3)->default('UGX');
            $table->bigInteger('balance_amount')->default(0);
            $table->bigInteger('hold_amount')->default(0);
            $table->bigInteger('min_required_amount')->default(0);
            $table->string('status', 32)->default('active')->index();
            $table->timestampsTz();
        });

        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('commission_type', 24);
            $table->decimal('value', 10, 4);
            $table->bigInteger('min_commission_amount')->nullable();
            $table->bigInteger('max_commission_amount')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestampTz('effective_from')->nullable();
            $table->timestampTz('effective_to')->nullable();
            $table->timestampsTz();

            $table->index(['service_id', 'is_active']);
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

            $table->boolean('is_scheduled')->default(false);
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
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'scheduled_at']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 24);
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_tier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('add_on_id')->nullable()->constrained('service_add_ons')->nullOnDelete();
            $table->string('name_snapshot', 120);
            $table->string('tier_name_snapshot', 120)->nullable();
            $table->integer('unit_price_amount');
            $table->unsignedInteger('quantity')->default(1);
            $table->integer('line_total_amount');
            $table->timestampsTz();

            $table->index(['order_id', 'item_type']);
        });

        Schema::create('order_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('batch_no')->default(1);
            $table->string('status', 24)->default('pending')->index();
            $table->timestampTz('expires_at');
            $table->timestampTz('responded_at')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestampTz('created_at');

            $table->index(['provider_profile_id', 'status', 'expires_at']);
            $table->index(['order_id', 'batch_no']);
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32);
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->jsonb('meta')->nullable();
            $table->timestampTz('created_at')->index();

            $table->index(['order_id', 'created_at']);
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40);
            $table->bigInteger('amount');
            $table->bigInteger('balance_after');
            $table->string('reference', 80)->unique()->nullable();
            $table->jsonb('meta')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('created_at')->index();

            $table->index(['wallet_id', 'created_at']);
        });

        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 40);
            $table->text('description');
            $table->jsonb('attachments')->nullable();
            $table->string('status', 24)->default('open')->index();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestampsTz();
        });

        Schema::create('notification_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 64);
            $table->string('title', 120);
            $table->text('body');
            $table->jsonb('payload')->nullable();
            $table->timestampTz('read_at')->nullable()->index();
            $table->timestampsTz();

            $table->index(['provider_profile_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80)->index();
            $table->string('auditable_type', 120)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->jsonb('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestampsTz();

            $table->index(['auditable_type', 'auditable_id']);
        });

        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('scope', 80);
            $table->string('idempotency_key', 120);
            $table->string('request_hash', 128);
            $table->unsignedSmallInteger('response_code');
            $table->jsonb('response_body');
            $table->timestampTz('expires_at')->index();
            $table->timestampsTz();

            $table->unique(['user_id', 'scope', 'idempotency_key']);
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('discount_type', 24);
            $table->decimal('value', 10, 4);
            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('idempotency_keys');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notification_records');
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_offers');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('transport_zones');
        Schema::dropIfExists('commission_rules');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('provider_locations');
        Schema::dropIfExists('provider_availabilities');
        Schema::dropIfExists('provider_service_tiers');
        Schema::dropIfExists('provider_services');
        Schema::dropIfExists('service_tiers');
        Schema::dropIfExists('service_pricing_rules');
        Schema::dropIfExists('service_add_ons');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
        Schema::dropIfExists('provider_documents');
        Schema::dropIfExists('provider_profiles');
    }
};
