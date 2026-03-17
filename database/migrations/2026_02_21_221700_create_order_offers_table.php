<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('order_offers');
    }
};
