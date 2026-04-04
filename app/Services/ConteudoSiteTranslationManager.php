<?php

namespace App\Services;

use App\Models\Conteudo\ConteudoSiteBloco;
use App\Models\Conteudo\ConteudoSiteBlocoTraducao;
use App\Models\Idioma;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ConteudoSiteTranslationManager
{
    public function __construct(
        private readonly ConteudoSiteTranslationEngine $engine,
    ) {
    }

    public function disponivel(): bool
    {
        return $this->engine->available();
    }

    public function idiomasAlvo(?string $sourceLocale = null): array
    {
        $sourceLocale = $this->normalizeLocale($sourceLocale ?: config('app.locale_prefix_fallback', 'pt'));
        $configured = config('services.site_translation.target_locales', ['en', 'es']);

        return collect($configured)
            ->map(fn ($locale) => $this->normalizeLocale((string) $locale))
            ->filter(fn ($locale) => $locale !== '' && $locale !== $sourceLocale)
            ->values()
            ->all();
    }

    public function traduzirBlocoParaIdiomasAlvo(
        ConteudoSiteBloco $bloco,
        string $sourceLocale = 'pt',
        ?array $targetLocales = null,
        bool $force = false,
    ): array {
        $targetLocales ??= $this->idiomasAlvo($sourceLocale);

        $result = [];

        foreach ($targetLocales as $targetLocale) {
            $result[$targetLocale] = $this->traduzirBloco($bloco, $sourceLocale, (string) $targetLocale, $force);
        }

        return $result;
    }

    public function traduzirBloco(
        ConteudoSiteBloco $bloco,
        string $sourceLocale,
        string $targetLocale,
        bool $force = false,
    ): ConteudoSiteBlocoTraducao {
        $sourceLocale = $this->normalizeLocale($sourceLocale);
        $targetLocale = $this->normalizeLocale($targetLocale);

        if ($sourceLocale === $targetLocale) {
            throw new RuntimeException('Idioma de origem e destino nao podem ser iguais.');
        }

        $bloco->loadMissing(['traducoes.idioma']);

        $source = $bloco->traducoes->first(fn (ConteudoSiteBlocoTraducao $item) => $item->idioma?->codigo === $sourceLocale);

        if (! $source) {
            throw new RuntimeException("Traducao base {$sourceLocale} nao encontrada para o bloco.");
        }

        $sourcePayload = $this->extractPayload($source);
        $sourceHash = $this->payloadHash($sourcePayload);
        $targetIdiomaId = $this->resolveIdiomaId($targetLocale);

        $target = $bloco->traducoes->first(fn (ConteudoSiteBlocoTraducao $item) => (int) $item->idioma_id === $targetIdiomaId);

        if (
            ! $force
            && $target
            && $target->source_hash === $sourceHash
            && $target->is_auto_translated
        ) {
            return $target;
        }

        $translationMode = 'fallback_copy';
        $sanitizedPayload = $this->buildFallbackPayload($sourcePayload);

        if ($this->engine->available()) {
            try {
                $translatedPayload = $this->engine->translate($sourcePayload, $sourceLocale, $targetLocale);
                $sanitizedPayload = $this->sanitizeTranslatedPayload($translatedPayload, $sourcePayload);
                $translationMode = 'provider';
            } catch (\Throwable) {
                $sanitizedPayload = $this->buildFallbackPayload($sourcePayload);
            }
        }

        return DB::transaction(function () use (
            $bloco,
            $targetIdiomaId,
            $sanitizedPayload,
            $sourceLocale,
            $sourceHash,
            $translationMode
        ) {
            /** @var ConteudoSiteBlocoTraducao $translation */
            $translation = $bloco->traducoes()->updateOrCreate(
                ['idioma_id' => $targetIdiomaId],
                array_merge($sanitizedPayload, [
                    'is_auto_translated' => true,
                    'auto_translated_at' => now(),
                    'reviewed_at' => null,
                    'source_locale' => $sourceLocale,
                    'source_hash' => $sourceHash,
                    'extras' => array_merge($sanitizedPayload['extras'] ?? [], [
                        '_translation_mode' => $translationMode,
                    ]),
                ])
            );

            $translation->load('idioma');

            return $translation;
        });
    }

    public function marcarComoRevisado(ConteudoSiteBlocoTraducao $traducao): ConteudoSiteBlocoTraducao
    {
        $traducao->forceFill([
            'reviewed_at' => now(),
        ])->save();

        return $traducao->fresh(['idioma', 'bloco']);
    }

    public function payloadHash(array $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function extractPayload(ConteudoSiteBlocoTraducao $traducao): array
    {
        return [
            'eyebrow' => $traducao->eyebrow,
            'titulo' => $traducao->titulo,
            'subtitulo' => $traducao->subtitulo,
            'lead' => $traducao->lead,
            'conteudo' => $traducao->conteudo,
            'cta_label' => $traducao->cta_label,
            'cta_href' => $traducao->cta_href,
            'seo_title' => $traducao->seo_title,
            'seo_description' => $traducao->seo_description,
            'extras' => $traducao->extras ?? [],
        ];
    }

    private function sanitizeTranslatedPayload(array $payload, array $sourcePayload): array
    {
        return [
            'eyebrow' => $this->nullableString(Arr::get($payload, 'eyebrow')),
            'titulo' => $this->nullableString(Arr::get($payload, 'titulo')),
            'subtitulo' => $this->nullableString(Arr::get($payload, 'subtitulo')),
            'lead' => $this->nullableString(Arr::get($payload, 'lead')),
            'conteudo' => $this->nullableString(Arr::get($payload, 'conteudo')),
            'cta_label' => $this->nullableString(Arr::get($payload, 'cta_label')),
            'cta_href' => $this->nullableString(Arr::get($sourcePayload, 'cta_href')),
            'seo_title' => $this->nullableString(Arr::get($payload, 'seo_title')),
            'seo_description' => $this->nullableString(Arr::get($payload, 'seo_description')),
            'extras' => is_array(Arr::get($payload, 'extras')) ? Arr::get($payload, 'extras') : (Arr::get($sourcePayload, 'extras') ?? []),
        ];
    }

    private function buildFallbackPayload(array $sourcePayload): array
    {
        return [
            'eyebrow' => $this->nullableString(Arr::get($sourcePayload, 'eyebrow')),
            'titulo' => $this->nullableString(Arr::get($sourcePayload, 'titulo')),
            'subtitulo' => $this->nullableString(Arr::get($sourcePayload, 'subtitulo')),
            'lead' => $this->nullableString(Arr::get($sourcePayload, 'lead')),
            'conteudo' => $this->nullableString(Arr::get($sourcePayload, 'conteudo')),
            'cta_label' => $this->nullableString(Arr::get($sourcePayload, 'cta_label')),
            'cta_href' => $this->nullableString(Arr::get($sourcePayload, 'cta_href')),
            'seo_title' => $this->nullableString(Arr::get($sourcePayload, 'seo_title')),
            'seo_description' => $this->nullableString(Arr::get($sourcePayload, 'seo_description')),
            'extras' => is_array(Arr::get($sourcePayload, 'extras')) ? Arr::get($sourcePayload, 'extras') : [],
        ];
    }

    private function resolveIdiomaId(string $locale): int
    {
        $idioma = Idioma::query()->where('codigo', $locale)->first();

        if (! $idioma) {
            throw new RuntimeException("Idioma {$locale} nao encontrado.");
        }

        return (int) $idioma->id;
    }

    private function normalizeLocale(string $locale): string
    {
        $normalized = strtolower(str_replace('_', '-', trim($locale)));

        return Str::before($normalized, '-') ?: $normalized;
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value) && $value !== null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
