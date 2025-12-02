<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banner_destaques', function (Blueprint $table) {
            $table->id();

            // Conteúdo
            $table->string('titulo', 160)->nullable();
            $table->string('subtitulo', 255)->nullable();
            $table->string('link_url', 500)->nullable();
            $table->boolean('target_blank')->default(false);

            // Imagens (paths no storage)
            $table->string('imagem_mobile_path')->nullable();
            $table->string('imagem_desktop_path')->nullable();

            // Enquadramento salvo (crop)
            // -> armazenaremos os dados retornados pelo Cropper (x,y,width,height,scaleX,scaleY,rotate)
            $table->json('crop_mobile')->nullable();
            $table->json('crop_desktop')->nullable();

            // Estética
            $table->string('cor_fundo', 20)->nullable();                // ex: #00837B
            $table->unsignedTinyInteger('overlay_opacity')->default(0); // 0..100

            // Publicação / ordenação
            $table->string('status', 20)->default('rascunho'); // rascunho|publicado|arquivado
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamp('inicio_publicacao')->nullable();
            $table->timestamp('fim_publicacao')->nullable();

            // Autoria (opcional)
            $table->foreignId('criado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('atualizado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Índices úteis
            $table->index(['status', 'ordem']);
            $table->index(['inicio_publicacao', 'fim_publicacao']);
        });

        // Check constraint de status (PostgreSQL)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                ALTER TABLE banner_destaques
                ADD CONSTRAINT banner_destaques_status_check
                CHECK (status IN ('rascunho','publicado','arquivado'))
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_destaques');
    }
};
