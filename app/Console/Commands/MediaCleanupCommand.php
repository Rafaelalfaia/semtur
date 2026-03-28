<?php

namespace App\Console\Commands;

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Empresa;
use App\Models\Catalogo\EmpresaFoto;
use App\Models\Catalogo\EspacoCultural;
use App\Models\Catalogo\EspacoCulturalMidia;
use App\Models\Catalogo\PontoMidia;
use App\Models\Catalogo\PontoTuristico;
use App\Models\Catalogo\Roteiro;
use App\Models\Conteudo\Aviso;
use App\Models\Conteudo\Banner;
use App\Models\Conteudo\BannerDestaque;
use App\Models\Conteudo\GuiaRevista;
use App\Models\Conteudo\OndeComerPagina;
use App\Models\Conteudo\OndeFicarPagina;
use App\Models\Conteudo\Video;
use App\Models\EquipeMembro;
use App\Models\Evento;
use App\Models\EventoAtrativo;
use App\Models\EventoMidia;
use App\Models\JogosIndigenas;
use App\Models\JogosIndigenasEdicao;
use App\Models\JogosIndigenasEdicaoFoto;
use App\Models\JogosIndigenasEdicaoPatrocinador;
use App\Models\RotaDoCacau;
use App\Models\RotaDoCacauEdicao;
use App\Models\RotaDoCacauEdicaoFoto;
use App\Models\RotaDoCacauEdicaoPatrocinador;
use App\Models\Secretaria;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class MediaCleanupCommand extends Command
{
    protected $signature = 'media:cleanup
        {--quarantine : Move orphaned files to quarantine-media}
        {--purge : Delete old quarantine batches}
        {--days=15 : Minimum age in days for purge and safe maintenance pruning}
        {--report=media-cleanup/latest.json : Relative path in storage/app for the JSON report}
        {--prune-temp : Delete old files and directories from storage/app/tmp}
        {--prune-optimized : Delete old derived image cache from public/optimized/site}
        {--prune-reports : Delete old media-cleanup reports while keeping latest.json}';

    protected $description = 'Audit orphaned public media, move to quarantine safely, purge old quarantine batches, and prune regenerable temporary files';

    public function handle(): int
    {
        $days = max((int) $this->option('days'), 1);
        $disk = Storage::disk('public');
        $report = [
            'generated_at' => now()->toIso8601String(),
            'days' => $days,
            'quarantine' => (bool) $this->option('quarantine'),
            'purge' => (bool) $this->option('purge'),
            'prune_temp' => (bool) $this->option('prune-temp'),
            'prune_optimized' => (bool) $this->option('prune-optimized'),
            'prune_reports' => (bool) $this->option('prune-reports'),
            'summary' => [],
            'orphans' => [],
            'quarantined' => [],
            'purged' => [],
            'maintenance' => [
                'temp_deleted' => [],
                'optimized_deleted' => [],
                'reports_deleted' => [],
            ],
        ];

        $referenced = $this->collectReferencedPaths();
        $files = collect($disk->allFiles())
            ->map(fn (string $path) => $this->normalizePath($path))
            ->reject(fn (?string $path) => ! $path || $this->isIgnoredDiskPath($path))
            ->values();

        $orphans = $files
            ->reject(fn (string $path) => array_key_exists($path, $referenced))
            ->values();

        $report['summary'] = [
            'disk_files' => $files->count(),
            'referenced_files' => count($referenced),
            'orphan_files' => $orphans->count(),
        ];
        $report['orphans'] = $orphans->all();

        $this->info('Files in storage/app/public: '.$files->count());
        $this->info('Referenced files: '.count($referenced));
        $this->warn('Orphaned file candidates: '.$orphans->count());

        if ($orphans->isNotEmpty()) {
            $this->line('Sample orphaned files:');
            foreach ($orphans->take(10) as $path) {
                $this->line(' - '.$path);
            }
        }

        if ($this->option('quarantine') && $orphans->isNotEmpty()) {
            $report['quarantined'] = $this->quarantineOrphans($disk, $orphans);
            $this->info('Moved to quarantine: '.count($report['quarantined']));
        }

        if ($this->option('purge')) {
            $report['purged'] = $this->purgeOldQuarantineBatches($disk, $days);
            $this->info('Quarantine batches deleted: '.count($report['purged']));
        }

        if ($this->option('prune-temp')) {
            $report['maintenance']['temp_deleted'] = $this->pruneStorageTmp($days);
            $this->info('Temporary files/directories deleted: '.count($report['maintenance']['temp_deleted']));
        }

        if ($this->option('prune-optimized')) {
            $report['maintenance']['optimized_deleted'] = $this->pruneOptimizedCache($days);
            $this->info('Optimized derivative files deleted: '.count($report['maintenance']['optimized_deleted']));
        }

        if ($this->option('prune-reports')) {
            $report['maintenance']['reports_deleted'] = $this->pruneOldReports($days);
            $this->info('Old reports deleted: '.count($report['maintenance']['reports_deleted']));
        }

        $reportPath = $this->writeReport($report);

        if ($this->isAuditOnly()) {
            $this->comment('Safe mode: audit only. Nothing was moved or deleted.');
        }

        if ($reportPath) {
            $this->line('Report saved to: '.$reportPath);
        }

        return self::SUCCESS;
    }

    private function isAuditOnly(): bool
    {
        return ! $this->option('quarantine')
            && ! $this->option('purge')
            && ! $this->option('prune-temp')
            && ! $this->option('prune-optimized')
            && ! $this->option('prune-reports');
    }

    private function quarantineOrphans($disk, $orphans): array
    {
        $batch = 'quarantine-media/'.now()->format('Ymd-His');
        $moved = [];

        foreach ($orphans as $path) {
            $target = $this->uniqueQuarantineTarget($disk, $batch, $path);
            $disk->makeDirectory(dirname($target));

            if ($disk->move($path, $target)) {
                $moved[] = [
                    'from' => $path,
                    'to' => $target,
                ];
            }
        }

        return $moved;
    }

    private function collectReferencedPaths(): array
    {
        $references = [];

        foreach ($this->referenceSources() as $source) {
            $modelClass = $source['model'];
            $query = $this->queryFor($modelClass);

            $query->chunkById(100, function ($records) use (&$references, $source, $modelClass) {
                foreach ($records as $record) {
                    $paths = [];

                    foreach ($source['fields'] ?? [] as $field) {
                        $paths[$field] = $record->{$field} ?? null;
                    }

                    if (isset($source['resolver']) && is_callable($source['resolver'])) {
                        foreach (($source['resolver'])($record) as $field => $value) {
                            $paths[$field] = $value;
                        }
                    }

                    foreach ($paths as $field => $value) {
                        foreach ($this->extractPathValues($value) as $path) {
                            $references[$path] ??= [];
                            $references[$path][] = class_basename($modelClass).'#'.$record->getKey().':'.$field;
                        }
                    }
                }
            });
        }

        return $references;
    }

    private function referenceSources(): array
    {
        return [
            ['model' => Theme::class, 'fields' => ['preview_image_path'], 'resolver' => fn (Theme $theme) => ['assets' => array_values($theme->persistedAssets())]],
            ['model' => User::class, 'fields' => ['avatar_url']],
            ['model' => Secretaria::class, 'fields' => ['foto_path', 'foto_capa_path']],
            ['model' => EquipeMembro::class, 'fields' => ['foto_path']],
            ['model' => Evento::class, 'fields' => ['capa_path', 'perfil_path']],
            ['model' => EventoAtrativo::class, 'fields' => ['thumb_path']],
            ['model' => EventoMidia::class, 'fields' => ['path']],
            ['model' => JogosIndigenas::class, 'fields' => ['foto_perfil_path', 'foto_capa_path']],
            ['model' => JogosIndigenasEdicao::class, 'fields' => ['capa_path']],
            ['model' => JogosIndigenasEdicaoFoto::class, 'fields' => ['imagem_path']],
            ['model' => JogosIndigenasEdicaoPatrocinador::class, 'fields' => ['logo_path']],
            ['model' => RotaDoCacau::class, 'fields' => ['foto_perfil_path', 'foto_capa_path']],
            ['model' => RotaDoCacauEdicao::class, 'fields' => ['capa_path']],
            ['model' => RotaDoCacauEdicaoFoto::class, 'fields' => ['imagem_path']],
            ['model' => RotaDoCacauEdicaoPatrocinador::class, 'fields' => ['logo_path']],
            ['model' => Categoria::class, 'fields' => ['icone_path']],
            ['model' => Empresa::class, 'fields' => ['foto_perfil_path', 'foto_capa_path']],
            ['model' => EmpresaFoto::class, 'fields' => ['path']],
            ['model' => EspacoCultural::class, 'fields' => ['capa_path']],
            ['model' => EspacoCulturalMidia::class, 'fields' => ['path']],
            ['model' => PontoTuristico::class, 'fields' => ['capa_path', 'foto_capa_path']],
            ['model' => PontoMidia::class, 'fields' => ['path', 'thumb_path']],
            ['model' => Roteiro::class, 'fields' => ['capa_path']],
            ['model' => Aviso::class, 'fields' => ['imagem_path']],
            ['model' => Banner::class, 'fields' => ['imagem_path', 'imagem_original_path']],
            ['model' => BannerDestaque::class, 'fields' => ['imagem_desktop_path', 'imagem_mobile_path', 'video_desktop_path', 'video_mobile_path', 'poster_desktop_path', 'poster_mobile_path', 'fallback_image_desktop_path', 'fallback_image_mobile_path']],
            ['model' => GuiaRevista::class, 'fields' => ['capa_path']],
            ['model' => OndeComerPagina::class, 'fields' => ['hero_path']],
            ['model' => OndeFicarPagina::class, 'fields' => ['hero_path']],
            ['model' => Video::class, 'fields' => ['capa_path']],
        ];
    }

    private function queryFor(string $modelClass)
    {
        $query = $modelClass::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            $query->withTrashed();
        }

        return $query->orderBy((new $modelClass())->getKeyName());
    }

    private function extractPathValues(mixed $value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->flatMap(fn ($item) => $this->extractPathValues($item))
                ->values()
                ->all();
        }

        $normalized = $this->normalizePath($value);

        return $normalized ? [$normalized] : [];
    }

    private function normalizePath(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim(str_replace('\\', '/', $value));

        if ($value === '' || Str::startsWith($value, ['http://', 'https://', '//', 'imagens/', 'images/', 'optimized/'])) {
            return null;
        }

        if (Str::startsWith($value, '/storage/')) {
            $value = Str::after($value, '/storage/');
        } elseif (Str::startsWith($value, 'storage/')) {
            $value = Str::after($value, 'storage/');
        } elseif (Str::startsWith($value, '/')) {
            return null;
        }

        $value = ltrim($value, '/');

        return Str::startsWith(basename($value), '.') ? null : $value;
    }

    private function isIgnoredDiskPath(string $path): bool
    {
        return Str::startsWith($path, ['quarantine-media/', '.']);
    }

    private function uniqueQuarantineTarget($disk, string $batch, string $path): string
    {
        $target = $batch.'/'.ltrim($path, '/');

        if (! $disk->exists($target)) {
            return $target;
        }

        $info = pathinfo($target);
        $dirname = $info['dirname'] ?? $batch;
        $filename = $info['filename'] ?? 'file';
        $extension = isset($info['extension']) ? '.'.$info['extension'] : '';

        return $dirname.'/'.$filename.'-'.Str::random(6).$extension;
    }

    private function purgeOldQuarantineBatches($disk, int $days): array
    {
        $cutoff = now()->subDays($days)->timestamp;
        $deleted = [];

        foreach ($disk->directories('quarantine-media') as $directory) {
            $lastModified = $this->directoryLastModified($disk, $directory);

            if ($lastModified === null || $lastModified > $cutoff) {
                continue;
            }

            if ($disk->deleteDirectory($directory)) {
                $deleted[] = [
                    'directory' => $directory,
                    'last_modified' => date(DATE_ATOM, $lastModified),
                ];
            }
        }

        return $deleted;
    }

    private function pruneStorageTmp(int $days): array
    {
        $root = storage_path('app/tmp');

        return $this->pruneFilesystemDirectory($root, now()->subDays($days)->timestamp, true);
    }

    private function pruneOptimizedCache(int $days): array
    {
        $root = public_path('optimized/site');

        return $this->pruneFilesystemDirectory($root, now()->subDays($days)->timestamp, false);
    }

    private function pruneOldReports(int $days): array
    {
        $root = storage_path('app/media-cleanup');

        if (! File::isDirectory($root)) {
            return [];
        }

        $cutoff = now()->subDays($days)->timestamp;
        $deleted = [];

        foreach (File::allFiles($root) as $file) {
            $path = $file->getPathname();

            if (Str::endsWith(str_replace('\\', '/', $path), '/latest.json')) {
                continue;
            }

            if ($file->getMTime() > $cutoff) {
                continue;
            }

            File::delete($path);
            $deleted[] = $path;
        }

        return $deleted;
    }

    private function pruneFilesystemDirectory(string $root, int $cutoff, bool $deleteDirectories): array
    {
        if (! File::isDirectory($root)) {
            return [];
        }

        $deleted = [];

        foreach (File::allFiles($root) as $file) {
            if ($file->getMTime() > $cutoff) {
                continue;
            }

            File::delete($file->getPathname());
            $deleted[] = $file->getPathname();
        }

        if ($deleteDirectories) {
            $directories = collect(iterator_to_array(new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            )))
                ->filter(fn ($item) => $item->isDir())
                ->map(fn ($item) => $item->getPathname());

            foreach ($directories as $directory) {
                if (! File::isDirectory($directory)) {
                    continue;
                }

                if (count(File::files($directory)) === 0 && count(File::directories($directory)) === 0) {
                    File::deleteDirectory($directory);
                    $deleted[] = $directory;
                }
            }
        }

        return $deleted;
    }

    private function directoryLastModified($disk, string $directory): ?int
    {
        $files = $disk->allFiles($directory);

        if ($files === []) {
            return null;
        }

        return collect($files)
            ->map(fn (string $path) => $disk->lastModified($path))
            ->filter()
            ->max();
    }

    private function writeReport(array $report): ?string
    {
        $relativePath = trim((string) $this->option('report'));

        if ($relativePath === '') {
            return null;
        }

        $target = storage_path('app/'.ltrim(str_replace('\\', '/', $relativePath), '/'));
        File::ensureDirectoryExists(dirname($target));
        File::put($target, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $target;
    }
}
