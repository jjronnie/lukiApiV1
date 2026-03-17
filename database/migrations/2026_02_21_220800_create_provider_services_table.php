<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->string('approval_status', 24)->default('pending')->index();
            $table->timestampTz('requested_at')->nullable()->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->text('review_reason')->nullable();
            $table->timestampsTz();

            $table->unique(['provider_profile_id', 'service_id']);
            $table->index(
                ['provider_profile_id', 'approval_status', 'is_active'],
                'provider_services_profile_status_active_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_services');
    }
};
