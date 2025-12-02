<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('secretaria', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 160);
            $table->string('slug', 180)->unique();
            $table->text('descricao')->nullable();

            $table->json('redes')->nullable(); // {instagram, facebook, site, whatsapp, ...}

            // Localização (via Google Maps)
            $table->string('maps_url')->nullable();
            $table->string('endereco')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->decimal('lat', 10, 7)->nullable()->index();
            $table->decimal('lng', 10, 7)->nullable()->index();

            // Mídias institucionais
            $table->string('foto_path')->nullable();
            $table->string('foto_capa_path')->nullable();

            $table->unsignedInteger('ordem')->default(0);
            $table->enum('status', ['rascunho','publicado','arquivado'])->default('publicado')->index();
            $table->timestamp('published_at')->nullable()->index();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status','ordem']);
        });

        // Opcional: garantir “singleton” (máx. 1 linha) com índice parcial (Postgres).
        // Em MySQL, você pode ignorar isso (ou controlar na aplicação).
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS secretaria_singleton_idx ON secretaria ((true)) WHERE deleted_at IS NULL;");
        }
    }

    public function down(): void {
        Schema::dropIfExists('secretaria');
    }
};
