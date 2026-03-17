<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->index(['expires_at', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
