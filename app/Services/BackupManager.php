<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

class BackupManager
{
    public function localDiskName(): string
    {
        return 'backups_local';
    }

    public function remoteDiskName(): string
    {
        return (string) env('BACKUP_REMOTE_DISK', 'backups');
    }

    public function listLocalBackups(): array
    {
        return $this->listBackupsFromDisk($this->localDiskName());
    }

    public function listRemoteBackups(): array
    {
        if (! $this->remoteConfigured()) {
            return [];
        }

        try {
            return $this->listBackupsFromDisk($this->remoteDiskName());
        } catch (Throwable) {
            return [];
        }
    }

    public function remoteConfigured(): bool
    {
        return filled(env('BACKUP_AWS_ACCESS_KEY_ID'))
            && filled(env('BACKUP_AWS_SECRET_ACCESS_KEY'))
            && filled(env('BACKUP_AWS_BUCKET'))
            && filled(env('BACKUP_AWS_ENDPOINT'));
    }

    public function currentRemoteConfig(): array
    {
        return [
            'remote_enabled' => filter_var(env('BACKUP_REMOTE_ENABLED', false), FILTER_VALIDATE_BOOL),
            'disk' => $this->remoteDiskName(),
            'bucket' => (string) env('BACKUP_AWS_BUCKET', ''),
            'endpoint' => (string) env('BACKUP_AWS_ENDPOINT', ''),
            'region' => (string) env('BACKUP_AWS_DEFAULT_REGION', 'auto'),
            'url' => (string) env('BACKUP_AWS_URL', ''),
            'use_path_style' => filter_var(env('BACKUP_AWS_USE_PATH_STYLE_ENDPOINT', true), FILTER_VALIDATE_BOOL),
            'access_key_id' => (string) env('BACKUP_AWS_ACCESS_KEY_ID', ''),
            'secret_access_key' => (string) env('BACKUP_AWS_SECRET_ACCESS_KEY', ''),
            'keep_local_days' => (int) env('BACKUP_KEEP_LOCAL_DAYS', 7),
            'keep_remote_days' => (int) env('BACKUP_KEEP_REMOTE_DAYS', 30),
            'auto_enabled' => filter_var(env('BACKUP_AUTO_ENABLED', false), FILTER_VALIDATE_BOOL),
            'time' => (string) env('BACKUP_TIME', '02:30'),
        ];
    }

