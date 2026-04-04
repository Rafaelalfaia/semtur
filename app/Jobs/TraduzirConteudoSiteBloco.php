<?php

namespace App\Jobs;

use App\Models\Conteudo\ConteudoSiteBloco;
use App\Services\ConteudoSiteTranslationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TraduzirConteudoSiteBloco implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public readonly int $blocoId,
        public readonly string $sourceLocale = 'pt',
        public readonly ?string $targetLocale = null,
        public readonly bool $force = false,
    ) {
        $this->onQueue((string) config('services.site_translation.queue', 'default'));
    }

    public function handle(ConteudoSiteTranslationManager $manager): void
    {
        $bloco = ConteudoSiteBloco::query()->find($this->blocoId);

        if (! $bloco) {
            return;
        }

        if ($this->targetLocale) {
            $manager->traduzirBloco($bloco, $this->sourceLocale, $this->targetLocale, $this->force);
            return;
        }

        $manager->traduzirBlocoParaIdiomasAlvo($bloco, $this->sourceLocale, null, $this->force);
    }
}
