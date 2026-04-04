<?php

namespace App\Services;

interface ConteudoSiteTranslationEngine
{
    public function available(): bool;

    public function translate(array $payload, string $sourceLocale, string $targetLocale): array;
}
