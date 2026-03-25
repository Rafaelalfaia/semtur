<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_fotos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->string('path');
            $table->string('alt', 160)->nullable();
            $table->unsignedInteger('ordem')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_fotos');
    }
};
