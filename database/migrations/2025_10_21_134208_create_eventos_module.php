<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $t) {
            $t->id();
            $t->string('nome');
            $t->string('slug')->unique();
            $t->string('cidade')->nullable();
            $t->string('regiao')->nullable();
            $t->text('descricao')->nullable();
            $t->string('capa_path')->nullable();
            $t->string('perfil_path')->nullable();
            $t->decimal('rating', 2, 1)->default(0);
            $t->string('status', 20)->default('publicado');
            $t->timestamps();

            $t->index(['status']);
            $t->index(['created_at']);
        });

        // ====== evento_edicoes (edições por ano) ======
        Schema::create('evento_edicoes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $t->unsignedSmallInteger('ano');                 // 2024, 2025...
            $t->date('data_inicio')->nullable();
            $t->date('data_fim')->nullable();
            $t->string('local')->nullable();
            $t->text('resumo')->nullable();
            // localização opcional da edição (para mapa do local do ano)
            $t->decimal('lat', 9, 6)->nullable();
            $t->decimal('lng', 9, 6)->nullable();
            $t->string('status', 20)->default('publicado');
            $t->timestamps();

            $t->unique(['evento_id', 'ano']);
            $t->index(['evento_id', 'status']);
            $t->index(['created_at']);
        });

        // ====== evento_atrativos (atrativos por edição) ======
        Schema::create('evento_atrativos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('edicao_id')->constrained('evento_edicoes')->cascadeOnDelete();
            $t->string('nome');
            $t->string('slug');
            $t->text('descricao')->nullable();
            $t->string('thumb_path')->nullable();
            $t->unsignedInteger('ordem')->default(1);
            $t->string('status', 20)->default('publicado');
            $t->timestamps();

            $t->unique(['edicao_id', 'slug']);
            $t->index(['edicao_id', 'ordem']);
            $t->index(['status']);
        });

        // ====== evento_midias (galeria por edição) ======
        Schema::create('evento_midias', function (Blueprint $t) {
            $t->id();
            $t->foreignId('edicao_id')->constrained('evento_edicoes')->cascadeOnDelete();
            $t->string('path', 2048);                        // Storage::url()
            $t->string('alt')->nullable();
            $t->unsignedInteger('ordem')->default(1);
            $t->string('tipo', 10)->default('foto');         // foto|video (flexível)
            $t->timestamps();

            $t->index(['edicao_id', 'ordem']);
            $t->index(['tipo']);
        });

        // ====== Checks/otimizações específicas para PostgreSQL ======
        if (DB::getDriverName() === 'pgsql') {
            // status (texto) padronizado
            DB::statement("ALTER TABLE eventos
                ADD CONSTRAINT eventos_status_check
                CHECK (status IN ('publicado','rascunho','arquivado'))");

            DB::statement("ALTER TABLE evento_edicoes
                ADD CONSTRAINT evento_edicoes_status_check
                CHECK (status IN ('publicado','rascunho','arquivado'))");

            DB::statement("ALTER TABLE evento_atrativos
                ADD CONSTRAINT evento_atrativos_status_check
                CHECK (status IN ('publicado','rascunho','arquivado'))");

            // latitude/longitude válidos (se informados)
            DB::statement("ALTER TABLE evento_edicoes
                ADD CONSTRAINT evento_edicoes_lat_check
                CHECK (lat IS NULL OR (lat >= -90 AND lat <= 90))");

            DB::statement("ALTER TABLE evento_edicoes
                ADD CONSTRAINT evento_edicoes_lng_check
                CHECK (lng IS NULL OR (lng >= -180 AND lng <= 180))");

            // (opcional) habilitar trigram para buscas por nome/slug
            DB::statement("CREATE EXTENSION IF NOT EXISTS pg_trgm");
            DB::statement("CREATE INDEX IF NOT EXISTS idx_eventos_nome_trgm ON eventos USING gin (nome gin_trgm_ops)");
            DB::statement("CREATE INDEX IF NOT EXISTS idx_eventos_slug_trgm ON eventos USING gin (slug gin_trgm_ops)");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_midias');
        Schema::dropIfExists('evento_atrativos');
        Schema::dropIfExists('evento_edicoes');
        Schema::dropIfExists('eventos');
    }
};
