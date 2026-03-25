<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roteiro_empresas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('roteiro_id')->constrained('roteiros')->cascadeOnDelete();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table->string('tipo_sugestao', 40)->default('apoio')->index();
            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('destaque')->default(false);
            $table->string('observacao_curta', 255)->nullable();

            $table->timestamps();

            $table->unique(['roteiro_id', 'empresa_id'], 'roteiro_empresa_unique');
            $table->index(['roteiro_id', 'ordem'], 'roteiro_empresa_ordem_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roteiro_empresas');
    }
};
