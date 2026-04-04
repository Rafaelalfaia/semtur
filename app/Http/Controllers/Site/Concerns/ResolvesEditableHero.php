<?php

namespace App\Http\Controllers\Site\Concerns;

use App\Models\Conteudo\ConteudoSiteBloco;
use App\Models\Conteudo\ConteudoSiteMidia;
use App\Services\ConteudoSiteResolver;
use Illuminate\Support\Collection;

trait ResolvesEditableHero
{
    protected function resolveEditableHero(string $page): array
    {
        $pageBlocks = $this->resolveEditablePageBlocks($page);
        $heroBlock = $pageBlocks->get('hero');

        return [
            'heroBlock' => $heroBlock,
            'heroTranslation' => $heroBlock?->getAttribute('traducao_resolvida'),
            'heroMedia' => $heroBlock?->midias->first(),
            'pageBlocks' => $pageBlocks,
        ];
    }

    protected function resolveEditablePageBlocks(string $page): Collection
    {
        return app(ConteudoSiteResolver::class)
            ->pagina($page, onlyPublished: false)
            ->keyBy('chave')
            ->map(function (ConteudoSiteBloco $block) {
                $mediaBySlot = $block->midias
                    ->groupBy('slot')
                    ->map(fn (Collection $items): ?ConteudoSiteMidia => $items->first());

                $block->setAttribute('media_by_slot', $mediaBySlot);

                return $block;
            });
    }
}
