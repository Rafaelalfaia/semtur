<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('system_settings', 'active_console_theme_id')) {
                $table->foreignId('active_console_theme_id')
                    ->nullable()
                    ->after('active_theme_id')
                    ->constrained('themes')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('system_settings', 'active_site_theme_id')) {
                $table->foreignId('active_site_theme_id')
                    ->nullable()
                    ->after('active_console_theme_id')
                    ->constrained('themes')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('system_settings', 'active_auth_theme_id')) {
                $table->foreignId('active_auth_theme_id')
                    ->nullable()
                    ->after('active_site_theme_id')
                    ->constrained('themes')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table) {
            if (Schema::hasColumn('system_settings', 'active_auth_theme_id')) {
                $table->dropConstrainedForeignId('active_auth_theme_id');
            }

            if (Schema::hasColumn('system_settings', 'active_site_theme_id')) {
                $table->dropConstrainedForeignId('active_site_theme_id');
            }

            if (Schema::hasColumn('system_settings', 'active_console_theme_id')) {
                $table->dropConstrainedForeignId('active_console_theme_id');
            }
        });
    }
};
