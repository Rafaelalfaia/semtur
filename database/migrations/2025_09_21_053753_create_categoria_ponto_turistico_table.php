<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categoria_ponto_turistico', function (Blueprint $table) {
            $table->foreignId('categoria_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ponto_turistico_id')->constrained('pontos_turisticos')->cascadeOnDelete();
            $table->primary(['categoria_id', 'ponto_turistico_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('categoria_ponto_turistico');
    }
};
