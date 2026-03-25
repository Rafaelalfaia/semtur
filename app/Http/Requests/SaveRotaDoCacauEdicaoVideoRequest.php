<?php

namespace App\Http\Requests;

use App\Models\RotaDoCacauEdicaoVideo;
use Illuminate\Foundation\Http\FormRequest;

class SaveRotaDoCacauEdicaoVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ordem' => (int) ($this->input('ordem', 0) ?: 0),
        ]);
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:180'],
            'descricao' => ['nullable', 'string'],
            'drive_url' => ['required', 'url', 'max:2048'],
            'embed_url' => ['nullable', 'url', 'max:2048'],
            'ordem' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $driveUrl = (string) $this->input('drive_url', '');

            if ($driveUrl === '') {
                return;
            }

            if (!RotaDoCacauEdicaoVideo::buildEmbedUrl($driveUrl)) {
                $validator->errors()->add('drive_url', 'Informe um link valido do Google Drive para o video.');
            }
        });
    }
}
