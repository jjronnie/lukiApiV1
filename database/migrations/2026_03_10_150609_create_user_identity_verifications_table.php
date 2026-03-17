<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_identity_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->ulid('public_id')->unique();
            $table->string('status', 24)->default('pending')->index();
            $table->string('id_type', 40)->index();
            $table->boolean('is_age_confirmed')->default(false);
            $table->string('id_number', 80)->nullable()->unique();
            $table->date('date_of_birth')->nullable();
            $table->string('district_id', 32)->nullable()->index();
            $table->string('district_name', 120)->nullable();
            $table->string('county_id', 32)->nullable();
            $table->string('county_name', 120)->nullable();
            $table->string('sub_county_id', 32)->nullable();
            $table->string('sub_county_name', 120)->nullable();
            $table->string('parish_id', 32)->nullable();
            $table->string('parish_name', 120)->nullable();
            $table->string('village_id', 32)->nullable();
            $table->string('village_name', 120)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('submitted_at')->nullable()->index();
            $table->timestampTz('reviewed_at')->nullable()->index();
            $table->timestampTz('verified_at')->nullable()->index();
            $table->text('rejection_reason')->nullable();
            $table->timestampsTz();

            $table->index(['status', 'submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_identity_verifications');
    }
};
