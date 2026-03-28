<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

class BackupRunCommand extends Command
{
    protected $signature = 'backup:run
        {--only=all : all, database ou storage}
        {--local-only : Gera somente o pacote local, sem envio remoto}
        {--no-prune : Nao aplica a retencao automatica ao final}';

    protected $description = 'Gera backup do banco e/ou storage publico com envio opcional ao disco remoto de backup';

    public function handle(): int
    {
        $only = strtolower((string) $this->option('only'));
        if (! in_array($only, ['all', 'database', 'storage'], true)) {
            $this->error('Opcao --only invalida. Use all, database ou storage.');
            return self::FAILURE;
        }

        if (! class_exists(ZipArchive::class)) {
            $this->error('A extensao zip do PHP nao esta disponivel neste ambiente.');
            return self::FAILURE;
        }

        $includeDatabase = $only === 'all' || $only === 'database';
        $includeStorage = $only === 'all' || $only === 'storage';
        $localOnly = (bool) $this->option('local-only');
        $remoteEnabled = $this->remoteEnabled() && ! $localOnly;
        $stamp = now()->format('Ymd_His');
        $slug = Str::slug((string) env('BACKUP_PREFIX', config('app.name', 'backup')) ?: 'backup');
        $tmpDir = storage_path('app/backups/tmp/'.$stamp.'-'.Str::lower(Str::random(6)));
        $localRelativePath = now()->format('Y/m/d').'/'.$slug.'-'.$stamp.'.zip';
        $localAbsolutePath = storage_path('app/backups/'.$localRelativePath);

        File::ensureDirectoryExists($tmpDir);
        File::ensureDirectoryExists(dirname($localAbsolutePath));

        $manifest = [
            'created_at' => now()->toIso8601String(),
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'url' => config('app.url'),
            ],
            'contents' => [
                'database' => null,
                'storage' => null,
            ],
        ];

        try {
            if ($includeDatabase) {
                $manifest['contents']['database'] = $this->createDatabaseDump($tmpDir);
            }

            if ($includeStorage) {
                $manifest['contents']['storage'] = $this->copyPublicStorage($tmpDir);
            }

            File::put($tmpDir.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            $this->createZipFromDirectory($tmpDir, $localAbsolutePath);
            $localSize = File::size($localAbsolutePath);
            $this->info('Pacote local criado em: '.$localRelativePath);
            $this->line('Tamanho local: '.$this->formatBytes($localSize));

            if ($remoteEnabled) {
                $remoteDisk = env('BACKUP_REMOTE_DISK', 'backups');
                $stream = fopen($localAbsolutePath, 'rb');
                if ($stream === false) {
                    throw new RuntimeException('Nao foi possivel abrir o pacote local para envio remoto.');
                }

                try {
                    Storage::disk($remoteDisk)->put($localRelativePath, $stream);
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }

                $this->info('Pacote enviado para o disco remoto: '.$remoteDisk.' -> '.$localRelativePath);
            } else {
                $this->comment('Envio remoto desativado. O backup ficou salvo apenas localmente.');
            }

            if (! (bool) $this->option('no-prune')) {
                $this->pruneLocalBackups();
                if ($remoteEnabled) {
                    $this->pruneRemoteBackups((string) env('BACKUP_REMOTE_DISK', 'backups'));
                }
            }
        } catch (Throwable $e) {
            File::delete($localAbsolutePath);
            $this->error($e->getMessage());
            return self::FAILURE;
        } finally {
            File::deleteDirectory($tmpDir);
        }

        return self::SUCCESS;
    }

    private function createDatabaseDump(string $tmpDir): array
    {
        $connectionName = (string) (env('BACKUP_DB_CONNECTION') ?: Config::get('database.default'));
        $connection = Config::get("database.connections.$connectionName");

        if (! is_array($connection)) {
            throw new RuntimeException("Conexao de banco [$connectionName] nao encontrada.");
        }

        $driver = (string) ($connection['driver'] ?? '');
        $databaseDir = $tmpDir.'/database';
        File::ensureDirectoryExists($databaseDir);

        return match ($driver) {
            'sqlite' => $this->backupSqliteDatabase($connection, $databaseDir),
            'mysql', 'mariadb' => $this->backupMysqlDatabase($connection, $databaseDir),
            'pgsql' => $this->backupPgsqlDatabase($connection, $databaseDir),
            default => throw new RuntimeException("Driver [$driver] ainda nao suportado pelo backup automatico."),
        };
    }

