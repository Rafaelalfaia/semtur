<?php

namespace App\Http\Requests;

use App\Models\Conteudo\Video;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $slug = trim((string) $this->input('slug'));

        $this->merge([
            'remover_capa' => $this->boolean('remover_capa'),
            'slug' => $slug !== '' ? Str::slug($slug) : null,
            'ordem' => (int) ($this->input('ordem', 0) ?: 0),
        ]);
    }

    public function rules(): array
    {
        $id = optional($this->route('video'))->id;

        return [
            'titulo' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('videos', 'slug')->ignore($id)],
            'descricao' => ['required', 'string'],
            'link_acesso' => ['required', 'url', 'max:2048'],

            'ordem' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(Video::STATUS)],

            'capa' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'remover_capa' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $host = strtolower((string) parse_url((string) $this->input('link_acesso'), PHP_URL_HOST));

            $hostsPermitidos = [
                'drive.google.com',
                'www.drive.google.com',
                'docs.google.com',
                'www.docs.google.com',
            ];

            if ($host !== '' && !in_array($host, $hostsPermitidos, true)) {
                $validator->errors()->add(
                    'link_acesso',
                    'Use um link do Google Drive.'
                );
            }

            if ((string) $this->input('status') !== Video::STATUS_PUBLICADO) {
                return;
            }

            $video = $this->route('video');

            $hasExistingCover = $video
                && filled($video->capa_path)
                && !$this->boolean('remover_capa');

            $hasNewCover = $this->hasFile('capa');

            if (!$hasExistingCover && !$hasNewCover) {
                $validator->errors()->add('capa', 'Para publicar, envie uma imagem de capa.');
            }
        });
    }
}
