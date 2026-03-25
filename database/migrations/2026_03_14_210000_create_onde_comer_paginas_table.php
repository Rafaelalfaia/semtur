<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('onde_comer_paginas', function (Blueprint $table) {
            $table->id();

            $table->string('titulo', 180)->default('Onde comer em Altamira');
            $table->string('subtitulo', 180)->nullable();
            $table->text('resumo')->nullable();

            $table->longText('texto_intro')->nullable();
            $table->longText('texto_gastronomia_local')->nullable();
            $table->longText('texto_dicas')->nullable();

            $table->string('hero_path')->nullable();

            $table->string('seo_title', 180)->nullable();
            $table->string('seo_description', 255)->nullable();

            $table->string('status', 20)->default('rascunho')->index();
            $table->timestamp('published_at')->nullable()->index();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onde_comer_paginas');
    }
};
