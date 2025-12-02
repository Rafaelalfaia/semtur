<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('equipe_membros', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 160);
            $table->string('slug', 180)->unique();
            $table->string('cargo', 160)->nullable();
            $table->string('resumo', 280)->nullable();

            $table->string('foto_path')->nullable();
            $table->json('redes')->nullable(); // {instagram, linkedin, whatsapp, site, ...}

            $table->unsignedInteger('ordem')->default(0);
            $table->enum('status', ['rascunho','publicado','arquivado'])->default('publicado')->index();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status','ordem']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('equipe_membros');
    }
};
