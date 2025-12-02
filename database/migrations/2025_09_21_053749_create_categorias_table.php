<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120);
            $table->string('slug', 140)->unique();
            $table->text('descricao')->nullable();
            $table->string('icone_path')->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->enum('status', ['rascunho','publicado','arquivado'])->default('rascunho')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void {
        Schema::dropIfExists('categorias');
    }
};
