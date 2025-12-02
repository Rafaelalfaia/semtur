<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Remove a UNIQUE CONSTRAINT antiga (nome mostrado no erro)
        DB::statement('ALTER TABLE categorias DROP CONSTRAINT IF EXISTS categorias_slug_unique');

        // 2) Cria UNIQUE INDEX PARCIAL: só vale para registros ativos (deleted_at IS NULL)
        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS categorias_slug_active_unique
            ON categorias (slug)
            WHERE deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        // 1) Remove o índice parcial
        DB::statement('DROP INDEX IF EXISTS categorias_slug_active_unique');

        // 2) Restaura a UNIQUE CONSTRAINT tradicional (sem parcial)
        DB::statement('ALTER TABLE categorias ADD CONSTRAINT categorias_slug_unique UNIQUE (slug)');
    }
};
