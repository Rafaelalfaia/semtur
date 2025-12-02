<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            // CTA (botão opcional)
            $table->string('cta_label', 80)->nullable()->after('subtitulo');
            $table->string('cta_url')->nullable()->after('cta_label');

            // Quem criou (opcional)
            $table->foreignId('created_by')
                ->nullable()
                ->after('published_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            // remover FK antes da coluna no Postgres
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['cta_label','cta_url']);
        });
    }
};
