<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveEspacoCulturalRequest;
use App\Models\Catalogo\EspacoCultural;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\SyncHorariosService;

class EspacoCulturalController extends Controller
{
    public function __construct(
        private SyncHorariosService $syncHorariosService
    ) {}

    private array $diasSemana = [
        0 => 'Domingo',
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
    ];

    public function index(Request $request)
    {
        $busca = trim((string) $request->input('busca', ''));
        $status = (string) $request->input('status', 'todos');
        $tipo = (string) $request->input('tipo', 'todos');

        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $espacos = EspacoCultural::query()
            ->withCount(['horarios', 'midias', 'agendamentos'])
            ->with('horarios')
            ->when($status !== 'todos', fn ($q) => $q->where('status', $status))
            ->when($tipo !== 'todos', fn ($q) => $q->where('tipo', $tipo))
            ->when($busca !== '', function ($q) use ($busca, $like) {
                $q->where(function ($w) use ($busca, $like) {
                    $w->where('nome', $like, "%{$busca}%")
                        ->orWhere('resumo', $like, "%{$busca}%")
                        ->orWhere('descricao', $like, "%{$busca}%")
                        ->orWhere('bairro', $like, "%{$busca}%");
                });
            })
            ->ordenados()
            ->paginate(12)
            ->withQueryString();

        return view('coordenador.espacos_culturais.index', compact('espacos', 'busca', 'status', 'tipo'));
    }

    public function create()
    {
        return view('coordenador.espacos_culturais.create', [
            'espaco' => new EspacoCultural(),
            'diasSemana' => $this->diasSemana,
        ]);
    }

    public function store(SaveEspacoCulturalRequest $request): RedirectResponse
    {
        $espaco = new EspacoCultural();
        $this->persist($request, $espaco);

        return redirect()
            ->route('coordenador.espacos-culturais.index')
            ->with('ok', 'Espaço cultural cadastrado com sucesso.');
    }

    public function edit(EspacoCultural $espaco)
    {
        return view('coordenador.espacos_culturais.edit', [
            'espaco' => $espaco->load(['horarios', 'midias']),
            'diasSemana' => $this->diasSemana,
        ]);
    }

    public function update(SaveEspacoCulturalRequest $request, EspacoCultural $espaco): RedirectResponse
    {
        $this->persist($request, $espaco);

        return back()->with('ok', 'Espaço cultural atualizado com sucesso.');
    }

    public function destroy(EspacoCultural $espaco): RedirectResponse
    {
        $temAgendamentoFuturo = $espaco->agendamentos()
            ->whereDate('data_visita', '>=', now()->toDateString())
            ->whereIn('status', \App\Models\Catalogo\EspacoCulturalAgendamento::STATUSES_QUE_CONSOMEM_VAGA)
            ->exists();

        if ($temAgendamentoFuturo) {
            return redirect()
                ->route('coordenador.espacos-culturais.index')
                ->with('erro', 'Não é possível excluir este espaço porque ele possui agendamentos futuros ativos.');
        }

        // Soft delete apenas. Não apagamos arquivos aqui para evitar perda desnecessária.
        $espaco->delete();

        return redirect()
            ->route('coordenador.espacos-culturais.index')
            ->with('ok', 'Espaço cultural arquivado com sucesso.');
    }

    private function persist(SaveEspacoCulturalRequest $request, EspacoCultural $espaco): void
    {
        $validated = $request->validated();

        $data = Arr::only($validated, [
            'tipo',
            'nome',
            'resumo',
            'descricao',
            'maps_url',
            'endereco',
            'bairro',
            'cidade',
            'lat',
            'lng',
            'ordem',
            'status',
            'agendamento_ativo',
            'agendamento_contato_nome',
            'agendamento_contato_label',
            'agendamento_whatsapp_phone',
            'agendamento_instrucoes',
        ]);

        DB::transaction(function () use ($request, $espaco, $data, $validated) {
            if (!$espaco->exists) {
                $espaco->created_by = auth()->id();
            }

            $espaco->fill($data);
            $espaco->save();

            $this->syncHorariosService->sync($espaco, $validated['horarios'] ?? []);

            $this->syncCapa($request, $espaco);

            if ($espaco->exists) {
                $this->removeSelectedMidias($request, $espaco);
            }

            $this->storeGaleria($request, $espaco);

            $espaco->save();
        });
    }

    private function syncCapa(Request $request, EspacoCultural $espaco): void
    {
        if ($request->boolean('remover_capa') && $espaco->capa_path) {
            $this->deleteFile($espaco->capa_path);
            $espaco->capa_path = null;
        }

        if ($request->hasFile('capa')) {
            $this->deleteFile($espaco->capa_path);

            $espaco->capa_path = $request->file('capa')->store('espacos-culturais/capas', 'public');
        }
    }

    private function storeGaleria(Request $request, EspacoCultural $espaco): void
    {
        if (!$request->hasFile('galeria')) {
            return;
        }

        $ordemBase = ((int) $espaco->midias()->max('ordem')) + 1;

        foreach ($request->file('galeria') as $i => $file) {
            $espaco->midias()->create([
                'path' => $file->store('espacos-culturais/galeria', 'public'),
                'alt' => $espaco->nome,
                'ordem' => $ordemBase + $i,
            ]);
        }
    }

    private function removeSelectedMidias(Request $request, EspacoCultural $espaco): void
    {
        $ids = collect($request->input('remover_midias', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        if (empty($ids)) {
            return;
        }

        $midias = $espaco->midias()->whereIn('id', $ids)->get();

        foreach ($midias as $midia) {
            $this->deleteFile($midia->path);
            $midia->delete();
        }
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
