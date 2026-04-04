<?php

namespace App\Services;

class NullConteudoSiteTranslationEngine implements ConteudoSiteTranslationEngine
{
    public function available(): bool
    {
        return false;
    }

    public function translate(array $payload, string $sourceLocale, string $targetLocale): array
    {
        return $payload;
    }
}
