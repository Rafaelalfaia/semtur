<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rota_do_cacau_edicao_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rota_do_cacau_edicao_id')
                ->constrained('rota_do_cacau_edicoes')
                ->cascadeOnDelete();
            $table->string('imagem_path');
            $table->string('legenda', 180)->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['rota_do_cacau_edicao_id', 'ordem'], 'rota_do_cacau_ed_fotos_ordem_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rota_do_cacau_edicao_fotos');
    }
};
