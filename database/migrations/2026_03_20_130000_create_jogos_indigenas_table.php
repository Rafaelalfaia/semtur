<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jogos_indigenas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 180);
            $table->string('slug', 200)->unique();
            $table->text('descricao')->nullable();
            $table->string('foto_perfil_path')->nullable();
            $table->string('foto_capa_path')->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->string('status', 20)->default('rascunho')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jogos_indigenas');
    }
};
