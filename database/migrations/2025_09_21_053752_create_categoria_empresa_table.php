<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categoria_empresa', function (Blueprint $table) {
            $table->foreignId('categoria_id')->constrained()->cascadeOnDelete();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->primary(['categoria_id', 'empresa_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('categoria_empresa');
    }
};
