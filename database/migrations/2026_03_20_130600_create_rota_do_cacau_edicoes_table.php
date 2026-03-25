<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rota_do_cacau_edicoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rota_do_cacau_id')
                ->constrained('rota_do_cacau')
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

            $table->unique(['rota_do_cacau_id', 'ano'], 'rota_do_cacau_edicoes_ano_unique');
            $table->unique(['rota_do_cacau_id', 'slug'], 'rota_do_cacau_edicoes_slug_unique');
            $table->index(['rota_do_cacau_id', 'status', 'ordem'], 'rota_do_cacau_edicoes_status_ordem_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rota_do_cacau_edicoes');
    }
};
