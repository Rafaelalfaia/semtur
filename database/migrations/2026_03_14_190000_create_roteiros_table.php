<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roteiros', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 180);
            $table->string('slug', 200)->unique();

            $table->text('resumo')->nullable();
            $table->longText('descricao')->nullable();

            $table->string('duracao_slug', 40)->index();
            $table->string('perfil_slug', 60)->index();

            $table->string('publico_label', 120)->nullable();
            $table->string('melhor_epoca', 160)->nullable();
            $table->string('deslocamento', 160)->nullable();
            $table->enum('nivel_intensidade', ['leve', 'moderado', 'intenso'])->nullable()->index();

            $table->string('capa_path')->nullable();

            $table->string('seo_title', 180)->nullable();
            $table->string('seo_description', 255)->nullable();

            $table->unsignedInteger('ordem')->default(0);

            $table->enum('status', ['rascunho', 'publicado', 'arquivado'])
                ->default('rascunho')
                ->index();

            $table->timestamp('published_at')->nullable()->index();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'ordem']);
            $table->index(['status', 'duracao_slug', 'perfil_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roteiros');
    }
};
