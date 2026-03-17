<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
