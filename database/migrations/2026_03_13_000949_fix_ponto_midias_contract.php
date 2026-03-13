<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('ponto_midias')) {
            return;
        }

        if (!Schema::hasColumn('ponto_midias', 'url')) {
            Schema::table('ponto_midias', function (Blueprint $table) {
                $table->string('url', 2048)->nullable()->after('path');
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            $constraint = DB::selectOne("
                SELECT conname
                FROM pg_constraint
                WHERE conrelid = 'ponto_midias'::regclass
                  AND contype = 'c'
                  AND pg_get_constraintdef(oid) ILIKE '%tipo%'
                LIMIT 1
            ");

            if ($constraint?->conname) {
                DB::statement('ALTER TABLE ponto_midias DROP CONSTRAINT "' . $constraint->conname . '"');
            }

            DB::statement("ALTER TABLE ponto_midias ALTER COLUMN tipo TYPE varchar(20)");
            DB::statement("ALTER TABLE ponto_midias ALTER COLUMN tipo SET DEFAULT 'image'");
            DB::statement("
                ALTER TABLE ponto_midias
                ADD CONSTRAINT ponto_midias_tipo_check
                CHECK (tipo IN ('image','video','video_file','video_link'))
            ");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('ponto_midias')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE ponto_midias DROP CONSTRAINT IF EXISTS ponto_midias_tipo_check');
            DB::statement("
                ALTER TABLE ponto_midias
                ADD CONSTRAINT ponto_midias_tipo_check
                CHECK (tipo IN ('image','video'))
            ");
            DB::statement("ALTER TABLE ponto_midias ALTER COLUMN tipo SET DEFAULT 'image'");
        }

        if (Schema::hasColumn('ponto_midias', 'url')) {
            Schema::table('ponto_midias', function (Blueprint $table) {
                $table->dropColumn('url');
            });
        }
    }
};
