<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('espaco_cultural_midias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('espaco_cultural_id')
                ->constrained('espacos_culturais')
                ->cascadeOnDelete();

            $table->string('path');
            $table->string('alt', 160)->nullable();
            $table->unsignedInteger('ordem')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['espaco_cultural_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('espaco_cultural_midias');
    }
};
