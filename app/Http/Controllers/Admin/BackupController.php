<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupManager;
use App\Services\SystemHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use RuntimeException;
use Throwable;

class BackupController extends Controller
{
    public function index(Request $request, BackupManager $backups, SystemHealthService $health)
    {
        $local = $backups->listLocalBackups();
        $remote = $backups->listRemoteBackups();
        $remoteStatus = $backups->remoteHealth();
        $selectedBackup = null;

        if ($request->filled('selected_path')) {
            try {
                $selectedBackup = $backups->inspect(
                    $request->string('selected_disk', 'local')->toString() === 'remote' ? 'remote' : 'local',
                    $request->string('selected_path')->toString(),
                );
            } catch (Throwable) {
                $selectedBackup = null;
            }
        }

        $history = collect(array_merge(
            array_map(fn (array $item) => $item + ['origin' => 'Local'], array_slice($local, 0, 6)),
            array_map(fn (array $item) => $item + ['origin' => 'R2'], array_slice($remote, 0, 6)),
        ))->sortByDesc('modified_at')->take(8)->values()->all();

        $allowedSections = ['health', 'backups', 'remote', 'config'];
        $activeSection = $request->string('section', 'health')->toString();
        if (! in_array($activeSection, $allowedSections, true)) {
            $activeSection = 'health';
        }

        if ($selectedBackup) {
            $activeSection = $request->string('selected_disk', 'local')->toString() === 'remote' ? 'remote' : 'backups';
        }

        return view('admin.backups.index', [
            'localBackups' => $local,
            'remoteBackups' => $remote,
            'remoteConfig' => $backups->currentRemoteConfig(),
            'remoteConfigured' => $backups->remoteConfigured(),
            'remoteStatus' => $remoteStatus,
            'selectedBackup' => $selectedBackup,
            'history' => $history,
            'summary' => [
                'local_total' => count($local),
                'remote_total' => count($remote),
                'last_local' => $local[0] ?? null,
                'last_remote' => $remote[0] ?? null,
            ],
            'systemHealth' => $health->snapshot(),
            'activeSection' => $activeSection,
        ]);
    }

    public function auditMedia(): RedirectResponse
    {
        try {
            $exitCode = Artisan::call('media:cleanup', ['--report' => 'media-cleanup/latest.json']);
            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                throw new RuntimeException($output !== '' ? $output : 'Falha ao auditar a midia.');
            }

            return back()->with('ok', 'Auditoria de midia executada com sucesso. '.($output !== '' ? $output : ''));
        } catch (Throwable $e) {
            return back()->with('erro', $e->getMessage());
        }
    }

    public function pruneSafe(): RedirectResponse
    {
        try {
            $exitCode = Artisan::call('media:cleanup', [
                '--prune-temp' => true,
                '--prune-optimized' => true,
                '--prune-reports' => true,
                '--days' => (int) env('MEDIA_CLEANUP_SAFE_DAYS', 15),
                '--report' => 'media-cleanup/latest.json',
            ]);
            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                throw new RuntimeException($output !== '' ? $output : 'Falha ao podar temporarios seguros.');
            }

            return back()->with('ok', 'Temporarios, derivados e relatorios antigos foram podados com seguranca. '.($output !== '' ? $output : ''));
        } catch (Throwable $e) {
            return back()->with('erro', $e->getMessage());
        }
    }

    public function generate(Request $request, BackupManager $backups): RedirectResponse
    {
        $data = $request->validate([
            'scope' => ['required', 'in:all,database,storage'],
            'destination' => ['required', 'in:local,remote'],
        ]);

        try {
            $result = $backups->generate($data['scope'], $data['destination'] === 'remote');
            return back()->with('ok', 'Backup gerado com sucesso. '.($result['output'] ?: ''));
        } catch (Throwable $e) {
            return back()->with('erro', $e->getMessage());
        }
    }

    public function importPackage(Request $request, BackupManager $backups): RedirectResponse
    {
        $data = $request->validate([
            'package' => ['required', 'file', 'mimes:zip'],
        ]);

        try {
            $path = $backups->importUploadedPackage($data['package']);
            return back()->with('ok', 'Pacote importado para a biblioteca local: '.$path);
        } catch (Throwable $e) {
            return back()->with('erro', $e->getMessage());
        }
    }

    public function download(Request $request, BackupManager $backups)
    {
        $data = $request->validate([
            'path' => ['required', 'string'],
        ]);

        $absolutePath = $backups->localAbsolutePath($data['path']);
        return Response::download($absolutePath, basename($data['path']));
    }

    public function pushRemote(Request $request, BackupManager $backups): RedirectResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string'],
        ]);

        try {
            $backups->pushLocalToRemote($data['path']);
            return back()->with('ok', 'Backup exportado para o R2 com sucesso.');
        } catch (Throwable $e) {
            return back()->with('erro', $e->getMessage());
        }
    }

    public function pullRemote(Request $request, BackupManager $backups): RedirectResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string'],
        ]);

        try {
            $path = $backups->pullRemoteToLocal($data['path']);
            return back()->with('ok', 'Backup importado do R2 para a biblioteca local: '.$path);
        } catch (Throwable $e) {
            return back()->with('erro', $e->getMessage());
        }
    }

    public function destroyLocal(Request $request, BackupManager $backups): RedirectResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string'],
        ]);

        try {
            $backups->deleteLocal($data['path']);
            return back()->with('ok', 'Backup local removido.');
        } catch (Throwable $e) {
            return back()->with('erro', $e->getMessage());
        }
    }

    public function destroyRemote(Request $request, BackupManager $backups): RedirectResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string'],
        ]);

        try {
            $backups->deleteRemote($data['path']);
            return back()->with('ok', 'Backup remoto removido do R2.');
        } catch (Throwable $e) {
            return back()->with('erro', $e->getMessage());
        }
    }

    public function testRemote(BackupManager $backups): RedirectResponse
    {
        try {
            $result = $backups->testRemoteConnection();
            return back()->with('ok', 'Conexao com o R2 validada. Bucket '.$result['bucket'].' com '.$result['count'].' item(ns) visiveis.');
        } catch (RuntimeException $e) {
            return back()->with('erro', $e->getMessage());
        }
    }

    public function updateRemoteConfig(Request $request, BackupManager $backups): RedirectResponse
    {
        $data = $request->validate([
            'remote_enabled' => ['nullable', 'boolean'],
            'remote_disk' => ['required', 'string', 'max:50'],
            'access_key_id' => ['nullable', 'string', 'max:255'],
            'secret_access_key' => ['nullable', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:50'],
            'bucket' => ['nullable', 'string', 'max:255'],
            'endpoint' => ['nullable', 'url', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'use_path_style' => ['nullable', 'boolean'],
            'keep_local_days' => ['required', 'integer', 'min:1', 'max:365'],
            'keep_remote_days' => ['required', 'integer', 'min:1', 'max:365'],
            'auto_enabled' => ['nullable', 'boolean'],
            'time' => ['required', 'date_format:H:i'],
        ]);

        try {
            $backups->updateRemoteConfig($data);
            return back()->with('ok', 'Configuracao de backup atualizada com sucesso.');
        } catch (Throwable $e) {
            return back()->with('erro', $e->getMessage());
        }
    }
}
