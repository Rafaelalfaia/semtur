<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key', 190)->unique();
            $table->string('group', 120)->nullable();
            $table->string('description')->nullable();
            $table->text('base_text');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['group', 'is_active'], 'translation_keys_group_active_idx');
        });

        Schema::create('translation_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_key_id')->constrained('translation_keys')->cascadeOnDelete();
            $table->foreignId('idioma_id')->constrained('idiomas')->cascadeOnDelete();
            $table->longText('text')->nullable();
            $table->timestamps();

            $table->unique(['translation_key_id', 'idioma_id'], 'translation_values_key_language_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_values');
        Schema::dropIfExists('translation_keys');
    }
};
