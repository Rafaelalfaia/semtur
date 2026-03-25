<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rota_do_cacau_edicao_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rota_do_cacau_edicao_id')
                ->constrained('rota_do_cacau_edicoes')
                ->cascadeOnDelete();
            $table->string('titulo', 180);
            $table->text('descricao')->nullable();
            $table->string('drive_url', 2048);
            $table->string('embed_url', 2048)->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['rota_do_cacau_edicao_id', 'ordem'], 'rota_do_cacau_ed_videos_ordem_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rota_do_cacau_edicao_videos');
    }
};
