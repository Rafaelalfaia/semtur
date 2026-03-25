<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('themes')) {
            return;
        }

        if (! Schema::hasColumn('themes', 'created_by')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('config_json')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        } elseif (! $this->hasConstraint('themes', 'themes_created_by_foreign')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->foreign('created_by', 'themes_created_by_foreign')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('themes', 'updated_by')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->foreignId('updated_by')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        } elseif (! $this->hasConstraint('themes', 'themes_updated_by_foreign')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->foreign('updated_by', 'themes_updated_by_foreign')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        $this->ensureIndex('themes', 'themes_status_index', ['status']);
        $this->ensureIndex('themes', 'themes_is_default_index', ['is_default']);
        $this->ensureIndex('themes', 'themes_starts_at_index', ['starts_at']);
        $this->ensureIndex('themes', 'themes_ends_at_index', ['ends_at']);
        $this->ensureIndex('themes', 'themes_created_by_index', ['created_by']);
        $this->ensureIndex('themes', 'themes_updated_by_index', ['updated_by']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('themes')) {
            return;
        }

        $this->dropIndexIfExists('themes', 'themes_updated_by_index');
        $this->dropIndexIfExists('themes', 'themes_created_by_index');
        $this->dropIndexIfExists('themes', 'themes_ends_at_index');
        $this->dropIndexIfExists('themes', 'themes_starts_at_index');
        $this->dropIndexIfExists('themes', 'themes_is_default_index');

        // Rollback conservador: nÃ£o remove colunas preexistentes em bancos restaurados.
        if ($this->hasConstraint('themes', 'themes_updated_by_foreign') && ! $this->columnHadForeignKeyBefore('themes', 'updated_by')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->dropForeign('themes_updated_by_foreign');
            });
        }

        if ($this->hasConstraint('themes', 'themes_created_by_foreign') && ! $this->columnHadForeignKeyBefore('themes', 'created_by')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->dropForeign('themes_created_by_foreign');
            });
        }
    }

    private function ensureIndex(string $table, string $indexName, array $columns): void
    {
        if ($this->hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! $this->hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropIndex($indexName);
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        return DB::table('pg_indexes')
            ->where('schemaname', 'public')
            ->where('tablename', $table)
            ->where('indexname', $indexName)
            ->exists();
    }

    private function hasConstraint(string $table, string $constraintName): bool
    {
        return DB::table('pg_constraint')
            ->whereRaw('conrelid = ?::regclass', ["public.{$table}"])
            ->where('conname', $constraintName)
            ->exists();
    }

    private function columnHadForeignKeyBefore(string $table, string $column): bool
    {
        // Em schema legado restaurado, a foreign key jÃ¡ existe antes desta migration.
        // Quando a coluna jÃ¡ existe, o rollback deve ser nÃ£o destrutivo.
        return Schema::hasColumn($table, $column);
    }
};
