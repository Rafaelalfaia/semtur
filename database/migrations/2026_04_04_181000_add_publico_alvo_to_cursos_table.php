<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->string('publico_alvo', 20)->default('ambos')->after('descricao_curta');
            $table->index('publico_alvo');
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropIndex(['publico_alvo']);
            $table->dropColumn('publico_alvo');
        });
    }
};