    private function backupSqliteDatabase(array $connection, string $databaseDir): array
    {
        $database = (string) ($connection['database'] ?? '');
        $source = $database === ':memory:' ? null : $database;
        if ($source && ! str_contains($source, DIRECTORY_SEPARATOR)) {
            $source = database_path($source);
        }

        if (! $source || ! File::exists($source)) {
            throw new RuntimeException('Arquivo sqlite nao encontrado para backup.');
        }

        $target = $databaseDir.'/database.sqlite';
        File::copy($source, $target);

        return [
            'driver' => 'sqlite',
            'file' => 'database/database.sqlite',
            'size' => File::size($target),
        ];
    }

    private function backupMysqlDatabase(array $connection, string $databaseDir): array
    {
        $binary = (string) env('BACKUP_MYSQLDUMP_BINARY', 'mysqldump');
        $target = $databaseDir.'/database.sql';
        $database = (string) ($connection['database'] ?? '');
        if ($database === '') {
            throw new RuntimeException('Banco mysql/mariadb nao configurado para backup.');
        }

        $parts = [
            $binary,
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--host='.$this->stringConfigValue($connection['host'] ?? '127.0.0.1', '127.0.0.1'),
            '--port='.$this->stringConfigValue($connection['port'] ?? '3306', '3306'),
            '--user='.$this->stringConfigValue($connection['username'] ?? '', ''),
            '--result-file='.$target,
            $database,
        ];

        $env = [];
        $password = (string) ($connection['password'] ?? '');
        if ($password !== '') {
            $env['MYSQL_PWD'] = $password;
        }

        $result = $this->runProcess($parts, $env);
        if ($result['exit_code'] !== 0 || ! File::exists($target)) {
            throw new RuntimeException('Falha ao executar mysqldump. Verifique BACKUP_MYSQLDUMP_BINARY e acesso ao banco. '.$result['stderr']);
        }

        return [
            'driver' => 'mysql',
            'file' => 'database/database.sql',
            'size' => File::size($target),
        ];
    }

    private function backupPgsqlDatabase(array $connection, string $databaseDir): array
    {
        $binary = (string) env('BACKUP_PG_DUMP_BINARY', 'pg_dump');
        $target = $databaseDir.'/database.sql';
        $database = (string) ($connection['database'] ?? '');
        if ($database === '') {
            throw new RuntimeException('Banco pgsql nao configurado para backup.');
        }

        $parts = [
            $binary,
            '--file='.$target,
            '--host='.$this->stringConfigValue($connection['host'] ?? '127.0.0.1', '127.0.0.1'),
            '--port='.$this->stringConfigValue($connection['port'] ?? '5432', '5432'),
            '--username='.$this->stringConfigValue($connection['username'] ?? '', ''),
            $database,
        ];

        $env = [];
        $password = (string) ($connection['password'] ?? '');
        if ($password !== '') {
            $env['PGPASSWORD'] = $password;
        }

        $result = $this->runProcess($parts, $env);
        if ($result['exit_code'] !== 0 || ! File::exists($target)) {
            throw new RuntimeException('Falha ao executar pg_dump. Verifique BACKUP_PG_DUMP_BINARY e acesso ao banco. '.$result['stderr']);
        }

        return [
            'driver' => 'pgsql',
            'file' => 'database/database.sql',
            'size' => File::size($target),
        ];
    }

    private function copyPublicStorage(string $tmpDir): array
    {
        $source = storage_path('app/public');
        if (! File::exists($source)) {
            throw new RuntimeException('Pasta storage/app/public nao encontrada para backup.');
        }

        $target = $tmpDir.'/storage/app/public';
        File::ensureDirectoryExists(dirname($target));
        File::copyDirectory($source, $target);

        $files = File::allFiles($target);
        $size = collect($files)->sum(fn ($file) => $file->getSize());

        return [
            'path' => 'storage/app/public',
            'files' => count($files),
            'size' => $size,
        ];
    }

