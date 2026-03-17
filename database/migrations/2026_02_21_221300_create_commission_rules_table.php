<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
