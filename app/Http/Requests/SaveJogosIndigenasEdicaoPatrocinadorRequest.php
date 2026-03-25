<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveJogosIndigenasEdicaoPatrocinadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ordem' => (int) ($this->input('ordem', 0) ?: 0),
            'remover_logo' => $this->boolean('remover_logo'),
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:180'],
            'url' => ['nullable', 'url', 'max:2048'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'remover_logo' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ];
    }
}
