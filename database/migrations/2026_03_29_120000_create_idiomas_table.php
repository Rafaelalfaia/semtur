<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idiomas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 12)->unique();
            $table->string('nome', 120);
            $table->string('sigla', 12);
            $table->string('bandeira')->nullable();
            $table->string('html_lang', 20)->nullable();
            $table->string('hreflang', 20)->nullable();
            $table->string('og_locale', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'nome'], 'idiomas_active_nome_idx');
        });

        DB::table('idiomas')->insert([
            [
                'codigo' => 'pt',
                'nome' => 'Português',
                'sigla' => 'PT',
                'bandeira' => 'icons/pt.png',
                'html_lang' => 'pt-BR',
                'hreflang' => 'pt-BR',
                'og_locale' => 'pt_BR',
                'is_active' => true,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'en',
                'nome' => 'English',
                'sigla' => 'EN',
                'bandeira' => 'icons/us.webp',
                'html_lang' => 'en-US',
                'hreflang' => 'en',
                'og_locale' => 'en_US',
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'es',
                'nome' => 'Español',
                'sigla' => 'ES',
                'bandeira' => 'icons/es.png',
                'html_lang' => 'es-ES',
                'hreflang' => 'es',
                'og_locale' => 'es_ES',
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('idiomas');
    }
};
