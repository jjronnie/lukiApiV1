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
        Schema::create('email_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('email', 255)->index();
            $table->string('purpose', 32)->index();
            $table->string('app_type', 24)->default('customer');
            $table->string('otp_hash', 128);
            $table->string('token_hash', 128)->unique();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('resend_count')->default(0);
            $table->timestampTz('last_sent_at')->nullable();
            $table->timestampTz('resend_window_started_at')->nullable();
            $table->timestampTz('expires_at')->index();
            $table->timestampTz('consumed_at')->nullable();
            $table->timestampsTz();

            $table->index(['email', 'purpose']);
            $table->index(['email', 'purpose', 'app_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_otps');
    }
};
