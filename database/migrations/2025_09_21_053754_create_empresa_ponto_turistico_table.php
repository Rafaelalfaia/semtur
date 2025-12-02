<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('empresa_ponto_turistico', function (Blueprint $table) {
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ponto_turistico_id')->constrained('pontos_turisticos')->cascadeOnDelete();
            $table->primary(['empresa_id', 'ponto_turistico_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('empresa_ponto_turistico');
    }
};
