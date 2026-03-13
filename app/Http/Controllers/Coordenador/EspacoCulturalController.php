<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\EspacoCultural;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EspacoCulturalController extends Controller
{
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
        $busca  = trim((string) $request->input('busca', ''));
        $status = (string) $request->input('status', 'todos');
        $tipo   = (string) $request->input('tipo', 'todos');

        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $espacos = EspacoCultural::query()
            ->withCount('horarios')
            ->with(['horarios' => fn($q) => $q->orderBy('dia_semana')->orderBy('hora_inicio')])
            ->when($status !== 'todos', fn($q) => $q->where('status', $status))
            ->when($tipo !== 'todos', fn($q) => $q->where('tipo', $tipo))
            ->when($busca !== '', function ($q) use ($busca, $like) {
                $q->where(function ($w) use ($busca, $like) {
                    $w->where('nome', $like, "%{$busca}%")
                      ->orWhere('descricao', $like, "%{$busca}%");
                });
            })
            ->orderBy('ordem')
            ->orderBy('nome')
            ->paginate(12)
            ->withQueryString();

        return view('coordenador.espacos_culturais.index', compact('espacos', 'busca', 'status', 'tipo'));
    }

    public function create()
    {
        $espaco = new EspacoCultural([
            'tipo'   => 'museu',
            'cidade' => 'Altamira',
            'status' => EspacoCultural::STATUS_RASCUNHO,
            'ordem'  => 0,
        ]);

        return view('coordenador.espacos_culturais.create', [
            'espaco'     => $espaco,
            'diasSemana' => $this->diasSemana,
        ]);
    }

    public function store(Request $request)
    {
        $this->mergeCoordsFromUrl($request);
        $data = $this->validatedData($request);

        $horarios = $this->sanitizeHorarios($data['horarios'] ?? []);

        if (empty($horarios)) {
            return back()->withErrors([
                'horarios' => 'Informe pelo menos um dia e horário de visita.',
            ])->withInput();
        }

        if ($erro = $this->validateHorarios($horarios)) {
            return back()->withErrors([
                'horarios' => $erro,
            ])->withInput();
        }

        if (
            $data['status'] === EspacoCultural::STATUS_PUBLICADO &&
            (empty($data['lat']) || empty($data['lng']))
        ) {
            return back()->withErrors([
                'maps_url' => 'Para publicar, informe um link de mapa com coordenadas válidas.',
            ])->withInput();
        }

        DB::transaction(function () use ($data, $horarios) {
            $espaco = new EspacoCultural();
            $espaco->fill($data);
            $espaco->created_by = auth()->id();
            $espaco->save();

            $espaco->horarios()->createMany($horarios);
        });

        return redirect()
            ->route('coordenador.espacos-culturais.index')
            ->with('ok', 'Espaço cultural cadastrado com sucesso.');
    }

    public function edit(EspacoCultural $espaco)
    {
        return view('coordenador.espacos_culturais.edit', [
            'espaco'     => $espaco->load('horarios'),
            'diasSemana' => $this->diasSemana,
        ]);
    }

    public function update(Request $request, EspacoCultural $espaco)
    {
        $this->mergeCoordsFromUrl($request);
        $data = $this->validatedData($request);

        $horarios = $this->sanitizeHorarios($data['horarios'] ?? []);

        if (empty($horarios)) {
            return back()->withErrors([
                'horarios' => 'Informe pelo menos um dia e horário de visita.',
            ])->withInput();
        }

        if ($erro = $this->validateHorarios($horarios)) {
            return back()->withErrors([
                'horarios' => $erro,
            ])->withInput();
        }

        if (
            $data['status'] === EspacoCultural::STATUS_PUBLICADO &&
            (empty($data['lat']) || empty($data['lng']))
        ) {
            return back()->withErrors([
                'maps_url' => 'Para publicar, informe um link de mapa com coordenadas válidas.',
            ])->withInput();
        }

        DB::transaction(function () use ($espaco, $data, $horarios) {
            $espaco->fill($data);
            $espaco->save();

            $espaco->horarios()->delete();
            $espaco->horarios()->createMany($horarios);
        });

        return back()->with('ok', 'Espaço cultural atualizado com sucesso.');
    }

    public function destroy(EspacoCultural $espaco)
    {
        $espaco->delete();

        return redirect()
            ->route('coordenador.espacos-culturais.index')
            ->with('ok', 'Espaço cultural excluído com sucesso.');
    }

    public function publicar(EspacoCultural $espaco)
    {
        if (empty($espaco->lat) || empty($espaco->lng)) {
            return back()->withErrors([
                'maps_url' => 'Antes de publicar, informe um link de mapa com coordenadas válidas.',
            ]);
        }

        $espaco->update([
            'status'       => EspacoCultural::STATUS_PUBLICADO,
            'published_at' => $espaco->published_at ?? now(),
        ]);

        return back()->with('ok', 'Espaço cultural publicado.');
    }

    public function arquivar(EspacoCultural $espaco)
    {
        $espaco->update([
            'status' => EspacoCultural::STATUS_ARQUIVADO,
        ]);

        return back()->with('ok', 'Espaço cultural arquivado.');
    }

    public function rascunho(EspacoCultural $espaco)
    {
        $espaco->update([
            'status' => EspacoCultural::STATUS_RASCUNHO,
        ]);

        return back()->with('ok', 'Espaço cultural movido para rascunho.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'tipo'      => ['required', Rule::in(EspacoCultural::TIPOS)],
            'nome'      => ['required', 'string', 'max:160'],
            'descricao' => ['nullable', 'string'],
            'status'    => ['required', Rule::in([
                EspacoCultural::STATUS_RASCUNHO,
                EspacoCultural::STATUS_PUBLICADO,
                EspacoCultural::STATUS_ARQUIVADO,
            ])],
            'ordem'     => ['nullable', 'integer', 'min:0'],

            'maps_url'  => ['nullable', 'url', 'max:2048'],
            'lat'       => ['nullable', 'numeric', 'between:-90,90'],
            'lng'       => ['nullable', 'numeric', 'between:-180,180'],

            'endereco'  => ['nullable', 'string', 'max:255'],
            'bairro'    => ['nullable', 'string', 'max:255'],
            'cidade'    => ['nullable', 'string', 'max:120'],

            'horarios'                    => ['required', 'array', 'min:1'],
            'horarios.*.dia_semana'       => ['required', 'integer', 'between:0,6'],
            'horarios.*.hora_inicio'      => ['required', 'date_format:H:i'],
            'horarios.*.hora_fim'         => ['required', 'date_format:H:i'],
            'horarios.*.vagas'            => ['nullable', 'integer', 'min:1'],
            'horarios.*.observacao'       => ['nullable', 'string', 'max:190'],
            'horarios.*.ativo'            => ['nullable', 'boolean'],
            'horarios.*.ordem'            => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function sanitizeHorarios(array $rows): array
    {
        return collect($rows)
            ->map(function ($row, $i) {
                return [
                    'dia_semana' => isset($row['dia_semana']) ? (int) $row['dia_semana'] : null,
                    'hora_inicio' => trim((string) ($row['hora_inicio'] ?? '')),
                    'hora_fim' => trim((string) ($row['hora_fim'] ?? '')),
                    'vagas' => filled($row['vagas'] ?? null) ? (int) $row['vagas'] : null,
                    'observacao' => trim((string) ($row['observacao'] ?? '')) ?: null,
                    'ativo' => (bool) ($row['ativo'] ?? true),
                    'ordem' => isset($row['ordem']) ? (int) $row['ordem'] : $i,
                ];
            })
            ->filter(fn($row) =>
                $row['dia_semana'] !== null &&
                $row['hora_inicio'] !== '' &&
                $row['hora_fim'] !== ''
            )
            ->values()
            ->all();
    }

    private function validateHorarios(array $horarios): ?string
    {
        foreach ($horarios as $i => $horario) {
            if ($horario['hora_fim'] <= $horario['hora_inicio']) {
                return 'No horário #' . ($i + 1) . ' a hora final precisa ser maior que a inicial.';
            }
        }

        return null;
    }

    private function extractCoordsFromUrl(?string $url): array
    {
        if (!$url) {
            return [null, null];
        }

        $s = urldecode(trim($url));

        if (preg_match('~@\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~', $s, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }

        if (preg_match('~[?&](?:q|ll)=\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~i', $s, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }

        if (preg_match('~!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)~', $s, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }

        if (preg_match('~!4d(-?\d+(?:\.\d+)?)!3d(-?\d+(?:\.\d+)?)~', $s, $m)) {
            return [(float) $m[2], (float) $m[1]];
        }

        if (preg_match('~[?&]cp=(-?\d+(?:\.\d+)?)\~(-?\d+(?:\.\d+)?)~i', $s, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }

        if (preg_match('~[?&]sp=point\.(-?\d+(?:\.\d+)?)_(-?\d+(?:\.\d+)?)~i', $s, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }

        if (
            preg_match('~[?&](?:mlat|lat)=(-?\d+(?:\.\d+)?)~i', $s, $ma) &&
            preg_match('~[?&](?:mlon|lon|lng)=(-?\d+(?:\.\d+)?)~i', $s, $mb)
        ) {
            return [(float) $ma[1], (float) $mb[1]];
        }

        if (preg_match('~(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~', $s, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }

        return [null, null];
    }

    private function mergeCoordsFromUrl(Request $request): void
    {
        [$lat, $lng] = $this->extractCoordsFromUrl($request->input('maps_url'));

        if ($lat !== null && $lng !== null) {
            $request->merge([
                'lat' => $lat,
                'lng' => $lng,
            ]);
        }
    }
}
