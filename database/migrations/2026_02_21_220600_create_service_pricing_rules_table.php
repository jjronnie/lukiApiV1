<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rule_type', 40);
            $table->jsonb('config');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('priority')->default(100)->index();
            $table->timestampsTz();

            $table->index(['service_id', 'rule_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_pricing_rules');
    }
};
