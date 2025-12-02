<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MaintenanceController extends Controller
{
    public function clear(Request $request)
    {
        // Autorização básica: somente Admin ou Coordenador
        $u = $request->user();
        if (!$u || (method_exists($u, 'hasAnyRole') && !$u->hasAnyRole(['Admin','Coordenador']))) {
            abort(403, 'Ação não permitida.');
        }

        try {
            // Limpa tudo que o "optimize:clear" cobre (config, route, view, cache, event)
            Artisan::call('optimize:clear');

            // (Opcional, mas explícito — redundante com optimize:clear)
            Artisan::call('view:clear');

            // Limpezas específicas do app (ex.: dashboards em cache)
            Cache::forget('coord:dashboard:v2'); // tua chave atual

            // Log para auditoria
            Log::info('Cache limpo manualmente', [
                'user_id' => $u->id ?? null,
                'ip'      => $request->ip(),
            ]);

            return back()->with('ok', 'Cache do sistema limpo com sucesso.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('fail', 'Falha ao limpar cache: '.$e->getMessage());
        }
    }
}
