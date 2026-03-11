<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('services', 'image_url')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('image_url', 255)->nullable()->after('icon_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('services', 'image_url')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('image_url');
            });
        }
    }
};
