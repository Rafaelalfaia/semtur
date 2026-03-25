<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jogos_indigenas_edicoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jogos_indigenas_id')
                ->constrained('jogos_indigenas')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('ano')->index();
            $table->string('titulo', 180);
            $table->string('slug', 200);
            $table->text('descricao')->nullable();
            $table->string('capa_path')->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->string('status', 20)->default('rascunho')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['jogos_indigenas_id', 'ano'], 'jogos_indigenas_edicoes_ano_unique');
            $table->unique(['jogos_indigenas_id', 'slug'], 'jogos_indigenas_edicoes_slug_unique');
            $table->index(['jogos_indigenas_id', 'status', 'ordem'], 'jogos_indigenas_edicoes_status_ordem_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jogos_indigenas_edicoes');
    }
};
