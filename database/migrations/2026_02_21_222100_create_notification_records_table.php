<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

            $table->index(['provider_profile_id', 'created_at'], 'notification_records_provider_created_idx');
            $table->index(
                ['provider_profile_id', 'read_at', 'created_at'],
                'notification_records_provider_read_created_idx'
            );
            $table->index(['user_id', 'created_at'], 'notification_records_user_created_idx');
            $table->index(['user_id', 'read_at', 'created_at'], 'notification_records_user_read_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_records');
    }
};
