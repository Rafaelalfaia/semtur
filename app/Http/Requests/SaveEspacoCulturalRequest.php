<?php

namespace App\Http\Requests;

use App\Models\Catalogo\EspacoCultural;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveEspacoCulturalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        [$lat, $lng] = $this->extractCoordsFromUrl($this->input('maps_url'));

        $merge = [
            'agendamento_ativo' => $this->boolean('agendamento_ativo'),
            'remover_capa' => $this->boolean('remover_capa'),
            'cidade' => trim((string) $this->input('cidade', '')) ?: 'Altamira',
            'agendamento_whatsapp_phone' => $this->onlyDigits($this->input('agendamento_whatsapp_phone')),
        ];

        if ($lat !== null && $lng !== null) {
            $merge['lat'] = $lat;
            $merge['lng'] = $lng;
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(EspacoCultural::TIPOS)],
            'nome' => ['required', 'string', 'max:160'],
            'resumo' => ['nullable', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'status' => ['required', Rule::in(EspacoCultural::STATUSES)],
            'ordem' => ['nullable', 'integer', 'min:0'],

            'maps_url' => ['nullable', 'url', 'max:2048'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:120'],
            'cidade' => ['nullable', 'string', 'max:120'],

            'capa' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remover_capa' => ['nullable', 'boolean'],

            'galeria' => ['nullable', 'array', 'max:12'],
            'galeria.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'remover_midias' => ['nullable', 'array'],
            'remover_midias.*' => ['integer'],

            'horarios' => ['required', 'array', 'min:1'],
            'horarios.*.id' => ['nullable', 'integer'],
            'horarios.*.dia_semana' => ['required', 'integer', 'between:0,6'],
            'horarios.*.hora_inicio' => ['required', 'date_format:H:i'],
            'horarios.*.hora_fim' => ['required', 'date_format:H:i'],
            'horarios.*.vagas' => ['nullable', 'integer', 'min:1'],
            'horarios.*.observacao' => ['nullable', 'string', 'max:190'],
            'horarios.*.ativo' => ['nullable', 'boolean'],
            'horarios.*.ordem' => ['nullable', 'integer', 'min:0'],

            'agendamento_ativo' => ['nullable', 'boolean'],
            'agendamento_contato_nome' => ['nullable', 'string', 'max:120'],
            'agendamento_contato_label' => ['nullable', 'string', 'max:80'],
            'agendamento_whatsapp_phone' => ['nullable', 'string', 'max:30'],
            'agendamento_instrucoes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $status = (string) $this->input('status');
            $espaco = $this->route('espaco');

            if ($status !== EspacoCultural::STATUS_PUBLICADO) {
                return;
            }

            $hasResumoOuDescricao = filled($this->input('resumo')) || filled($this->input('descricao'));
            if (!$hasResumoOuDescricao) {
                $validator->errors()->add('descricao', 'Para publicar, informe resumo ou descrição.');
            }

            $hasLocalizacao = filled($this->input('maps_url'))
                || (filled($this->input('lat')) && filled($this->input('lng')))
                || filled($this->input('endereco'));

            if (!$hasLocalizacao) {
                $validator->errors()->add('maps_url', 'Para publicar, informe localização por mapa, coordenadas ou endereço.');
            }

            $hasExistingCover = $espaco && filled($espaco->capa_path) && !$this->boolean('remover_capa');
            $hasNewCover = $this->hasFile('capa');

            if (!$hasExistingCover && !$hasNewCover) {
                $validator->errors()->add('capa', 'Para publicar, envie uma imagem de capa.');
            }

            if ($this->boolean('agendamento_ativo') && !filled($this->input('agendamento_whatsapp_phone'))) {
                $validator->errors()->add('agendamento_whatsapp_phone', 'Informe o WhatsApp de atendimento para habilitar agendamento.');
            }
        });
    }

    private function onlyDigits(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits !== '' ? $digits : null;
    }

    private function extractCoordsFromUrl(?string $url): array
    {
        if (!$url) {
            return [null, null];
        }

        $s = urldecode(trim($url));

        if (preg_match('~@\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~', $s, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }

        if (preg_match('~[?&](?:q|ll)=\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~i', $s, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }

        if (preg_match('~!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)~', $s, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }

        if (preg_match('~!4d(-?\d+(?:\.\d+)?)!3d(-?\d+(?:\.\d+)?)~', $s, $m)) {
            return [(float) $m[2], (float) $m[1]];
        }

        if (preg_match('~(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~', $s, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }

        return [null, null];
    }
}
