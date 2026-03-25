<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->string('type', 80)->nullable()->after('base_theme');
            $table->string('preview_image_path')->nullable()->after('description');
            $table->timestamp('starts_at')->nullable()->after('preview_image_path');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
            $table->boolean('is_default')->default(false)->after('ends_at');
            $table->json('config_json')->nullable()->after('assets');
        });
    }

    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'preview_image_path',
                'starts_at',
                'ends_at',
                'is_default',
                'config_json',
            ]);
        });
    }
};
