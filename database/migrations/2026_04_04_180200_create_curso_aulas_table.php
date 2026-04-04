<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_aulas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('modulo_id')->constrained('curso_modulos')->cascadeOnDelete();

            $table->string('nome', 180);
            $table->string('slug', 200)->unique();
            $table->string('capa_path')->nullable();
            $table->longText('descricao')->nullable();
            $table->text('link_acesso');

            $table->unsignedInteger('ordem')->default(0);

            $table->string('status', 20)->default('rascunho')->index();
            $table->timestamp('published_at')->nullable()->index();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['modulo_id', 'status', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_aulas');
    }
};
