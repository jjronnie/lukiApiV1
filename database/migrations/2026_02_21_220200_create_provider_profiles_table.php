<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->ulid('public_id')->unique();
            $table->unsignedInteger('provider_number')->nullable()->unique();
            $table->string('provider_type', 32)->default('individual')->index();
            $table->string('display_name', 120);
            $table->string('legal_name', 160)->nullable();
            $table->text('bio')->nullable();
            $table->string('phone', 32)->nullable()->index();
            $table->string('address_text', 255)->nullable();
            $table->string('business_name', 160)->nullable();
            $table->string('business_address', 255)->nullable();
            $table->timestampTz('onboarding_completed_at')->nullable()->index();
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

            $table->index(
                ['verification_status', 'onboarding_completed_at'],
                'provider_profiles_status_onboarding_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_profiles');
    }
};
