<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roteiro_etapa_pontos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('roteiro_etapa_id')->constrained('roteiro_etapas')->cascadeOnDelete();
            $table->foreignId('ponto_turistico_id')->constrained('pontos_turisticos')->cascadeOnDelete();

            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('destaque')->default(false);
            $table->string('observacao_curta', 255)->nullable();
            $table->unsignedSmallInteger('tempo_estimado_min')->nullable();

            $table->timestamps();

            $table->unique(['roteiro_etapa_id', 'ponto_turistico_id'], 'roteiro_etapa_ponto_unique');
            $table->index(['roteiro_etapa_id', 'ordem'], 'roteiro_etapa_ponto_ordem_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roteiro_etapa_pontos');
    }
};
