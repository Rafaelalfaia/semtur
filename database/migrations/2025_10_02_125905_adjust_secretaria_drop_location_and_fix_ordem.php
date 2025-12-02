<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Prevenir violação de NOT NULL: preenche NULLs com 0
        DB::table('secretaria')->whereNull('ordem')->update(['ordem' => 0]);

        Schema::table('secretaria', function (Blueprint $t) {
            // 2) Garante default 0 (e mantém NOT NULL)
            $t->unsignedInteger('ordem')->default(0)->change();

            // 3) Remover campos de localização detalhada
            if (Schema::hasColumn('secretaria','endereco')) $t->dropColumn('endereco');
            if (Schema::hasColumn('secretaria','bairro'))   $t->dropColumn('bairro');
            if (Schema::hasColumn('secretaria','cidade'))   $t->dropColumn('cidade');
            if (Schema::hasColumn('secretaria','lat'))      $t->dropColumn('lat');
            if (Schema::hasColumn('secretaria','lng'))      $t->dropColumn('lng');
        });

        // (opcional) Se em algum momento foi criado um campo com acento "descrição",
        // normaliza para "descricao".
        if (Schema::hasColumn('secretaria','descrição') && !Schema::hasColumn('secretaria','descricao')) {
            DB::statement('ALTER TABLE secretaria RENAME COLUMN "descrição" TO descricao;');
        }
    }

    public function down(): void
    {
        Schema::table('secretaria', function (Blueprint $t) {
            // Recria os campos (se quiser rollback)
            $t->string('endereco')->nullable();
            $t->string('bairro')->nullable();
            $t->string('cidade')->nullable();
            $t->decimal('lat', 10, 7)->nullable()->index();
            $t->decimal('lng', 10, 7)->nullable()->index();
            $t->unsignedInteger('ordem')->default(0)->change();
        });
        // não renomeamos "descricao" de volta para "descrição" no down para evitar problemas
    }
};
