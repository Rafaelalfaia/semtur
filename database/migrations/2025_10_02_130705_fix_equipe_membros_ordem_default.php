<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Normaliza registros já salvos
        DB::table('equipe_membros')->whereNull('ordem')->update(['ordem' => 0]);

        // 2) Garante default 0 na coluna (e mantém NOT NULL)
        Schema::table('equipe_membros', function (Blueprint $t) {
            $t->unsignedInteger('ordem')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('equipe_membros', function (Blueprint $t) {
            $t->unsignedInteger('ordem')->default(0)->change(); // deixa como estava
        });
    }
};