    private function createZipFromDirectory(string $sourceDir, string $zipPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Nao foi possivel criar o arquivo zip do backup.');
        }

        $sourceDir = rtrim(str_replace('\\', DIRECTORY_SEPARATOR, $sourceDir), DIRECTORY_SEPARATOR);
        foreach (File::allFiles($sourceDir) as $file) {
            $absolutePath = str_replace('\\', DIRECTORY_SEPARATOR, $file->getRealPath());
            $relativePath = ltrim(str_replace('\\', '/', substr($absolutePath, strlen($sourceDir))), '/');
            $zip->addFile($absolutePath, $relativePath);
        }

        $zip->close();
    }

    private function runProcess(array $parts, array $extraEnv = []): array
    {
        $parts = array_values(array_filter($parts, fn ($part) => $part !== null && $part !== ''));
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        if (PHP_OS_FAMILY === 'Windows') {
            $command = [
                'powershell',
                '-NoProfile',
                '-NonInteractive',
                '-Command',
                $this->buildWindowsPowerShellCommand($parts, $extraEnv),
            ];
            $process = proc_open($command, $descriptors, $pipes, base_path());
        } else {
            $command = implode(' ', array_map([$this, 'escapeProcessArgument'], $parts));
            $process = proc_open($command, $descriptors, $pipes, base_path(), $extraEnv ?: null);
        }

        if (! is_resource($process)) {
            throw new RuntimeException('Nao foi possivel iniciar o processo externo de backup.');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]) ?: '';
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return [
            'exit_code' => $exitCode,
            'stdout' => trim($stdout),
            'stderr' => trim($stderr),
        ];
    }

    private function buildWindowsPowerShellCommand(array $parts, array $extraEnv): string
    {
        $commands = [];
        foreach ($extraEnv as $key => $value) {
            $commands[] = '$env:'.$key.'='.$this->quotePowerShellString((string) $value).';';
        }

        $command = '& '.$this->quotePowerShellString((string) array_shift($parts));
        foreach ($parts as $part) {
            $command .= ' '.$this->quotePowerShellString((string) $part);
        }

        $commands[] = $command;

        return implode(' ', $commands);
    }

    private function quotePowerShellString(string $value): string
    {
        return "'".str_replace("'", "''", $value)."'";
    }

    private function escapeProcessArgument(string $value): string
    {
        return escapeshellarg($value);
    }

    private function stringConfigValue(mixed $value, string $fallback): string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        if ($value === null || $value === '') {
            return $fallback;
        }

        return (string) $value;
    }

    private function remoteEnabled(): bool
    {
        return filter_var(env('BACKUP_REMOTE_ENABLED', false), FILTER_VALIDATE_BOOL);
    }

    private function pruneLocalBackups(): void
    {
        $days = max(1, (int) env('BACKUP_KEEP_LOCAL_DAYS', 7));
        $cutoff = now()->subDays($days)->getTimestamp();
        $disk = Storage::disk('backups_local');

        foreach ($disk->allFiles() as $path) {
            if (str_starts_with($path, 'tmp/')) {
                continue;
            }

            if ($disk->lastModified($path) < $cutoff) {
                $disk->delete($path);
            }
        }
    }

    private function pruneRemoteBackups(string $diskName): void
    {
        $days = max(1, (int) env('BACKUP_KEEP_REMOTE_DAYS', 30));
        $cutoff = now()->subDays($days)->getTimestamp();
        $disk = Storage::disk($diskName);

        foreach ($disk->allFiles() as $path) {
            if ($disk->lastModified($path) < $cutoff) {
                $disk->delete($path);
            }
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = $bytes / 1024;
        foreach ($units as $unit) {
            if ($value < 1024 || $unit === 'TB') {
                return number_format($value, 2, '.', '').' '.$unit;
            }
            $value /= 1024;
        }

        return $bytes.' B';
    }
}
