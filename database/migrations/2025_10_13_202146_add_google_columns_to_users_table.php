<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // adiciona google_id se não existir
        if (!Schema::hasColumn('users', 'google_id')) {
            Schema::table('users', function (Blueprint $t) {
                $t->string('google_id')->nullable()->unique(); // sem ->after() porque PG não usa
            });
        }

        // adiciona avatar_url se não existir
        if (!Schema::hasColumn('users', 'avatar_url')) {
            Schema::table('users', function (Blueprint $t) {
                $t->string('avatar_url')->nullable();
            });
        }
    }

    public function down(): void
    {
        // remove só se existir
        if (Schema::hasColumn('users', 'google_id') || Schema::hasColumn('users', 'avatar_url')) {
            Schema::table('users', function (Blueprint $t) {
                if (Schema::hasColumn('users', 'google_id')) {
                    $t->dropColumn('google_id');
                }
                if (Schema::hasColumn('users', 'avatar_url')) {
                    $t->dropColumn('avatar_url');
                }
            });
        }
    }
};
