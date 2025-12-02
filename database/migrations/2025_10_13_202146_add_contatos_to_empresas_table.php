<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('empresas', function (Blueprint $table) {
      if (!Schema::hasColumn('empresas','contatos')) {
        $table->json('contatos')->nullable()->after('email');
      }
    });
  }

  public function down(): void {
    Schema::table('empresas', function (Blueprint $table) {
      if (Schema::hasColumn('empresas','contatos')) {
        $table->dropColumn('contatos');
      }
    });
  }
};
