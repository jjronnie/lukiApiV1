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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestampTz('email_verified_at')->nullable();
            $table->string('password');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestampTz('two_factor_confirmed_at')->nullable();

            $table->string('phone', 32)->nullable()->unique();
            $table->string('phone_country_code', 8)->nullable();
            $table->string('phone_local_number', 24)->nullable();
            $table->timestampTz('phone_verified_at')->nullable();
            $table->string('referral_code', 24)->nullable()->unique();
            $table->timestampTz('last_seen_at')->nullable();
            $table->timestampTz('profile_completed_at')->nullable();
            $table->string('heard_about_source', 40)->nullable();
            $table->string('heard_about_other', 120)->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->string('google_id')->nullable()->unique();
            $table->string('signup_method', 24)->default('email');

            $table->softDeletesTz();

            $table->rememberToken();
            $table->timestampsTz();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestampTz('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
