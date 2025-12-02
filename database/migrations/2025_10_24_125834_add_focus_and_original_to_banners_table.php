<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            // foco (0–100 com 1-2 casas); em PG use decimal
            $table->decimal('pos_banner_x', 5, 2)->default(50)->after('status');
            $table->decimal('pos_banner_y', 5, 2)->default(50)->after('pos_banner_x');

            // caminhos de imagem
            $table->string('imagem_original_path')->nullable()->after('pos_banner_y');
            // se já existir imagem_path, deixe; se não existir, crie:
            if (!Schema::hasColumn('banners', 'imagem_path')) {
                $table->string('imagem_path')->nullable()->after('imagem_original_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners','pos_banner_x')) $table->dropColumn('pos_banner_x');
            if (Schema::hasColumn('banners','pos_banner_y')) $table->dropColumn('pos_banner_y');
            if (Schema::hasColumn('banners','imagem_original_path')) $table->dropColumn('imagem_original_path');
            // remova a linha abaixo somente se você criou imagem_path nesta migration
            // if (Schema::hasColumn('banners','imagem_path')) $table->dropColumn('imagem_path');
        });
    }
};
