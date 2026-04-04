<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ResolvesEditableHero;
use App\Models\JogosIndigenas;
use Illuminate\Support\Collection;

class JogosIndigenasPublicController extends Controller
{
    use ResolvesEditableHero;

    public function index()
    {
        $jogo = $this->principalPublicado()?->load([
            'edicoes' => fn ($query) => $this->aplicarFiltroPublicacaoEdicoes($query)
                ->withCount(['fotos', 'videos', 'patrocinadores'])
                ->with([
                    'fotos' => fn ($rel) => $rel->orderBy('ordem')->orderBy('id'),
                    'videos' => fn ($rel) => $rel->orderBy('ordem')->orderBy('id'),
                    'patrocinadores' => fn ($rel) => $rel->orderBy('ordem')->orderBy('id'),
                ]),
        ]);

        $edicoes = $jogo?->edicoes ?? collect();

        $stats = [
            'edicoes' => $edicoes->count(),
            'fotos' => $edicoes->sum(fn ($edicao) => (int) ($edicao->fotos_count ?? $edicao->fotos->count())),
            'videos' => $edicoes->sum(fn ($edicao) => (int) ($edicao->videos_count ?? $edicao->videos->count())),
            'patrocinadores' => $edicoes->sum(fn ($edicao) => (int) ($edicao->patrocinadores_count ?? $edicao->patrocinadores->count())),
        ];

        return view('site.jogos_indigenas.index', array_merge([
            'jogo' => $jogo,
            'edicoes' => $edicoes,
            'stats' => $stats,
            'edicaoDestaque' => $edicoes->first(),
        ], $this->resolveEditableHero('site.jogos_indigenas.index')));
    }

    private function principalPublicado(): ?JogosIndigenas
    {
        $publicado = JogosIndigenas::query()
            ->publicados()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('ordem')
            ->orderBy('id')
            ->first();

        if ($publicado) {
            return $publicado;
        }

        return JogosIndigenas::query()
            ->whereHas('edicoes', fn ($query) => $this->aplicarFiltroPublicacaoEdicoes($query))
            ->orderBy('ordem')
            ->orderBy('id')
            ->first();
    }

    private function aplicarFiltroPublicacaoEdicoes($query)
    {
        return $query
            ->publicados()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('ano')
            ->orderBy('ordem')
            ->orderBy('id');
    }
}
