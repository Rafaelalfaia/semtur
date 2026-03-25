<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banner_destaques', function (Blueprint $table) {
            $table->string('media_type', 20)->default('image')->after('target_blank');
            $table->string('video_desktop_path')->nullable()->after('imagem_desktop_path');
            $table->string('video_mobile_path')->nullable()->after('video_desktop_path');
            $table->string('poster_desktop_path')->nullable()->after('video_mobile_path');
            $table->string('poster_mobile_path')->nullable()->after('poster_desktop_path');
            $table->string('fallback_image_desktop_path')->nullable()->after('poster_mobile_path');
            $table->string('fallback_image_mobile_path')->nullable()->after('fallback_image_desktop_path');
            $table->boolean('autoplay')->default(true)->after('overlay_opacity');
            $table->boolean('loop')->default(true)->after('autoplay');
            $table->boolean('muted')->default(true)->after('loop');
            $table->string('hero_variant', 40)->nullable()->after('muted');
            $table->string('preload_mode', 20)->nullable()->after('hero_variant');
            $table->string('alt_text', 255)->nullable()->after('preload_mode');
        });
    }

    public function down(): void
    {
        Schema::table('banner_destaques', function (Blueprint $table) {
            $table->dropColumn([
                'media_type',
                'video_desktop_path',
                'video_mobile_path',
                'poster_desktop_path',
                'poster_mobile_path',
                'fallback_image_desktop_path',
                'fallback_image_mobile_path',
                'autoplay',
                'loop',
                'muted',
                'hero_variant',
                'preload_mode',
                'alt_text',
            ]);
        });
    }
};
