<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('avisos', function (Blueprint $t) {
            $t->id();
            $t->string('titulo');
            $t->text('descricao');
            $t->string('whatsapp')->nullable();       // ex: 5593999998888 (só números)
            $t->string('imagem_path')->nullable();     // Storage path
            $t->string('status')->default('publicado'); // publicado|rascunho|arquivado
            $t->timestamp('inicio_em')->nullable();    // opcional (janela de exibição)
            $t->timestamp('fim_em')->nullable();       // opcional
            $t->timestamps();

            $t->index(['status','inicio_em','fim_em']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('avisos');
    }
};
