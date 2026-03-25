<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('espaco_cultural_agendamentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('espaco_cultural_id')
                ->constrained('espacos_culturais')
                ->cascadeOnDelete();

            $table->foreignId('espaco_cultural_horario_id')
                ->nullable()
                ->constrained('espaco_cultural_horarios')
                ->nullOnDelete();

            $table->date('data_visita')->index();

            $table->string('protocolo', 40)->unique();

            $table->string('nome', 160);
            $table->string('telefone', 30);
            $table->string('email', 160)->nullable();

            $table->unsignedInteger('qtd_visitantes')->default(1);

            $table->text('observacao_visitante')->nullable();
            $table->text('observacao_interna')->nullable();

            $table->string('status', 30)->default('pendente')->index();

            $table->string('whatsapp_phone', 30)->nullable();
            $table->text('whatsapp_message')->nullable();
            $table->timestamp('whatsapp_clicked_at')->nullable();

            $table->timestamp('expirado_em')->nullable();
            $table->timestamp('confirmado_em')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->timestamp('concluido_em')->nullable();

            $table->foreignId('tecnico_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('atribuido_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['espaco_cultural_id', 'status', 'data_visita']);
            $table->index(['tecnico_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('espaco_cultural_agendamentos');
    }
};
