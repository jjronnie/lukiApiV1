<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 24);
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_tier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('add_on_id')->nullable()->constrained('service_add_ons')->nullOnDelete();
            $table->string('name_snapshot', 120);
            $table->string('tier_name_snapshot', 120)->nullable();
            $table->integer('unit_price_amount');
            $table->unsignedInteger('quantity')->default(1);
            $table->integer('line_total_amount');
            $table->timestampsTz();

            $table->index(['order_id', 'item_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
