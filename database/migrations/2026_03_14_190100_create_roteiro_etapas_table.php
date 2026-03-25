<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roteiro_etapas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roteiro_id')->constrained('roteiros')->cascadeOnDelete();

            $table->string('titulo', 120);
            $table->string('subtitulo', 160)->nullable();
            $table->longText('descricao')->nullable();

            $table->string('tipo_bloco', 30)->default('extra')->index();
            $table->unsignedInteger('ordem')->default(0);

            $table->timestamps();

            $table->index(['roteiro_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roteiro_etapas');
    }
};
