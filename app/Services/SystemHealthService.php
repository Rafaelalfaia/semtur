<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

class SystemHealthService
{
    public function snapshot(): array
    {
        $areas = [
            $this->scanArea('storage_public', 'Midia publica', storage_path('app/public')),
            $this->scanArea('storage_logs', 'Logs Laravel', storage_path('logs')),
            $this->scanArea('storage_backups', 'Backups locais', storage_path('app/backups')),
            $this->scanArea('optimized_site', 'Derivados otimizados', public_path('optimized/site')),
            $this->scanArea('vendor', 'Vendor', base_path('vendor')),
            $this->scanArea('public', 'Public', public_path()),
        ];

        $totalBytes = array_sum(array_column($areas, 'bytes'));
        $largestAreas = collect($areas)
            ->sortByDesc('bytes')
            ->take(5)
            ->values()
            ->all();

        return [
            'generated_at' => now()->toDateTimeString(),
            'totals' => [
                'bytes' => $totalBytes,
                'label' => $this->formatBytes($totalBytes),
                'files' => array_sum(array_column($areas, 'files_count')),
            ],
            'areas' => $areas,
            'largest_areas' => $largestAreas,
            'largest_files' => $this->largestFiles(),
            'media_cleanup' => $this->latestMediaCleanupReport(),
        ];
    }

    private function scanArea(string $key, string $label, string $path): array
    {
        if (! File::exists($path)) {
            return [
                'key' => $key,
                'label' => $label,
                'path' => $path,
                'exists' => false,
                'bytes' => 0,
                'size_label' => '0 B',
                'files_count' => 0,
            ];
        }

        $bytes = 0;
        $filesCount = 0;

        try {
            if (File::isFile($path)) {
                $bytes = (int) filesize($path);
                $filesCount = 1;
            } else {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
                );

                foreach ($iterator as $item) {
                    if (! $item instanceof SplFileInfo || ! $item->isFile()) {
                        continue;
                    }

                    $bytes += (int) $item->getSize();
                    $filesCount++;
                }
            }
        } catch (Throwable) {
            $bytes = 0;
            $filesCount = 0;
        }

        return [
            'key' => $key,
            'label' => $label,
            'path' => $path,
            'exists' => true,
            'bytes' => $bytes,
            'size_label' => $this->formatBytes($bytes),
            'files_count' => $filesCount,
        ];
    }

    private function largestFiles(): array
    {
        $roots = [
            storage_path('app/public'),
            storage_path('logs'),
            storage_path('app/backups'),
            public_path('optimized/site'),
        ];

        $files = [];

        foreach ($roots as $root) {
            if (! File::isDirectory($root)) {
                continue;
            }

            try {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
                );

                foreach ($iterator as $item) {
                    if (! $item instanceof SplFileInfo || ! $item->isFile()) {
                        continue;
                    }

                    $path = $item->getPathname();
                    $size = (int) $item->getSize();
                    $relative = str_replace('\\', '/', Str::after($path, base_path().DIRECTORY_SEPARATOR));

                    $files[] = [
                        'path' => $relative === $path ? str_replace('\\', '/', $path) : $relative,
                        'bytes' => $size,
                        'size_label' => $this->formatBytes($size),
                    ];
                }
            } catch (Throwable) {
                continue;
            }
        }

        usort($files, fn (array $a, array $b) => $b['bytes'] <=> $a['bytes']);

        return array_slice($files, 0, 8);
    }

    private function latestMediaCleanupReport(): ?array
    {
        $path = storage_path('app/media-cleanup/latest.json');

        if (! File::isFile($path)) {
            return null;
        }

        try {
            $decoded = json_decode(File::get($path), true);
        } catch (Throwable) {
            return null;
        }

        if (! is_array($decoded)) {
            return null;
        }

        $summary = $decoded['summary'] ?? [];
        $maintenance = $decoded['maintenance'] ?? [];

        return [
            'generated_at' => $decoded['generated_at'] ?? null,
            'days' => $decoded['days'] ?? null,
            'summary' => [
                'disk_files' => (int) ($summary['disk_files'] ?? 0),
                'referenced_files' => (int) ($summary['referenced_files'] ?? 0),
                'orphan_files' => (int) ($summary['orphan_files'] ?? 0),
            ],
            'maintenance' => [
                'temp_deleted' => count($maintenance['temp_deleted'] ?? []),
                'optimized_deleted' => count($maintenance['optimized_deleted'] ?? []),
                'reports_deleted' => count($maintenance['reports_deleted'] ?? []),
            ],
        ];
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
