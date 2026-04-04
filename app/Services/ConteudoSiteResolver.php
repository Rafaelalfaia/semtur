<?php

namespace App\Services;

use App\Models\Conteudo\ConteudoSiteBloco;
use App\Models\Conteudo\ConteudoSiteBlocoTraducao;
use Illuminate\Support\Collection;

class ConteudoSiteResolver
{
    public function pagina(string $pagina, ?string $locale = null, bool $onlyPublished = true): Collection
    {
        $locale = $this->normalizeLocale($locale);
        $fallbackLocale = $this->fallbackLocale();

        $query = ConteudoSiteBloco::query()
            ->when($onlyPublished, fn ($builder) => $builder->publicados())
            ->daPagina($pagina)
            ->whereNull('parent_id')
            ->with([
                'children' => fn ($builder) => $builder
                    ->when($onlyPublished, fn ($childQuery) => $childQuery->publicados())
                    ->with(['traducoes.idioma', 'midias.idioma']),
                'traducoes.idioma',
                'midias.idioma',
            ])
            ->orderBy('ordem')
            ->orderBy('id');

        return $query->get()->map(function (ConteudoSiteBloco $bloco) use ($locale, $fallbackLocale) {
            return $this->hydrateBlock($bloco, $locale, $fallbackLocale);
        });
    }

    public function bloco(string $pagina, string $chave, ?string $locale = null, bool $onlyPublished = true): ?ConteudoSiteBloco
    {
        $locale = $this->normalizeLocale($locale);
        $fallbackLocale = $this->fallbackLocale();

        $bloco = ConteudoSiteBloco::query()
            ->when($onlyPublished, fn ($builder) => $builder->publicados())
            ->daPagina($pagina)
            ->daChave($chave)
            ->with([
                'children' => fn ($builder) => $builder
                    ->when($onlyPublished, fn ($childQuery) => $childQuery->publicados())
                    ->with(['traducoes.idioma', 'midias.idioma']),
                'traducoes.idioma',
                'midias.idioma',
            ])
            ->first();

        if (! $bloco) {
            return null;
        }

        return $this->hydrateBlock($bloco, $locale, $fallbackLocale);
    }

    public function traducao(ConteudoSiteBloco $bloco, ?string $locale = null): ?ConteudoSiteBlocoTraducao
    {
        $locale = $this->normalizeLocale($locale);
        $fallbackLocale = $this->fallbackLocale();

        return $this->resolveTranslation($bloco, $locale, $fallbackLocale);
    }

    private function hydrateBlock(ConteudoSiteBloco $bloco, string $locale, string $fallbackLocale): ConteudoSiteBloco
    {
        $traducao = $this->resolveTranslation($bloco, $locale, $fallbackLocale);
        $midias = $this->resolveMedia($bloco, $locale, $fallbackLocale);

        $bloco->setRelation('traducoes', collect($traducao ? [$traducao] : []));
        $bloco->setRelation('midias', $midias);
        $bloco->setAttribute('traducao_resolvida', $traducao);

        if ($bloco->relationLoaded('children')) {
            $children = $bloco->children->map(function (ConteudoSiteBloco $child) use ($locale, $fallbackLocale) {
                return $this->hydrateBlock($child, $locale, $fallbackLocale);
            });

            $bloco->setRelation('children', $children);
        }

        return $bloco;
    }

    private function resolveTranslation(ConteudoSiteBloco $bloco, string $locale, string $fallbackLocale): ?ConteudoSiteBlocoTraducao
    {
        $traducoes = $bloco->relationLoaded('traducoes')
            ? $bloco->traducoes
            : $bloco->traducoes()->with('idioma')->get();

        return $traducoes->first(fn (ConteudoSiteBlocoTraducao $item) => $item->idioma?->codigo === $locale)
            ?: $traducoes->first(fn (ConteudoSiteBlocoTraducao $item) => $item->idioma?->codigo === $fallbackLocale)
            ?: $traducoes->first();
    }

    private function resolveMedia(ConteudoSiteBloco $bloco, string $locale, string $fallbackLocale): Collection
    {
        $midias = $bloco->relationLoaded('midias')
            ? $bloco->midias
            : $bloco->midias()->with('idioma')->get();

        $grouped = $midias->groupBy('slot');

        return $grouped->map(function (Collection $slotItems) use ($locale, $fallbackLocale) {
            $localeSpecific = $slotItems
                ->filter(fn ($item) => $item->idioma?->codigo === $locale)
                ->values();

            if ($localeSpecific->isNotEmpty()) {
                return $localeSpecific;
            }

            $fallbackSpecific = $slotItems
                ->filter(fn ($item) => $item->idioma?->codigo === $fallbackLocale)
                ->values();

            if ($fallbackSpecific->isNotEmpty()) {
                return $fallbackSpecific;
            }

            return $slotItems
                ->filter(fn ($item) => $item->idioma_id === null)
                ->values();
        })->flatten(1)->values();
    }

    private function normalizeLocale(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $normalized = strtolower(str_replace('_', '-', (string) $locale));

        return explode('-', $normalized)[0] ?: $this->fallbackLocale();
    }

    private function fallbackLocale(): string
    {
        return (string) config('app.locale_prefix_fallback', 'pt');
    }
}
