<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ponto_midias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ponto_turistico_id')->constrained('pontos_turisticos')->cascadeOnDelete();
            $table->enum('tipo', ['image','video'])->default('image');
            $table->string('path');
            $table->string('thumb_path')->nullable();
            $table->unsignedInteger('ordem')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void {
        Schema::dropIfExists('ponto_midias');
    }
};
