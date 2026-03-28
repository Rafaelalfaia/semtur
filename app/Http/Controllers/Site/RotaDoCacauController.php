<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\RotaDoCacau;
use App\Models\RotaDoCacauEdicao;
use Illuminate\Support\Collection;

class RotaDoCacauController extends Controller
{
    public function index()
    {
        $rota = $this->principalPublicada()?->load([
            'edicoes' => fn ($query) => $this->aplicarFiltroPublicacaoEdicoes($query)
                ->withCount(['fotos', 'videos', 'patrocinadores'])
                ->with([
                    'fotos' => fn ($rel) => $rel->orderBy('ordem')->orderBy('id'),
                    'videos' => fn ($rel) => $rel->orderBy('ordem')->orderBy('id'),
                    'patrocinadores' => fn ($rel) => $rel->orderBy('ordem')->orderBy('id'),
                ]),
        ]);

        $edicoes = $rota?->edicoes ?? collect();
        $edicaoDestaque = $edicoes->first();
        $outrasEdicoes = $edicoes->slice(1)->values();

        return view('site.rota_do_cacau.index', [
            'rota' => $rota,
            'edicoes' => $edicoes,
            'edicaoDestaque' => $edicaoDestaque,
            'outrasEdicoes' => $outrasEdicoes,
        ]);
    }

    public function show(string $slug)
    {
        $rota = $this->principalPublicada();

        abort_unless($rota, 404);

        $edicao = $rota->edicoes()
            ->where('slug', $slug);

        $this->aplicarFiltroPublicacaoEdicoes($edicao);

        $edicao = $edicao
            ->withCount(['fotos', 'videos', 'patrocinadores'])
            ->with([
                'fotos' => fn ($query) => $query->orderBy('ordem')->orderBy('id'),
                'videos' => fn ($query) => $query->orderBy('ordem')->orderBy('id'),
                'patrocinadores' => fn ($query) => $query->orderBy('ordem')->orderBy('id'),
            ])
            ->firstOrFail();

        $outrasEdicoes = $rota->edicoes()
            ->where('id', '<>', $edicao->id);

        $this->aplicarFiltroPublicacaoEdicoes($outrasEdicoes);

        $outrasEdicoes = $outrasEdicoes
            ->withCount(['fotos', 'videos', 'patrocinadores'])
            ->limit(6)
            ->get();

        $temConteudoComplementar = $this->temConteudoComplementar($edicao);

        return view('site.rota_do_cacau.show', [
            'rota' => $rota,
            'edicao' => $edicao,
            'outrasEdicoes' => $outrasEdicoes,
            'temConteudoComplementar' => $temConteudoComplementar,
        ]);
    }

    private function principalPublicada(): ?RotaDoCacau
    {
        return RotaDoCacau::query()
            ->publicados()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
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

    private function temConteudoComplementar(RotaDoCacauEdicao $edicao): bool
    {
        return collect([
            $edicao->fotos_count ?? $edicao->fotos->count(),
            $edicao->videos_count ?? $edicao->videos->count(),
            $edicao->patrocinadores_count ?? $edicao->patrocinadores->count(),
        ])->sum() > 0;
    }
}
