<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('espacos_culturais', function (Blueprint $table) {
            $table->id();

            $table->string('tipo', 30)->index(); // museu | teatro
            $table->string('nome', 160);
            $table->string('slug', 180)->unique();

            $table->string('resumo', 255)->nullable();
            $table->text('descricao')->nullable();

            $table->string('capa_path')->nullable();

            $table->string('maps_url', 2048)->nullable();
            $table->string('endereco')->nullable();
            $table->string('bairro', 120)->nullable();
            $table->string('cidade', 120)->default('Altamira');

            $table->decimal('lat', 10, 7)->nullable()->index();
            $table->decimal('lng', 10, 7)->nullable()->index();

            $table->unsignedInteger('ordem')->default(0);

            $table->string('status', 30)->default('rascunho')->index(); // rascunho|publicado|arquivado
            $table->timestamp('published_at')->nullable()->index();

            $table->boolean('agendamento_ativo')->default(false);
            $table->string('agendamento_contato_nome', 120)->nullable();
            $table->string('agendamento_contato_label', 80)->nullable();
            $table->string('agendamento_whatsapp_phone', 30)->nullable();
            $table->text('agendamento_instrucoes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tipo', 'status', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('espacos_culturais');
    }
};
