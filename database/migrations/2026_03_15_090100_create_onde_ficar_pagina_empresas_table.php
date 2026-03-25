<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('onde_ficar_pagina_empresas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('onde_ficar_pagina_id')
                ->constrained('onde_ficar_paginas')
                ->cascadeOnDelete();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('destaque')->default(false);
            $table->string('observacao_curta', 255)->nullable();

            $table->timestamps();

            $table->unique(
                ['onde_ficar_pagina_id', 'empresa_id'],
                'onde_ficar_pagina_empresa_unique'
            );

            $table->index(
                ['onde_ficar_pagina_id', 'ordem'],
                'onde_ficar_pagina_ordem_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onde_ficar_pagina_empresas');
    }
};
