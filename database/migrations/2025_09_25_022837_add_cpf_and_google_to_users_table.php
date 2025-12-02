<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            // Opção A (somente dígitos, recomendado):
            $t->char('cpf', 11)->nullable()->unique()->after('email');

            // E-mail opcional
            DB::statement('ALTER TABLE users ALTER COLUMN email DROP NOT NULL');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['cpf']);
        });


    }
};
