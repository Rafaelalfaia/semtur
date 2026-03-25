<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('espaco_cultural_horarios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('espaco_cultural_id')
                ->constrained('espacos_culturais')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('dia_semana')->index(); // 0=Dom, 6=Sáb
            $table->time('hora_inicio');
            $table->time('hora_fim');

            $table->unsignedInteger('vagas')->nullable();
            $table->string('observacao', 190)->nullable();

            $table->boolean('ativo')->default(true);
            $table->unsignedInteger('ordem')->default(0);

            $table->timestamps();

            $table->index(['espaco_cultural_id', 'dia_semana', 'ativo']);
            $table->unique(
                ['espaco_cultural_id', 'dia_semana', 'hora_inicio', 'hora_fim'],
                'ec_horarios_unique_slot'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('espaco_cultural_horarios');
    }
};
