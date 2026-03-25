<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\Empresa;
use App\Models\Catalogo\PontoTuristico;
use App\Models\Catalogo\Roteiro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoteiroController extends Controller
{
    public function index(Request $request)
    {
        $busca   = trim((string) $request->input('busca', ''));
        $status  = (string) $request->input('status', 'todos');
        $duracao = (string) $request->input('duracao', 'todos');
        $perfil  = (string) $request->input('perfil', 'todos');

        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $roteiros = Roteiro::query()
            ->withCount(['etapas', 'empresasSugestao'])
            ->when($status !== 'todos' && $status !== '', fn ($q) => $q->where('status', $status))
            ->when($duracao !== 'todos' && $duracao !== '', fn ($q) => $q->where('duracao_slug', $duracao))
            ->when($perfil !== 'todos' && $perfil !== '', fn ($q) => $q->where('perfil_slug', $perfil))
            ->when($busca !== '', function ($q) use ($busca, $like) {
                $q->where(function ($w) use ($busca, $like) {
                    $w->where('titulo', $like, "%{$busca}%")
                        ->orWhere('resumo', $like, "%{$busca}%")
                        ->orWhere('descricao', $like, "%{$busca}%");
                });
            })
            ->orderBy('ordem')
            ->orderByDesc('published_at')
            ->orderBy('titulo')
            ->paginate(12)
            ->withQueryString();

        return view('coordenador.roteiros.index', [
            'roteiros' => $roteiros,
            'busca' => $busca,
            'status' => $status,
            'duracao' => $duracao,
            'perfil' => $perfil,
            'duracoes' => Roteiro::DURACOES,
            'perfis' => Roteiro::PERFIS,
        ]);
    }

    public function create()
    {
        return view('coordenador.roteiros.create', [
            'roteiro' => new Roteiro([
                'status' => Roteiro::STATUS_RASCUNHO,
                'ordem' => 0,
                'duracao_slug' => '1_dia',
                'perfil_slug' => 'geral',
            ]),
            'duracoes' => Roteiro::DURACOES,
            'perfis' => Roteiro::PERFIS,
            'tiposBloco' => Roteiro::TIPOS_BLOCO,
            'tiposSugestao' => Roteiro::TIPOS_SUGESTAO,
            'pontos' => PontoTuristico::query()
                ->where('status', 'publicado')
                ->orderBy('ordem')
                ->orderBy('nome')
                ->get(['id', 'nome']),
            'empresas' => Empresa::query()
                ->where('status', 'publicado')
                ->orderBy('ordem')
                ->orderBy('nome')
                ->get(['id', 'nome']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $roteiro = DB::transaction(function () use ($request, $data) {
            $roteiro = new Roteiro();
            $this->fillAndPersist($roteiro, $request, $data);

            return $roteiro;
        });

        return redirect()
            ->route('coordenador.roteiros.edit', $roteiro)
            ->with('ok', 'Roteiro criado com sucesso.');
    }

    public function edit(Roteiro $roteiro)
    {
        $roteiro->load([
            'etapas.pontos.pontoTuristico:id,nome',
            'empresasSugestao.empresa:id,nome',
        ]);

        return view('coordenador.roteiros.edit', [
            'roteiro' => $roteiro,
            'duracoes' => Roteiro::DURACOES,
            'perfis' => Roteiro::PERFIS,
            'tiposBloco' => Roteiro::TIPOS_BLOCO,
            'tiposSugestao' => Roteiro::TIPOS_SUGESTAO,
            'pontos' => PontoTuristico::query()
                ->where('status', 'publicado')
                ->orderBy('ordem')
                ->orderBy('nome')
                ->get(['id', 'nome']),
            'empresas' => Empresa::query()
                ->where('status', 'publicado')
                ->orderBy('ordem')
                ->orderBy('nome')
                ->get(['id', 'nome']),
        ]);
    }

    public function update(Request $request, Roteiro $roteiro)
    {
        $data = $this->validated($request, $roteiro->id);

        DB::transaction(function () use ($roteiro, $request, $data) {
            $this->fillAndPersist($roteiro, $request, $data);
        });

        return back()->with('ok', 'Roteiro atualizado com sucesso.');
    }

    public function destroy(Roteiro $roteiro)
    {
        $roteiro->delete();

        return back()->with('ok', 'Roteiro movido para a lixeira.');
    }

    public function publicar(Roteiro $roteiro)
    {
        $roteiro->update([
            'status' => Roteiro::STATUS_PUBLICADO,
            'published_at' => $roteiro->published_at ?: now(),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Roteiro publicado.');
    }

    public function arquivar(Roteiro $roteiro)
    {
        $roteiro->update([
            'status' => Roteiro::STATUS_ARQUIVADO,
            'published_at' => null,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Roteiro arquivado.');
    }

    public function rascunho(Roteiro $roteiro)
    {
        $roteiro->update([
            'status' => Roteiro::STATUS_RASCUNHO,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Roteiro movido para rascunho.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('roteiros', 'slug')->ignore($id)],
            'resumo' => ['required', 'string', 'max:1200'],
            'descricao' => ['nullable', 'string'],

            'duracao_slug' => ['required', Rule::in(array_keys(Roteiro::DURACOES))],
            'perfil_slug' => ['required', Rule::in(array_keys(Roteiro::PERFIS))],

            'publico_label' => ['nullable', 'string', 'max:120'],
            'melhor_epoca' => ['nullable', 'string', 'max:160'],
            'deslocamento' => ['nullable', 'string', 'max:160'],
            'nivel_intensidade' => ['nullable', Rule::in(['leve', 'moderado', 'intenso'])],

            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:255'],

            'ordem' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(Roteiro::STATUS)],

            'capa' => ['nullable', 'image', 'max:6144'],
            'remover_capa' => ['nullable', 'boolean'],

            'etapas' => ['required', 'array', 'min:1'],
            'etapas.*.titulo' => ['required', 'string', 'max:120'],
            'etapas.*.subtitulo' => ['nullable', 'string', 'max:160'],
            'etapas.*.descricao' => ['nullable', 'string'],
            'etapas.*.tipo_bloco' => ['required', Rule::in(array_keys(Roteiro::TIPOS_BLOCO))],

            'etapas.*.pontos' => ['required', 'array', 'min:1'],
            'etapas.*.pontos.*.ponto_turistico_id' => ['required', 'integer', 'exists:pontos_turisticos,id'],
            'etapas.*.pontos.*.observacao_curta' => ['nullable', 'string', 'max:255'],
            'etapas.*.pontos.*.tempo_estimado_min' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'etapas.*.pontos.*.destaque' => ['nullable', 'boolean'],

            'empresas' => ['nullable', 'array'],
            'empresas.*.empresa_id' => ['required', 'integer', 'exists:empresas,id'],
            'empresas.*.tipo_sugestao' => ['required', Rule::in(array_keys(Roteiro::TIPOS_SUGESTAO))],
            'empresas.*.observacao_curta' => ['nullable', 'string', 'max:255'],
            'empresas.*.destaque' => ['nullable', 'boolean'],
        ]);

        if (
            ($data['status'] ?? null) === Roteiro::STATUS_PUBLICADO &&
            !$request->hasFile('capa') &&
            !$id
        ) {
            abort(422, 'Para publicar o roteiro na criação inicial, envie uma capa.');
        }

        return $data;
    }

    private function fillAndPersist(Roteiro $roteiro, Request $request, array $data): void
    {
        $roteiro->fill([
            'titulo' => $data['titulo'],
            'slug' => filled($data['slug'] ?? null) ? Str::slug($data['slug']) : null,
            'resumo' => $data['resumo'],
            'descricao' => $data['descricao'] ?? null,
            'duracao_slug' => $data['duracao_slug'],
            'perfil_slug' => $data['perfil_slug'],
            'publico_label' => $this->nullable($data['publico_label'] ?? null),
            'melhor_epoca' => $this->nullable($data['melhor_epoca'] ?? null),
            'deslocamento' => $this->nullable($data['deslocamento'] ?? null),
            'nivel_intensidade' => $data['nivel_intensidade'] ?? null,
            'seo_title' => $this->nullable($data['seo_title'] ?? null),
            'seo_description' => $this->nullable($data['seo_description'] ?? null),
            'ordem' => $data['ordem'] ?? 0,
            'status' => $data['status'],
        ]);

        if (!$roteiro->exists) {
            $roteiro->created_by = auth()->id();
        }

        $roteiro->updated_by = auth()->id();

        if ($request->boolean('remover_capa') && $roteiro->capa_path) {
            Storage::disk('public')->delete($roteiro->capa_path);
            $roteiro->capa_path = null;
        }

        if ($request->hasFile('capa')) {
            if ($roteiro->capa_path) {
                Storage::disk('public')->delete($roteiro->capa_path);
            }

            $roteiro->capa_path = ltrim(
                $request->file('capa')->store('roteiros/capas', 'public'),
                '/'
            );
        }

        if ($roteiro->status === Roteiro::STATUS_PUBLICADO && empty($roteiro->published_at)) {
            $roteiro->published_at = now();
        }

        if ($roteiro->status !== Roteiro::STATUS_PUBLICADO) {
            $roteiro->published_at = null;
        }

        $roteiro->save();

        $this->syncEtapas($roteiro, $data['etapas'] ?? []);
        $this->syncEmpresas($roteiro, $data['empresas'] ?? []);
    }

    private function syncEtapas(Roteiro $roteiro, array $etapas): void
    {
        $roteiro->etapas()->delete();

        foreach (array_values($etapas) as $indexEtapa => $etapaData) {
            $etapa = $roteiro->etapas()->create([
                'titulo' => $etapaData['titulo'],
                'subtitulo' => $this->nullable($etapaData['subtitulo'] ?? null),
                'descricao' => $etapaData['descricao'] ?? null,
                'tipo_bloco' => $etapaData['tipo_bloco'],
                'ordem' => $indexEtapa,
            ]);

            foreach (array_values($etapaData['pontos'] ?? []) as $indexPonto => $pontoData) {
                $etapa->pontos()->create([
                    'ponto_turistico_id' => (int) $pontoData['ponto_turistico_id'],
                    'ordem' => $indexPonto,
                    'destaque' => (bool) ($pontoData['destaque'] ?? false),
                    'observacao_curta' => $this->nullable($pontoData['observacao_curta'] ?? null),
                    'tempo_estimado_min' => filled($pontoData['tempo_estimado_min'] ?? null)
                        ? (int) $pontoData['tempo_estimado_min']
                        : null,
                ]);
            }
        }
    }

    private function syncEmpresas(Roteiro $roteiro, array $empresas): void
    {
        $roteiro->empresasSugestao()->delete();

        foreach (array_values($empresas) as $indexEmpresa => $empresaData) {
            $roteiro->empresasSugestao()->create([
                'empresa_id' => (int) $empresaData['empresa_id'],
                'tipo_sugestao' => $empresaData['tipo_sugestao'],
                'ordem' => $indexEmpresa,
                'destaque' => (bool) ($empresaData['destaque'] ?? false),
                'observacao_curta' => $this->nullable($empresaData['observacao_curta'] ?? null),
            ]);
        }
    }

    private function nullable(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === '' ? null : $value;
    }
}
