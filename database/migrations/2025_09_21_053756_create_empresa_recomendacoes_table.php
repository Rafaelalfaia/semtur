<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('empresa_recomendacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('inicio_em')->nullable()->index();
            $table->timestamp('fim_em')->nullable()->index();
            $table->boolean('ativo_forcado')->default(false)->index();
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['empresa_id', 'categoria_id'], 'empresa_cat_unq');
        });
    }
    public function down(): void {
        Schema::dropIfExists('empresa_recomendacoes');
    }
};
