<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('theme_activity_logs')) {
            Schema::create('theme_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('theme_id')
                    ->nullable()
                    ->constrained('themes')
                    ->nullOnDelete();
                $table->string('action', 60);
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->string('user_name_snapshot')->nullable();
                $table->jsonb('metadata')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index('theme_id');
                $table->index('user_id');
                $table->index('action');
                $table->index('created_at');
            });

            return;
        }

        Schema::table('theme_activity_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('theme_activity_logs', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('action')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('theme_activity_logs', 'user_name_snapshot')) {
                $table->string('user_name_snapshot')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('theme_activity_logs', 'metadata')) {
                $table->jsonb('metadata')->nullable()->after('user_name_snapshot');
            }

            if (! Schema::hasColumn('theme_activity_logs', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('metadata');
            }
        });

        if (Schema::hasColumn('theme_activity_logs', 'user_id') && ! $this->hasConstraint('theme_activity_logs', 'theme_activity_logs_user_id_foreign')) {
            Schema::table('theme_activity_logs', function (Blueprint $table) {
                $table->foreign('user_id', 'theme_activity_logs_user_id_foreign')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        $this->ensureIndex('theme_activity_logs', 'theme_activity_logs_theme_id_index', ['theme_id']);
        $this->ensureIndex('theme_activity_logs', 'theme_activity_logs_user_id_index', ['user_id']);
        $this->ensureIndex('theme_activity_logs', 'theme_activity_logs_action_index', ['action']);
        $this->ensureIndex('theme_activity_logs', 'theme_activity_logs_created_at_index', ['created_at']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('theme_activity_logs')) {
            return;
        }

        $legacyTable = Schema::hasColumn('theme_activity_logs', 'performed_by')
            || Schema::hasColumn('theme_activity_logs', 'meta')
            || Schema::hasColumn('theme_activity_logs', 'updated_at');

        if ($legacyTable) {
            $this->dropIndexIfExists('theme_activity_logs', 'theme_activity_logs_created_at_index');
            $this->dropIndexIfExists('theme_activity_logs', 'theme_activity_logs_user_id_index');
            $this->dropIndexIfExists('theme_activity_logs', 'theme_activity_logs_theme_id_index');

            if ($this->hasConstraint('theme_activity_logs', 'theme_activity_logs_user_id_foreign')) {
                Schema::table('theme_activity_logs', function (Blueprint $table) {
                    $table->dropForeign('theme_activity_logs_user_id_foreign');
                });
            }

            Schema::table('theme_activity_logs', function (Blueprint $table) {
                if (Schema::hasColumn('theme_activity_logs', 'metadata')) {
                    $table->dropColumn('metadata');
                }

                if (Schema::hasColumn('theme_activity_logs', 'user_name_snapshot')) {
                    $table->dropColumn('user_name_snapshot');
                }

                if (Schema::hasColumn('theme_activity_logs', 'user_id')) {
                    $table->dropColumn('user_id');
                }
            });

            return;
        }

        Schema::dropIfExists('theme_activity_logs');
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
};
