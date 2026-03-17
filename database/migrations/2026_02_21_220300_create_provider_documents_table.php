<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 40);
            $table->string('status', 32)->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('file_hash', 128)->nullable();
            $table->timestampsTz();

            $table->index(['provider_profile_id', 'document_type']);
            $table->index(['status', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_documents');
    }
};