    public function remoteHealth(): array
    {
        if (! $this->remoteConfigured()) {
            return [
                'configured' => false,
                'reachable' => false,
                'count' => 0,
                'message' => 'Configuracao do R2 ainda incompleta.',
            ];
        }

        try {
            $disk = Storage::disk($this->remoteDiskName());
            $files = $disk->files('');
            $directories = $disk->directories('');

            return [
                'configured' => true,
                'reachable' => true,
                'count' => count($files) + count($directories),
                'message' => 'Conexao com o R2 valida para leitura e escrita.',
            ];
        } catch (Throwable $e) {
            return [
                'configured' => true,
                'reachable' => false,
                'count' => 0,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function generate(string $scope = 'all', bool $sendRemote = false): array
    {
        $params = ['--only' => $scope];
        if (! $sendRemote) {
            $params['--local-only'] = true;
        }

        $exitCode = Artisan::call('backup:run', $params);
        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            throw new RuntimeException($output !== '' ? $output : 'Falha ao gerar o backup.');
        }

        return [
            'output' => $output,
            'last_local' => $this->firstBackup($this->listLocalBackups()),
        ];
    }

    public function importUploadedPackage(UploadedFile $file): string
    {
        $name = now()->format('Y/m/d').'/manual-'.now()->format('His').'-'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'backup').'.zip';
        Storage::disk($this->localDiskName())->putFileAs(dirname($name), $file, basename($name));

        return $name;
    }

    public function pushLocalToRemote(string $path): void
    {
        $this->assertLocalPath($path);
        $stream = Storage::disk($this->localDiskName())->readStream($path);
        if (! is_resource($stream)) {
            throw new RuntimeException('Nao foi possivel abrir o backup local para envio.');
        }

        try {
            Storage::disk($this->remoteDiskName())->put($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    public function pullRemoteToLocal(string $path): string
    {
        $this->assertRemotePath($path);
        $stream = Storage::disk($this->remoteDiskName())->readStream($path);
        if (! is_resource($stream)) {
            throw new RuntimeException('Nao foi possivel abrir o backup remoto para importacao local.');
        }

        try {
            Storage::disk($this->localDiskName())->put($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $path;
    }

    public function deleteLocal(string $path): void
    {
        $this->assertLocalPath($path);
        Storage::disk($this->localDiskName())->delete($path);
    }

    public function deleteRemote(string $path): void
    {
        $this->assertRemotePath($path);
        Storage::disk($this->remoteDiskName())->delete($path);
    }

    public function localAbsolutePath(string $path): string
    {
        $this->assertLocalPath($path);
        return Storage::disk($this->localDiskName())->path($path);
    }

    public function inspect(string $scope, string $path): array
    {
        $diskName = $scope === 'remote' ? $this->remoteDiskName() : $this->localDiskName();

        if ($scope === 'remote') {
            $this->assertRemotePath($path);
        } else {
            $this->assertLocalPath($path);
        }

        $size = $this->safeSize($diskName, $path);

        return [
            'scope' => $scope,
            'path' => $path,
            'name' => basename($path),
            'size' => $size,
            'size_label' => $this->formatBytes($size),
            'modified_at' => $this->safeModifiedAt($diskName, $path),
            'manifest' => $this->readManifestFromDisk($diskName, $path),
        ];
    }

    public function testRemoteConnection(): array
    {
        $status = $this->remoteHealth();

        if (! $status['configured']) {
            throw new RuntimeException($status['message']);
        }

        if (! $status['reachable']) {
            throw new RuntimeException($status['message']);
        }

        return [
            'ok' => true,
            'count' => $status['count'],
            'bucket' => env('BACKUP_AWS_BUCKET'),
        ];
    }

    public function updateRemoteConfig(array $data): void
    {
        $map = [
            'BACKUP_REMOTE_ENABLED' => ! empty($data['remote_enabled']) ? 'true' : 'false',
            'BACKUP_REMOTE_DISK' => (string) ($data['remote_disk'] ?? 'backups'),
            'BACKUP_AWS_ACCESS_KEY_ID' => (string) ($data['access_key_id'] ?? ''),
            'BACKUP_AWS_SECRET_ACCESS_KEY' => (string) ($data['secret_access_key'] ?? ''),
            'BACKUP_AWS_DEFAULT_REGION' => (string) ($data['region'] ?? 'auto'),
            'BACKUP_AWS_BUCKET' => (string) ($data['bucket'] ?? ''),
            'BACKUP_AWS_ENDPOINT' => (string) ($data['endpoint'] ?? ''),
            'BACKUP_AWS_USE_PATH_STYLE_ENDPOINT' => ! empty($data['use_path_style']) ? 'true' : 'false',
            'BACKUP_AWS_URL' => (string) ($data['url'] ?? ''),
            'BACKUP_KEEP_LOCAL_DAYS' => (string) max(1, (int) ($data['keep_local_days'] ?? 7)),
            'BACKUP_KEEP_REMOTE_DAYS' => (string) max(1, (int) ($data['keep_remote_days'] ?? 30)),
            'BACKUP_AUTO_ENABLED' => ! empty($data['auto_enabled']) ? 'true' : 'false',
            'BACKUP_TIME' => (string) ($data['time'] ?? '02:30'),
        ];

        $this->writeEnvValues($map);
    }

    private function listBackupsFromDisk(string $diskName): array
    {
        $disk = Storage::disk($diskName);
        $items = [];

        foreach ($disk->allFiles() as $path) {
            if (! str_ends_with(Str::lower($path), '.zip')) {
                continue;
            }

            $size = $this->safeSize($diskName, $path);
            $items[] = [
                'path' => $path,
                'name' => basename($path),
                'directory' => str_replace('\\', '/', dirname($path)),
                'size' => $size,
                'size_label' => $this->formatBytes($size),
                'modified_at' => $this->safeModifiedAt($diskName, $path),
            ];
        }

        usort($items, fn ($a, $b) => strcmp((string) $b['modified_at'], (string) $a['modified_at']));

        return $items;
    }

    private function readManifestFromDisk(string $diskName, string $path): ?array
    {
        if (! class_exists(ZipArchive::class)) {
            return null;
        }

        $tempFile = null;

        try {
            if ($diskName === $this->localDiskName()) {
                $zipPath = Storage::disk($diskName)->path($path);
            } else {
                $stream = Storage::disk($diskName)->readStream($path);
                if (! is_resource($stream)) {
                    return null;
                }

                File::ensureDirectoryExists(storage_path('app/tmp'));
                $tempFile = tempnam(storage_path('app/tmp'), 'backup-manifest-');
                $target = fopen($tempFile, 'wb');
                if (! is_resource($target)) {
                    fclose($stream);
                    return null;
                }

                stream_copy_to_stream($stream, $target);
                fclose($stream);
                fclose($target);
                $zipPath = $tempFile;
            }

            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                return null;
            }

            $manifest = $zip->getFromName('manifest.json');
            if (! is_string($manifest) || trim($manifest) === '') {
                for ($index = 0; $index < $zip->numFiles; $index++) {
                    $entryName = $zip->getNameIndex($index);
                    if (is_string($entryName) && str_ends_with(str_replace('\\', '/', $entryName), '/manifest.json')) {
                        $manifest = $zip->getFromIndex($index);
                        break;
                    }
                }
            }
            $zip->close();

            if (! is_string($manifest) || trim($manifest) === '') {
                return null;
            }

            $decoded = json_decode($manifest, true);
            return is_array($decoded) ? $decoded : null;
        } catch (Throwable) {
            return null;
        } finally {
            if ($tempFile && File::exists($tempFile)) {
                File::delete($tempFile);
            }
        }
    }

    private function safeSize(string $diskName, string $path): int
    {
        try {
            return (int) Storage::disk($diskName)->size($path);
        } catch (Throwable) {
            return 0;
        }
    }

    private function safeModifiedAt(string $diskName, string $path): ?string
    {
        try {
            return date('Y-m-d H:i:s', (int) Storage::disk($diskName)->lastModified($path));
        } catch (Throwable) {
            return null;
        }
    }

    private function firstBackup(array $items): ?array
    {
        return $items[0] ?? null;
    }

    private function assertLocalPath(string $path): void
    {
        $path = trim($path);
        if ($path === '' || ! Storage::disk($this->localDiskName())->exists($path)) {
            throw new RuntimeException('Backup local nao encontrado.');
        }
    }

    private function assertRemotePath(string $path): void
    {
        $path = trim($path);
        if ($path === '' || ! Storage::disk($this->remoteDiskName())->exists($path)) {
            throw new RuntimeException('Backup remoto nao encontrado.');
        }
    }

    private function writeEnvValues(array $values): void
    {
        $envPath = base_path('.env');
        $content = File::exists($envPath) ? File::get($envPath) : '';

        foreach ($values as $key => $value) {
            $escaped = $this->quoteEnvValue($value);
            $pattern = "/^".preg_quote($key, '/')."=.*/m";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $key.'='.$escaped, $content, 1);
            } else {
                $content = rtrim($content).PHP_EOL.$key.'='.$escaped.PHP_EOL;
            }
        }

        File::put($envPath, $content);
        Artisan::call('config:clear');
    }

    private function quoteEnvValue(string $value): string
    {
        if ($value === '' || preg_match('/\s/', $value)) {
            return '"'.str_replace('"', '\\"', str_replace('\\', '/', $value)).'"';
        }

        return $value;
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
