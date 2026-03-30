<?php

namespace App\Support;

use App\Models\Idioma;
use App\Models\TranslationKey;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TranslationCatalogSync
{
    public function syncFromLangFiles(): int
    {
        $idiomas = Idioma::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('nome')
            ->get();

        if ($idiomas->isEmpty()) {
            return 0;
        }

        $defaultCodigo = $idiomas->firstWhere('is_default', true)?->codigo ?? $idiomas->first()->codigo;
        $defaultStrings = $this->loadUiStringsForIdioma($defaultCodigo);

        $catalog = [];
        foreach ($idiomas as $idioma) {
            $strings = $this->loadUiStringsForIdioma($idioma->codigo);
            foreach ($strings as $key => $text) {
                $catalog[$key][$idioma->codigo] = $text;
            }
        }

        foreach ($defaultStrings as $key => $text) {
            $catalog[$key][$defaultCodigo] = $catalog[$key][$defaultCodigo] ?? $text;
        }

        if ($catalog === []) {
            return 0;
        }

        $idiomaIds = $idiomas->pluck('id', 'codigo');

        DB::transaction(function () use ($catalog, $defaultCodigo, $defaultStrings, $idiomaIds) {
            foreach ($catalog as $key => $valuesByCode) {
                $translation = TranslationKey::query()->firstOrNew(['key' => $key]);
                $translation->group = $this->resolveGroup($key);
                $translation->base_text = (string) ($defaultStrings[$key] ?? $valuesByCode[$defaultCodigo] ?? reset($valuesByCode) ?: '');
                $translation->is_active = true;
                $translation->save();

                foreach ($valuesByCode as $code => $text) {
                    $idiomaId = $idiomaIds[$code] ?? null;
                    if (! $idiomaId || trim((string) $text) === '') {
                        continue;
                    }

                    $translation->values()->updateOrCreate(
                        ['idioma_id' => $idiomaId],
                        ['text' => trim((string) $text)]
                    );
                }
            }
        });

        return count($catalog);
    }

    private function loadUiStringsForIdioma(string $codigo): array
    {
        $appLocale = (string) data_get(config('app.supported_locales'), "{$codigo}.app_locale", $codigo);
        $candidates = array_unique(array_filter([
            lang_path("{$appLocale}/ui.php"),
            lang_path("{$codigo}/ui.php"),
            lang_path(strtolower($appLocale).'/ui.php'),
            lang_path(strtoupper($codigo).'/ui.php'),
        ]));

        foreach ($candidates as $path) {
            if (! is_file($path)) {
                continue;
            }

            $data = require $path;
            if (! is_array($data)) {
                continue;
            }

            return $this->flattenStrings($data, 'ui.');
        }

        return [];
    }

    private function flattenStrings(array $data, string $prefix): array
    {
        $flat = [];

        foreach (Arr::dot($data, $prefix) as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $flat[$key] = (string) $value;
            }
        }

        return $flat;
    }

    private function resolveGroup(string $key): ?string
    {
        $parts = explode('.', $key);

        return count($parts) >= 2 ? implode('.', array_slice($parts, 0, 2)) : $parts[0];
    }
}
