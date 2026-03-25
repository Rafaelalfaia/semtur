<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveRotaDoCacauEdicaoFotoRequest extends FormRequest
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
        $foto = $this->route('foto');

        return [
            'legenda' => ['nullable', 'string', 'max:180'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'imagem' => [
                $foto ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:6144',
            ],
        ];
    }
}
