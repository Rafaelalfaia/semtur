<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conteudo_site_blocos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('conteudo_site_blocos')->nullOnDelete();
            $table->string('pagina', 120);
            $table->string('chave', 120);
            $table->string('rotulo', 160)->nullable();
            $table->string('tipo', 40)->default('rich_text');
            $table->string('regiao', 80)->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->json('configuracao')->nullable();
            $table->string('status', 20)->default('rascunho');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['pagina', 'chave'], 'conteudo_site_blocos_pagina_chave_unique');
            $table->index(['pagina', 'status', 'ordem'], 'conteudo_site_blocos_pagina_status_ordem_idx');
            $table->index(['parent_id', 'ordem'], 'conteudo_site_blocos_parent_ordem_idx');
        });

        Schema::create('conteudo_site_bloco_traducoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conteudo_site_bloco_id')->constrained('conteudo_site_blocos')->cascadeOnDelete();
            $table->foreignId('idioma_id')->constrained('idiomas')->cascadeOnDelete();
            $table->string('eyebrow', 180)->nullable();
            $table->string('titulo', 180)->nullable();
            $table->string('subtitulo', 255)->nullable();
            $table->text('lead')->nullable();
            $table->longText('conteudo')->nullable();
            $table->string('cta_label', 180)->nullable();
            $table->string('cta_href', 500)->nullable();
            $table->string('seo_title', 180)->nullable();
            $table->string('seo_description', 255)->nullable();
            $table->json('extras')->nullable();
            $table->boolean('is_auto_translated')->default(false);
            $table->timestamp('auto_translated_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('source_locale', 12)->nullable();
            $table->string('source_hash', 64)->nullable();
            $table->timestamps();

            $table->unique(
                ['conteudo_site_bloco_id', 'idioma_id'],
                'conteudo_site_bloco_traducoes_bloco_idioma_unique'
            );
        });

        Schema::create('conteudo_site_midias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conteudo_site_bloco_id')->constrained('conteudo_site_blocos')->cascadeOnDelete();
            $table->foreignId('idioma_id')->nullable()->constrained('idiomas')->nullOnDelete();
            $table->string('slot', 60)->default('default');
            $table->string('disk', 40)->default('public');
            $table->string('path', 500);
            $table->string('alt_text', 255)->nullable();
            $table->string('legenda', 255)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedInteger('largura')->nullable();
            $table->unsignedInteger('altura')->nullable();
            $table->unsignedBigInteger('tamanho_bytes')->nullable();
            $table->decimal('focal_x', 5, 2)->nullable();
            $table->decimal('focal_y', 5, 2)->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->json('configuracao')->nullable();
            $table->timestamps();

            $table->index(['conteudo_site_bloco_id', 'slot', 'ordem'], 'conteudo_site_midias_bloco_slot_ordem_idx');
            $table->index(['idioma_id', 'slot'], 'conteudo_site_midias_idioma_slot_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conteudo_site_midias');
        Schema::dropIfExists('conteudo_site_bloco_traducoes');
        Schema::dropIfExists('conteudo_site_blocos');
    }
};
