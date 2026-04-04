<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiConteudoSiteTranslationEngine implements ConteudoSiteTranslationEngine
{
    public function available(): bool
    {
        return filled(config('services.site_translation.openai.api_key'));
    }

    public function translate(array $payload, string $sourceLocale, string $targetLocale): array
    {
        if (! $this->available()) {
            throw new RuntimeException('OpenAI API key ausente para tradução automática.');
        }

        $response = Http::baseUrl(rtrim((string) config('services.site_translation.openai.base_url'), '/'))
            ->timeout((int) config('services.site_translation.timeout', 30))
            ->withToken((string) config('services.site_translation.openai.api_key'))
            ->acceptJson()
            ->post('/chat/completions', [
                'model' => (string) config('services.site_translation.openai.model', 'gpt-4.1-mini'),
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Você é um tradutor profissional de conteúdo institucional e turístico. Responda apenas JSON válido, preservando as mesmas chaves de entrada. Não invente links. Não traduza URLs. Mantenha tom editorial claro e natural.',
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode([
                            'source_locale' => $sourceLocale,
                            'target_locale' => $targetLocale,
                            'payload' => $payload,
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Falha ao traduzir conteúdo automaticamente.');
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('Resposta vazia do provedor de tradução.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Resposta inválida do provedor de tradução.');
        }

        $translated = $decoded['payload'] ?? $decoded;

        if (! is_array($translated)) {
            throw new RuntimeException('Payload traduzido inválido.');
        }

        return $translated;
    }
}
