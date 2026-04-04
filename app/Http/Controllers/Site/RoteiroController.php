<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ResolvesEditableHero;
use App\Models\Catalogo\Roteiro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoteiroController extends Controller
{
    use ResolvesEditableHero;

    public function index(Request $request)
    {
        $busca   = trim((string) $request->input('q', ''));
        $duracao = (string) $request->input('duracao', '');
        $perfil  = (string) $request->input('perfil', '');

        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $roteiros = Roteiro::publicados()
            ->withCount(['etapas', 'empresasSugestao'])
            ->when($duracao !== '', fn ($q) => $q->where('duracao_slug', $duracao))
            ->when($perfil !== '', fn ($q) => $q->where('perfil_slug', $perfil))
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

        return view('site.roteiros.index', array_merge([
            'roteiros' => $roteiros,
            'q' => $busca,
            'duracao' => $duracao,
            'perfil' => $perfil,
            'duracoes' => Roteiro::DURACOES,
            'perfis' => Roteiro::PERFIS,
        ], $this->resolveEditableHero('site.roteiros')));
    }

    public function show(string $locale, string $slug)
    {
        $roteiro = Roteiro::publicados()
            ->where('slug', $slug)
            ->with([
                'etapas' => fn ($q) => $q->orderBy('ordem')->orderBy('id'),
                'etapas.pontos' => fn ($q) => $q->orderBy('ordem')->orderBy('id'),
                'etapas.pontos.pontoTuristico' => fn ($q) => $q
                    ->where('status', 'publicado')
                    ->select([
                        'id',
                        'nome',
                        'slug',
                        'descricao',
                        'capa_path',
                        'maps_url',
                        'lat',
                        'lng',
                        'cidade',
                        'bairro',
                        'status',
                    ]),
                'empresasSugestao' => fn ($q) => $q->orderBy('ordem')->orderBy('id'),
                'empresasSugestao.empresa' => fn ($q) => $q
                    ->where('status', 'publicado')
                    ->select([
                        'id',
                        'nome',
                        'slug',
                        'descricao',
                        'foto_capa_path',
                        'maps_url',
                        'lat',
                        'lng',
                        'cidade',
                        'bairro',
                        'status',
                    ]),
            ])
            ->firstOrFail();

        $relacionados = Roteiro::publicados()
            ->where('id', '<>', $roteiro->id)
            ->where(function ($q) use ($roteiro) {
                $q->where('perfil_slug', $roteiro->perfil_slug)
                    ->orWhere('duracao_slug', $roteiro->duracao_slug);
            })
            ->orderBy('ordem')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('site.roteiros.show', array_merge([
            'roteiro' => $roteiro,
            'relacionados' => $relacionados,
        ], $this->resolveEditableHero('site.roteiros.show')));
    }
}
