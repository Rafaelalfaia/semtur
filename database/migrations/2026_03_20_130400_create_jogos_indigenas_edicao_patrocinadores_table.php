<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jogos_indigenas_edicao_patrocinadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jogos_indigenas_edicao_id')
                ->constrained('jogos_indigenas_edicoes')
                ->cascadeOnDelete();
            $table->string('nome', 180);
            $table->string('logo_path')->nullable();
            $table->string('url', 2048)->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['jogos_indigenas_edicao_id', 'ordem'], 'jogos_indigenas_ed_patr_ordem_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jogos_indigenas_edicao_patrocinadores');
    }
};
