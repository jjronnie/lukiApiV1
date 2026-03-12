<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'heard_about_source') || Schema::hasColumn('users', 'heard_about_other')) {
            Schema::table('users', function (Blueprint $table): void {
                if (Schema::hasColumn('users', 'heard_about_source')) {
                    $table->dropColumn('heard_about_source');
                }
                if (Schema::hasColumn('users', 'heard_about_other')) {
                    $table->dropColumn('heard_about_other');
                }
            });
        }

        if (Schema::hasColumn('orders', 'location_notes')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('location_notes');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'heard_about_source') || ! Schema::hasColumn('users', 'heard_about_other')) {
            Schema::table('users', function (Blueprint $table): void {
                if (! Schema::hasColumn('users', 'heard_about_source')) {
                    $table->string('heard_about_source', 40)->nullable()->after('profile_completed_at');
                }
                if (! Schema::hasColumn('users', 'heard_about_other')) {
                    $table->string('heard_about_other', 120)->nullable()->after('heard_about_source');
                }
            });
        }

        if (! Schema::hasColumn('orders', 'location_notes')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('location_notes', 255)->nullable()->after('place_id');
            });
        }
    }
};
