<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 160);
            $table->string('slug', 180)->unique();
            $table->text('descricao')->nullable();
            $table->string('telefone', 40)->nullable();
            $table->string('email', 160)->nullable();
            $table->string('site_url')->nullable();
            $table->string('maps_url')->nullable();
            $table->string('endereco')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->decimal('lat', 10, 7)->nullable()->index();
            $table->decimal('lng', 10, 7)->nullable()->index();
            $table->string('foto_perfil_path')->nullable();
            $table->string('foto_capa_path')->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->enum('status', ['rascunho','publicado','arquivado'])->default('rascunho')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status','ordem']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('empresas');
    }
};
