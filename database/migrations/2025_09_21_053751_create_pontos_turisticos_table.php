<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pontos_turisticos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 160);
            $table->string('slug', 180)->unique();
            $table->text('descricao')->nullable();
            $table->string('capa_path')->nullable();
            $table->string('maps_url')->nullable();
            $table->string('endereco')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->decimal('lat', 10, 7)->nullable()->index();
            $table->decimal('lng', 10, 7)->nullable()->index();
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
        Schema::dropIfExists('pontos_turisticos');
    }
};
