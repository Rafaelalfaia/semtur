<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\EspacoCultural;
use App\Models\Catalogo\EspacoCulturalAgendamento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EspacoCulturalAgendamentoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));
        $espacoId = $request->input('espaco_id');
        $tecnicoId = $request->input('tecnico_id');
        $dataInicial = $request->input('data_inicial');
        $dataFinal = $request->input('data_final');

        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $agendamentos = EspacoCulturalAgendamento::query()
            ->with(['espaco', 'horario', 'tecnico'])
            ->when($q !== '', function ($query) use ($q, $like) {
                $query->where(function ($w) use ($q, $like) {
                    $w->where('protocolo', $like, "%{$q}%")
                        ->orWhere('nome', $like, "%{$q}%")
                        ->orWhere('telefone', $like, "%{$q}%")
                        ->orWhere('email', $like, "%{$q}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when(filled($espacoId), fn ($query) => $query->where('espaco_cultural_id', $espacoId))
            ->when(filled($tecnicoId), fn ($query) => $query->where('tecnico_id', $tecnicoId))
            ->when(filled($dataInicial), fn ($query) => $query->whereDate('data_visita', '>=', $dataInicial))
            ->when(filled($dataFinal), fn ($query) => $query->whereDate('data_visita', '<=', $dataFinal))
            ->orderBy('data_visita')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $espacos = EspacoCultural::query()
            ->orderBy('nome')
            ->get(['id', 'nome']);

        $tecnicos = User::query()
            ->role('Tecnico')
            ->where('coordenador_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('coordenador.espacos_culturais.agendamentos.index', compact(
            'agendamentos',
            'espacos',
            'tecnicos',
            'q',
            'status',
            'espacoId',
            'tecnicoId',
            'dataInicial',
            'dataFinal'
        ));
    }

    public function show(EspacoCulturalAgendamento $agendamento)
    {
        return view('coordenador.espacos_culturais.agendamentos.show', [
            'agendamento' => $agendamento->load(['espaco', 'horario', 'tecnico', 'atribuidor']),
            'tecnicos' => User::query()
                ->role('Tecnico')
                ->where('coordenador_id', auth()->id())
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function confirmar(EspacoCulturalAgendamento $agendamento)
    {
        $agendamento->update([
            'status' => EspacoCulturalAgendamento::STATUS_CONFIRMADO,
            'confirmado_em' => now(),
        ]);

        return back()->with('ok', 'Agendamento confirmado.');
    }

    public function cancelar(Request $request, EspacoCulturalAgendamento $agendamento)
    {
        $data = $request->validate([
            'observacao_interna' => ['nullable', 'string'],
        ]);

        $agendamento->update([
            'status' => EspacoCulturalAgendamento::STATUS_CANCELADO,
            'cancelado_em' => now(),
            'observacao_interna' => $data['observacao_interna'] ?? $agendamento->observacao_interna,
        ]);

        return back()->with('ok', 'Agendamento cancelado.');
    }

    public function concluir(EspacoCulturalAgendamento $agendamento)
    {
        $agendamento->update([
            'status' => EspacoCulturalAgendamento::STATUS_CONCLUIDO,
            'concluido_em' => now(),
        ]);

        return back()->with('ok', 'Agendamento concluído.');
    }

    public function atribuirTecnico(Request $request, EspacoCulturalAgendamento $agendamento)
    {
        $tecnicosIds = User::query()
            ->role('Tecnico')
            ->where('coordenador_id', auth()->id())
            ->pluck('id')
            ->all();

        $data = $request->validate([
            'tecnico_id' => ['nullable', Rule::in($tecnicosIds)],
        ]);

        $agendamento->update([
            'tecnico_id' => $data['tecnico_id'] ?? null,
            'atribuido_por' => $data['tecnico_id'] ? auth()->id() : null,
        ]);

        return back()->with('ok', 'Técnico atualizado no agendamento.');
    }

    public function observacaoInterna(Request $request, EspacoCulturalAgendamento $agendamento)
    {
        $data = $request->validate([
            'observacao_interna' => ['nullable', 'string'],
        ]);

        $agendamento->update([
            'observacao_interna' => $data['observacao_interna'] ?? null,
        ]);

        return back()->with('ok', 'Observação interna salva.');
    }
}
